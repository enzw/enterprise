@extends('layouts.app')

@section('page-title', 'Sales Invoice: ' . $invoice->invoice_number)

@section('content')
@php $balanceDue = max(0, $invoice->total - $invoice->amount_paid); @endphp
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-5"><h3>{{ $invoice->invoice_number }}</h3></div>
        <div class="col-md-7 text-end d-flex gap-2 justify-content-end">
            <a href="{{ route('sales-invoices.index') }}" class="btn btn-secondary">Invoice List</a>
            <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}" class="btn btn-primary">View SO</a>
            @if(in_array(auth()->user()->current_role, ['admin', 'accounting_manager']) && $invoice->status === 'draft')
                <form method="POST" action="{{ route('sales-invoices.approve', $invoice) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">Approve Invoice</button>
                </form>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']) && $invoice->status === 'draft')
                <form method="POST" action="{{ route('sales-invoices.cancel', $invoice) }}">
                    @csrf
                    <button class="btn btn-outline-danger" type="submit" onclick="return confirm('Cancel this draft invoice?')">Cancel Invoice</button>
                </form>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']) && in_array($invoice->status, ['approved', 'partial']))
                <a href="{{ route('payments.create', ['customer' => $invoice->customer_id]) }}" class="btn btn-success">Record Payment</a>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Invoice Information</h5></div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span></td></tr>
                        <tr><td class="text-muted">Customer</td><td><strong>{{ $invoice->customer->name }}</strong></td></tr>
                        <tr><td class="text-muted">Sales Order</td><td><a href="{{ route('sales-orders.show', $invoice->salesOrder) }}">{{ $invoice->salesOrder->so_number }}</a></td></tr>
                        <tr><td class="text-muted">Invoice Date</td><td>{{ $invoice->invoice_date->format('Y-m-d') }}</td></tr>
                        <tr><td class="text-muted">Due Date</td><td>{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Currency</td><td>{{ $invoice->currency_code }}</td></tr>
                        <tr><td class="text-muted">Created By</td><td>{{ $invoice->createdBy->name }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Receivable Summary</h5></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4"><small class="text-muted d-block">Invoice Total</small><strong>${{ number_format($invoice->total, 2) }}</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Amount Paid</small><strong class="text-success">${{ number_format($invoice->amount_paid, 2) }}</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Balance Due</small><strong class="text-danger">${{ number_format($balanceDue, 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white"><h5 class="mb-0">Invoice Lines</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Description</th><th class="text-end">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @foreach($invoice->items as $line)
                        <tr><td>{{ $line->description }}</td><td class="text-end">{{ $line->quantity }}</td><td class="text-end">${{ number_format($line->unit_price, 2) }}</td><td class="text-end"><strong>${{ number_format($line->line_amount, 2) }}</strong></td></tr>
                    @endforeach
                </tbody>
                <tfoot><tr class="table-light"><td colspan="3" class="text-end"><strong>Total:</strong></td><td class="text-end"><strong>${{ number_format($invoice->total, 2) }}</strong></td></tr></tfoot>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Payment Allocations</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Payment</th><th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Allocated</th></tr></thead>
                <tbody>
                    @forelse($invoice->payments as $allocation)
                        <tr>
                            <td><a href="{{ route('payments.show', $allocation->payment) }}">{{ $allocation->payment->payment_number }}</a></td>
                            <td>{{ $allocation->payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($allocation->payment->payment_method)) }}</td>
                            <td>{{ $allocation->payment->reference_no ?? '-' }}</td>
                            <td class="text-end">${{ number_format($allocation->amount_allocated, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No payments allocated</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
