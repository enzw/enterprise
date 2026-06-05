@extends('layouts.app')

@section('page-title', 'Sales Orders')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Sales Orders</h3>
        </div>
<<<<<<< HEAD
        <div class="col-md-6 text-end">
            <a href="{{ route('sales-orders.create') }}" class="btn btn-success">+ Create Sales Order</a>
        </div>
=======
        @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative']))
        <div class="col-md-6 text-end">
            <a href="{{ route('sales-orders.create') }}" class="btn btn-success">+ Create Sales Order</a>
        </div>
        @endif
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SO Number</th>
                        <th>Customer</th>
<<<<<<< HEAD
                        <th>Order Date</th>
                        <th>Currency</th>
                        <th>Total</th>
                        <th>Status</th>
=======
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Total</th>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesOrders as $so)
                    <tr>
                        <td><strong>{{ $so->so_number }}</strong></td>
<<<<<<< HEAD
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
=======
                        <td>{{ $so->customer->name }}</td>
                        <td>
                            @if($so->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($so->status === 'pending_approval')
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                            @elseif($so->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($so->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $so->order_date }}</td>
                        <td>{{ $so->requested_delivery_date ?? '-' }}</td>
                        <td>${{ number_format($so->total, 2) }}</td>
                        <td>
                            <a href="{{ route('sales-orders.show', $so) }}" class="btn btn-sm btn-primary">View</a>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                        </td>
                    </tr>
                    @empty
                    <tr>
<<<<<<< HEAD
                        <td colspan="7" class="text-center text-muted py-4">No sales orders found</td>
=======
                        <td colspan="7" class="text-center text-muted py-4">No Sales Orders found</td>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
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
