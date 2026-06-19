@extends('layouts.app')

@section('page-title', 'Vendor Payments')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3>Vendor Payments</h3>
            <p class="text-muted mb-0">Payments posted against approved vendor bills.</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment</th>
                        <th>Vendor</th>
                        <th>Bill</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Cash Account</th>
                        <th class="text-end">Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td><strong>BP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $payment->bill->vendor->name }}</td>
                            <td><a href="{{ route('bills.show', $payment->bill) }}">{{ $payment->bill->bill_number }}</a></td>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                            <td>{{ $payment->cashAccount->number }} - {{ $payment->cashAccount->name }}</td>
                            <td class="text-end">${{ number_format($payment->amount, 2) }}</td>
                            <td><a href="{{ route('bill-payments.show', $payment) }}" class="btn btn-sm btn-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No vendor payments recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
