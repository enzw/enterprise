@extends('layouts.app')

@section('page-title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Purchase Orders</h3>
        </div>
<<<<<<< HEAD
        <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-success">+ Create Purchase Order</a>
        </div>
=======
        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
        <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-success">+ Create Purchase Order</a>
        </div>
        @endif
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Vendor</th>
<<<<<<< HEAD
                        <th>Order Date</th>
                        <th>Expected Delivery</th>
                        <th>Total</th>
                        <th>Status</th>
=======
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Expected Delivery</th>
                        <th>Total</th>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
<<<<<<< HEAD
                        <td>{{ $po->vendor->name ?? 'N/A' }}</td>
=======
                        <td>{{ $po->vendor->name }}</td>
                        <td>
                            @if($po->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($po->status === 'approved')
                                <span class="badge bg-info">Approved</span>
                            @elseif($po->status === 'partial')
                                <span class="badge bg-warning text-dark">Partial Received</span>
                            @elseif($po->status === 'received')
                                <span class="badge bg-success">Received</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($po->status) }}</span>
                            @endif
                        </td>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                        <td>{{ $po->order_date }}</td>
                        <td>{{ $po->expected_delivery_date ?? '-' }}</td>
                        <td>${{ number_format($po->total, 2) }}</td>
                        <td>
<<<<<<< HEAD
                            @switch($po->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success">Approved</span>
                                    @break
                                @case('partially_received')
                                    <span class="badge bg-warning text-dark">Partially Received</span>
                                    @break
                                @case('received')
                                    <span class="badge bg-primary">Received</span>
                                    @break
                                @case('closed')
                                    <span class="badge bg-dark">Closed</span>
                                    @break
                                @default
                                    <span class="badge bg-info">{{ ucfirst($po->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-info">View</a>
                            @if($po->status === 'draft')
                            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-warning">Edit</a>
=======
                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-primary">View</a>
                            @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']) && $po->status === 'draft')
                                <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-warning">Edit</a>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
<<<<<<< HEAD
                        <td colspan="7" class="text-center text-muted py-4">No purchase orders found</td>
=======
                        <td colspan="7" class="text-center text-muted py-4">No Purchase Orders found</td>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $purchaseOrders->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
