@extends('layouts.app')

@section('page-title', 'Vendor Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Payment BP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('bill-payments.index') }}" class="btn btn-secondary">Payment List</a>
            <a href="{{ route('bills.show', $payment->bill) }}" class="btn btn-primary">View Bill</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Payment Details</h5></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><td class="text-muted">Vendor</td><td><strong>{{ $payment->bill->vendor->name }}</strong></td></tr>
                        <tr><td class="text-muted">Vendor Bill</td><td><a href="{{ route('bills.show', $payment->bill) }}">{{ $payment->bill->bill_number }}</a></td></tr>
                        <tr><td class="text-muted">Purchase Order</td><td>{{ $payment->bill->purchaseOrder->po_number ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Payment Date</td><td>{{ $payment->payment_date->format('Y-m-d') }}</td></tr>
                        <tr><td class="text-muted">Method</td><td>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td></tr>
                        <tr><td class="text-muted">Reference</td><td>{{ $payment->reference_no ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Created By</td><td>{{ $payment->createdBy->name }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Accounting Impact</h5></div>
                <div class="card-body">
                    <div class="display-6 text-success mb-4">${{ number_format($payment->amount, 2) }}</div>
                    <table class="table table-sm">
                        <tr>
                            <td>Reduce Accounts Payable</td>
                            <td class="text-end">{{ $payment->apAccount->number }} - {{ $payment->apAccount->name }}</td>
                        </tr>
                        <tr>
                            <td>Reduce Cash</td>
                            <td class="text-end">{{ $payment->cashAccount->number }} - {{ $payment->cashAccount->name }}</td>
                        </tr>
                    </table>
                    @if($payment->memo)
                        <p class="text-muted mb-0"><strong>Memo:</strong> {{ $payment->memo }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
