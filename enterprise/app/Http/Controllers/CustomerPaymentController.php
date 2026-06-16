<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\Account;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function index()
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'admin');
        $payments = CustomerPayment::with('customer', 'createdBy')->paginate(20);
        
        // Calculate aging
        foreach ($payments as $payment) {
            $allocations = $payment->allocations()->sum('amount_allocated');
            $payment->unallocated_amount = $payment->amount - $allocations;
        }
        
        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $this->checkRole('ar_analyst', 'admin');
        $customers = Customer::where('is_active', true)->get();
        $arAccount = Account::where('type', 'asset')->where('number', '1110')->first();
        $cashAccounts = Account::where('type', 'asset')->whereIn('number', ['1000', '1010'])->get();
        
        return view('payments.create', compact('customers', 'arAccount', 'cashAccounts'));
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
            'allocations.*.invoice_id' => 'required_with:allocations|exists:sales_invoices,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        // Get AR Account
        $arAccount = Account::where('type', 'asset')->where('number', '1110')->first();
        if (!$arAccount) {
            return back()->withErrors(['error' => 'AR Account not found']);
        }

        // Generate Payment Number
        $paymentCount = CustomerPayment::count() + 1;
        $paymentNumber = 'PAY-' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT);

        $payment = CustomerPayment::create([
            'customer_id' => $validated['customer_id'],
            'payment_number' => $paymentNumber,
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'],
            'memo' => $validated['memo'],
            'ar_account_id' => $arAccount->id,
            'cash_account_id' => $validated['cash_account_id'],
            'created_by' => auth()->id(),
        ]);

        // Allocate to invoices if provided
        if (!empty($validated['allocations'])) {
            $totalAllocated = 0;
            foreach ($validated['allocations'] as $allocation) {
                if ($allocation['amount'] > 0) {
                    CustomerPaymentAllocation::create([
                        'customer_payment_id' => $payment->id,
                        'sales_invoice_id' => $allocation['invoice_id'],
                        'amount_allocated' => $allocation['amount'],
                    ]);
                    $totalAllocated += $allocation['amount'];
                }
            }

            // Update invoice paid amount
            foreach ($validated['allocations'] as $allocation) {
                $invoice = SalesInvoice::find($allocation['invoice_id']);
                if ($invoice) {
                    $invoice->update([
                        'amount_paid' => $invoice->amount_paid + $allocation['amount'],
                    ]);
                }
            }
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Customer Payment recorded successfully');
    }

    public function show(CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'admin');
        $payment->load('customer', 'createdBy', 'allocations.invoice');
        
        // Get unallocated amount
        $allocatedAmount = $payment->allocations()->sum('amount_allocated');
        $unallocatedAmount = $payment->amount - $allocatedAmount;

        // Get customer's open invoices
        $openInvoices = SalesInvoice::where('customer_id', $payment->customer_id)
            ->where('status', '!=', 'paid')
            ->get();

        return view('payments.show', compact('payment', 'unallocatedAmount', 'openInvoices', 'allocatedAmount'));
    }

    public function edit(CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');
        
        // Check if payment is already allocated - no editing if fully allocated
        $allocatedAmount = $payment->allocations()->sum('amount_allocated');
        if ($allocatedAmount > 0 && $allocatedAmount >= $payment->amount) {
            return back()->withErrors(['error' => 'Cannot edit a fully allocated payment']);
        }

        $payment->load('allocations');
        $customers = Customer::where('is_active', true)->get();
        $cashAccounts = Account::where('type', 'asset')->whereIn('number', ['1000', '1010'])->get();
        $customer = $payment->customer;

        return view('payments.edit', compact('payment', 'customers', 'cashAccounts', 'customer'));
    }

    public function update(Request $request, CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');

        // Check if payment is already allocated
        $allocatedAmount = $payment->allocations()->sum('amount_allocated');
        if ($allocatedAmount > 0 && $allocatedAmount >= $payment->amount) {
            return back()->withErrors(['error' => 'Cannot update a fully allocated payment']);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:check,bank_transfer,cash,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)->with('success', 'Payment updated successfully');
    }

    public function allocate(Request $request, CustomerPayment $payment)
    {
        $this->checkRole('ar_analyst', 'admin');

        $validated = $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the invoice
        $invoice = SalesInvoice::find($validated['invoice_id']);

        if ($invoice->customer_id != $payment->customer_id) {
            return back()->withErrors(['error' => 'Invoice does not belong to this customer']);
        }

        // Check if amount is valid
        $allocatedAmount = $payment->allocations()->sum('amount_allocated');
        $availableAmount = $payment->amount - $allocatedAmount;

        if ($validated['amount'] > $availableAmount) {
            return back()->withErrors(['error' => "Amount exceeds available payment amount ({$availableAmount})"]);
        }

        // Check if amount doesn't exceed invoice balance
        $invoicePaid = $payment->allocations()
            ->where('sales_invoice_id', $invoice->id)
            ->sum('amount_allocated');
        $invoiceBalance = $invoice->total - $invoicePaid;

        if ($validated['amount'] > $invoiceBalance) {
            return back()->withErrors(['error' => "Amount exceeds invoice balance ({$invoiceBalance})"]);
        }

        // Create allocation
        CustomerPaymentAllocation::create([
            'customer_payment_id' => $payment->id,
            'sales_invoice_id' => $invoice->id,
            'amount_allocated' => $validated['amount'],
        ]);

        // Update invoice paid amount
        $invoice->update([
            'amount_paid' => $invoice->amount_paid + $validated['amount'],
        ]);

        return redirect()->route('payments.show', $payment)->with('success', 'Payment allocated successfully');
    }

    public function removeAllocation(CustomerPaymentAllocation $allocation)
    {
        $this->checkRole('ar_analyst', 'admin');

        $payment = $allocation->payment;
        $invoice = $allocation->invoice;

        // Update invoice paid amount
        $invoice->update([
            'amount_paid' => max(0, $invoice->amount_paid - $allocation->amount_allocated),
        ]);

        $allocation->delete();

        return redirect()->route('payments.show', $payment)->with('success', 'Allocation removed successfully');
    }

    public function getCustomerInvoices($customerId)
    {
        $this->checkRole('ar_analyst', 'admin');

        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->select('id', 'invoice_number', 'total', 'amount_paid')
            ->get();

        return response()->json($invoices);
    }
}
