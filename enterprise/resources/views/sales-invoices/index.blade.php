@extends('layouts.app')

@section('page-title', 'Sales Invoices')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3>Sales Invoices</h3>
            <p class="text-muted mb-0">Invoices generated from shipped Sales Order quantities.</p>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Invoice</th><th>Customer</th><th>Sales Order</th><th>Status</th><th>Date</th><th>Due</th><th class="text-end">Total</th><th class="text-end">Balance</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td><a href="{{ route('sales-orders.show', $invoice->salesOrder) }}">{{ $invoice->salesOrder->so_number }}</a></td>
                            <td><span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span></td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end">${{ number_format(max(0, $invoice->total - $invoice->amount_paid), 2) }}</td>
                            <td><a href="{{ route('sales-invoices.show', $invoice) }}" class="btn btn-sm btn-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No sales invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
