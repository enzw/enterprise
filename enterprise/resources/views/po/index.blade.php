@extends('layouts.app')

@section('page-title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Purchase Orders</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-success">+ Create Purchase Order</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Vendor</th>
                        <th>Order Date</th>
                        <th>Expected Delivery</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
                        <td>{{ $po->vendor->name ?? 'N/A' }}</td>
                        <td>{{ $po->order_date }}</td>
                        <td>{{ $po->expected_delivery_date ?? '-' }}</td>
                        <td>${{ number_format($po->total, 2) }}</td>
                        <td>
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
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No purchase orders found</td>
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
