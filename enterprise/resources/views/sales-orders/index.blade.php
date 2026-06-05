@extends('layouts.app')

@section('page-title', 'Sales Orders')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Sales Orders</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('sales-orders.create') }}" class="btn btn-success">+ Create Sales Order</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SO Number</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Currency</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesOrders as $so)
                    <tr>
                        <td><strong>{{ $so->so_number }}</strong></td>
                        <td>{{ $so->customer->name ?? 'N/A' }}</td>
                        <td>{{ $so->order_date }}</td>
                        <td>{{ $so->currency_code }}</td>
                        <td>${{ number_format($so->total, 2) }}</td>
                        <td>
                            @switch($so->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @case('pending_approval')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success">Approved</span>
                                    @break
                                @case('fulfilled')
                                    <span class="badge bg-primary">Fulfilled</span>
                                    @break
                                @case('closed')
                                    <span class="badge bg-dark">Closed</span>
                                    @break
                                @default
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $so->status)) }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('sales-orders.show', $so) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No sales orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $salesOrders->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
