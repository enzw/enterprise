<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPaymentController extends Controller
{
    public function index()
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'admin');

        $payments = CustomerPayment::with('customer', 'createdBy')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20);

        foreach ($payments as $payment) {
            $allocated = $payment->allocations()->sum('amount_allocated');
            $payment->unallocated_amount = (float) $payment->amount - (float) $allocated;
        }

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $this->checkRole('ar_analyst', 'admin');

        $customers = Customer::where('is_active', true)->get();
        $cashAccounts = Account::where('type', 'asset')
            ->whereIn('number', ['1000', '1010'])
            ->where('is_active', true)
            ->get();
        $selectedCustomerId = request()->integer('customer');

        return view('payments.create', compact('customers', 'cashAccounts', 'selectedCustomerId'));
    }

    public function store(Request $request)
    {
        $this->checkRole('ar_analyst', 'admin');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:check,bank_transfer,cash,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
            'cash_account_id' => 'required|exists:accounts,id',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|distinct|exists:sales_invoices,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        $payment = DB::transaction(function () use ($validated) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($validated['customer_id']);
            $cashAccount = $this->lockedCashAccount($validated['cash_account_id'], $customer->subsidiary_id);
            $arAccount = $this->lockedArAccount($customer->subsidiary_id);
            $amount = round((float) $validated['amount'], 2);
            $allocations = collect($validated['allocations'] ?? []);

            if (round((float) $allocations->sum('amount'), 2) > $amount) {
                throw ValidationException::withMessages([
                    'allocations' => 'Total invoice allocation cannot exceed the payment amount.',
                ]);
            }

            $payment = CustomerPayment::create([
                'customer_id' => $customer->id,
                'payment_number' => $this->nextPaymentNumber(),
                'payment_date' => $validated['payment_date'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'ar_account_id' => $arAccount->id,
                'cash_account_id' => $cashAccount->id,
                'created_by' => auth()->id(),
            ]);

            $cashAccount->increment('balance', $amount);

            foreach ($allocations as $allocation) {
                $this->applyAllocation(
                    $payment,
                    $customer,
                    $arAccount,
                    (int) $allocation['invoice_id'],
                    round((float) $allocation['amount'], 2)
                );
            }

            return $payment;
        });

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Customer Payment recorded successfully');
    }

    public function show(CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'admin');

        $payment->load('customer', 'createdBy', 'cashAccount', 'arAccount', 'allocations.invoice');
        $allocatedAmount = (float) $payment->allocations()->sum('amount_allocated');
        $unallocatedAmount = (float) $payment->amount - $allocatedAmount;
        $openInvoices = $this->openInvoices($payment->customer_id);

        return view('payments.show', compact('payment', 'unallocatedAmount', 'openInvoices', 'allocatedAmount'));
    }

    public function edit(CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');

        if ($payment->allocations()->exists()) {
            return back()->withErrors(['error' => 'Remove all invoice allocations before editing this payment']);
        }

        $customers = Customer::where('is_active', true)->get();
        $cashAccounts = Account::where('type', 'asset')
            ->whereIn('number', ['1000', '1010'])
            ->where('is_active', true)
            ->get();
        $customer = $payment->customer;

        return view('payments.edit', compact('payment', 'customers', 'cashAccounts', 'customer'));
    }

    public function update(Request $request, CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:check,bank_transfer,cash,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        DB::transaction(function () use ($payment, $validated) {
            $payment = CustomerPayment::with('customer')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->allocations()->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'Remove all invoice allocations before editing this payment.',
                ]);
            }

            $oldCashAccount = Account::query()->lockForUpdate()->findOrFail($payment->cash_account_id);
            $newCashAccount = $this->lockedCashAccount(
                $validated['cash_account_id'],
                $payment->customer->subsidiary_id
            );
            $newAmount = round((float) $validated['amount'], 2);

            $oldCashAccount->decrement('balance', $payment->amount);
            $newCashAccount->increment('balance', $newAmount);

            $payment->update([
                'payment_date' => $validated['payment_date'],
                'amount' => $newAmount,
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'cash_account_id' => $newCashAccount->id,
            ]);
        });

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully');
    }

    public function allocate(Request $request, CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');

        $validated = $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($payment, $validated) {
            $payment = CustomerPayment::with('customer')
                ->lockForUpdate()
                ->findOrFail($payment->id);
            $arAccount = Account::query()->lockForUpdate()->findOrFail($payment->ar_account_id);
            $allocatedAmount = (float) $payment->allocations()->sum('amount_allocated');
            $availableAmount = round((float) $payment->amount - $allocatedAmount, 2);
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $availableAmount) {
                throw ValidationException::withMessages([
                    'amount' => "Amount exceeds the unallocated payment balance ({$availableAmount}).",
                ]);
            }

            $this->applyAllocation(
                $payment,
                $payment->customer,
                $arAccount,
                (int) $validated['invoice_id'],
                $amount
            );
        });

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment allocated successfully');
    }

    public function removeAllocation(CustomerPaymentAllocation $allocation)
    {
        $this->checkRole('ar_analyst', 'admin');

        $payment = DB::transaction(function () use ($allocation) {
            $allocation = CustomerPaymentAllocation::with('payment.customer', 'invoice')
                ->lockForUpdate()
                ->findOrFail($allocation->id);
            $invoice = SalesInvoice::query()->lockForUpdate()->findOrFail($allocation->sales_invoice_id);
            $arAccount = Account::query()->lockForUpdate()->findOrFail($allocation->payment->ar_account_id);
            $amount = (float) $allocation->amount_allocated;

            $newAmountPaid = max(0, (float) $invoice->amount_paid - $amount);
            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newAmountPaid > 0 ? 'partial' : 'approved',
            ]);

            $arAccount->increment('balance', $amount);
            $allocation->payment->customer->increment('credit_used', $amount);
            $payment = $allocation->payment;
            $allocation->delete();

            return $payment;
        });

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Allocation removed and Accounts Receivable restored');
    }

    public function getCustomerInvoices($customerId)
    {
        $this->checkRole('ar_analyst', 'admin');

        return response()->json(
            $this->openInvoices((int) $customerId)
                ->map(fn (SalesInvoice $invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total' => (float) $invoice->total,
                    'amount_paid' => (float) $invoice->amount_paid,
                ])
        );
    }

    private function applyAllocation(
        CustomerPayment $payment,
        Customer $customer,
        Account $arAccount,
        int $invoiceId,
        float $amount
    ): void {
        $invoice = SalesInvoice::query()->lockForUpdate()->findOrFail($invoiceId);

        if ((int) $invoice->customer_id !== (int) $customer->id) {
            throw ValidationException::withMessages([
                'invoice_id' => 'The invoice does not belong to the selected customer.',
            ]);
        }

        if (! in_array($invoice->status, ['approved', 'partial'], true)) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Only approved or partially paid invoices can receive payment.',
            ]);
        }

        $invoiceBalance = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
        if ($amount > $invoiceBalance) {
            throw ValidationException::withMessages([
                'amount' => "Allocation exceeds the invoice balance ({$invoiceBalance}).",
            ]);
        }

        CustomerPaymentAllocation::create([
            'customer_payment_id' => $payment->id,
            'sales_invoice_id' => $invoice->id,
            'amount_allocated' => $amount,
        ]);

        $newAmountPaid = round((float) $invoice->amount_paid + $amount, 2);
        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newAmountPaid >= (float) $invoice->total ? 'paid' : 'partial',
        ]);

        $arAccount->decrement('balance', $amount);
        $customer->update([
            'credit_used' => max(0, (float) $customer->credit_used - $amount),
        ]);
    }

    private function openInvoices(int $customerId)
    {
        return SalesInvoice::where('customer_id', $customerId)
            ->whereIn('status', ['approved', 'partial', 'pending_approval'])
            ->whereColumn('amount_paid', '<', 'total')
            ->orderBy('due_date')
            ->get();
    }

    private function lockedCashAccount(int $accountId, int $subsidiaryId): Account
    {
        $account = Account::query()->lockForUpdate()->findOrFail($accountId);

        if (
            (int) $account->subsidiary_id !== $subsidiaryId
            || $account->type !== 'asset'
            || ! in_array($account->number, ['1000', '1010'], true)
        ) {
            throw ValidationException::withMessages([
                'cash_account_id' => 'Select a valid cash account for the customer subsidiary.',
            ]);
        }

        return $account;
    }

    private function lockedArAccount(int $subsidiaryId): Account
    {
        $account = Account::query()
            ->where('subsidiary_id', $subsidiaryId)
            ->where('number', '1110')
            ->lockForUpdate()
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'account' => 'Accounts Receivable account 1110 was not found.',
            ]);
        }

        return $account;
    }

    private function nextPaymentNumber(): string
    {
        $nextNumber = ((int) CustomerPayment::max('id')) + 1;

        return 'PAY-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
