@extends('layouts.app')

@section('page-title', 'Customer Payments')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Customer Payments</h3>
        </div>
        @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']))
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.create') }}" class="btn btn-success">+ Record Payment</a>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment #</th>
                        <th>Customer</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Allocated</th>
                        <th>Unallocated</th>
                        <th>Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><strong>{{ $payment->payment_number }}</strong></td>
                        <td>{{ $payment->customer->name }}</td>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                        <td>
                            @php
                                $allocated = $payment->allocations()->sum('amount_allocated');
                            @endphp
                            ${{ number_format($allocated, 2) }}
                        </td>
                        <td>
                            @if($payment->unallocated_amount > 0)
                                <span class="badge bg-warning">${{ number_format($payment->unallocated_amount, 2) }}</span>
                            @else
                                <span class="badge bg-success">Complete</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-info">View</a>
                            @if($payment->unallocated_amount > 0)
                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <em>No customer payments recorded yet.</em>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $payments->links() }}
</div>
@endsection
