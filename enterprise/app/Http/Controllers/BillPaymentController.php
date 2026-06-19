<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BillPayment;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillPaymentController extends Controller
{
    public function index()
    {
        $this->checkRole('ap_analyst', 'accounting_manager', 'admin');

        $payments = BillPayment::with('bill.vendor', 'cashAccount', 'createdBy')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20);

        return view('bill-payments.index', compact('payments'));
    }

    public function create(VendorBill $bill)
    {
        $this->checkRole('ap_analyst', 'admin');

        if (! in_array($bill->status, ['approved', 'partial'], true)) {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Only approved or partially paid bills can be paid');
        }

        $bill->load('vendor', 'purchaseOrder');
        $cashAccounts = Account::where('subsidiary_id', $bill->subsidiary_id)
            ->where('type', 'asset')
            ->whereIn('number', ['1000', '1010'])
            ->where('is_active', true)
            ->get();

        return view('bill-payments.create', compact('bill', 'cashAccounts'));
    }

    public function store(Request $request, VendorBill $bill)
    {
        $this->checkRole('ap_analyst', 'admin');

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:check,bank_transfer,cash,credit_card',
            'cash_account_id' => 'required|exists:accounts,id',
            'reference_no' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
        ]);

        $payment = DB::transaction(function () use ($bill, $validated) {
            $bill = VendorBill::with('vendor')
                ->lockForUpdate()
                ->findOrFail($bill->id);

            if (! in_array($bill->status, ['approved', 'partial'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This bill is not available for payment.',
                ]);
            }

            $balanceDue = round($bill->total - $bill->amount_paid, 2);
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $balanceDue) {
                throw ValidationException::withMessages([
                    'amount' => "Payment exceeds the bill balance ({$balanceDue}).",
                ]);
            }

            $cashAccount = Account::query()
                ->whereKey($validated['cash_account_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $cashAccount->subsidiary_id !== (int) $bill->subsidiary_id
                || $cashAccount->type !== 'asset'
            ) {
                throw ValidationException::withMessages([
                    'cash_account_id' => 'Select a valid cash account for this bill subsidiary.',
                ]);
            }

            if ((float) $cashAccount->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'The selected cash account has insufficient balance.',
                ]);
            }

            $apAccount = Account::query()
                ->where('subsidiary_id', $bill->subsidiary_id)
                ->where('number', '2110')
                ->lockForUpdate()
                ->first();

            if (! $apAccount) {
                throw ValidationException::withMessages([
                    'account' => 'Accounts Payable account 2110 was not found for this subsidiary.',
                ]);
            }

            $payment = BillPayment::create([
                'vendor_bill_id' => $bill->id,
                'ap_account_id' => $apAccount->id,
                'cash_account_id' => $cashAccount->id,
                'created_by' => auth()->id(),
                'amount' => $amount,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'memo' => $validated['memo'] ?? null,
            ]);

            $newAmountPaid = round($bill->amount_paid + $amount, 2);
            $bill->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newAmountPaid >= (float) $bill->total ? 'paid' : 'partial',
            ]);

            $cashAccount->decrement('balance', $amount);
            $apAccount->decrement('balance', $amount);

            $bill->vendor->update([
                'credit_used' => max(0, $bill->vendor->credit_used - $amount),
            ]);

            return $payment;
        });

        return redirect()
            ->route('bill-payments.show', $payment)
            ->with('success', 'Vendor payment recorded successfully');
    }

    public function show(BillPayment $payment)
    {
        $this->checkRole('ap_analyst', 'accounting_manager', 'admin');

        $payment->load('bill.vendor', 'bill.purchaseOrder', 'apAccount', 'cashAccount', 'createdBy');

        return view('bill-payments.show', compact('payment'));
    }
}
