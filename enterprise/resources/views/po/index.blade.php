@extends('layouts.app')

@section('page-title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Purchase Orders</h3>
        </div>
        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
        <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-success">+ Create Purchase Order</a>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Expected Delivery</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
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
                        <td>{{ $po->order_date }}</td>
                        <td>{{ $po->expected_delivery_date ?? '-' }}</td>
                        <td>${{ number_format($po->total, 2) }}</td>
                        <td>
                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-primary">View</a>
                            @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']) && $po->status === 'draft')
                                <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No Purchase Orders found</td>
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
