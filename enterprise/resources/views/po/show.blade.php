@extends('layouts.app')

@section('page-title', 'Purchase Order: ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>PO: {{ $purchaseOrder->po_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Back to List</a>
            
            @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']) && $purchaseOrder->status === 'draft')
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">Edit PO</a>
                <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this Purchase Order?')">Approve PO</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'inventory_manager']) && in_array($purchaseOrder->status, ['approved', 'partial']))
                <a href="{{ route('item-receipts.create', $purchaseOrder) }}" class="btn btn-primary">Receive Items</a>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <!-- PO Information Card -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">PO Header Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 40%;">Status:</td>
                            <td>
                                @switch($purchaseOrder->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-info">Approved</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning text-dark">Partial Received</span>
                                        @break
                                    @case('received')
                                        <span class="badge bg-success">Received</span>
                                        @break
                                    @case('closed')
                                        <span class="badge bg-dark">Closed</span>
                                        @break
                                    @default
                                        <span class="badge bg-info">{{ ucfirst($purchaseOrder->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Order Date:</td>
                            <td><strong>{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('M d, Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Expected Delivery:</td>
                            <td>{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By:</td>
                            <td>{{ $purchaseOrder->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo:</td>
                            <td>{{ $purchaseOrder->memo ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vendor & Location Details Card -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Vendor & Location Details</h5>
                </div>
                <div class="card-body">
                    <h6>Vendor Information</h6>
                    <p class="mb-3">
                        <strong>{{ $purchaseOrder->vendor->name }}</strong><br>
                        <small class="text-muted">Code: {{ $purchaseOrder->vendor->vendor_code ?? '-' }}</small><br>
                        Email: {{ $purchaseOrder->vendor->email ?? '-' }}<br>
                        Phone: {{ $purchaseOrder->vendor->phone ?? '-' }}<br>
                        Address: {{ $purchaseOrder->vendor->address ?? '-' }}
                    </p>
                    <hr>
                    <h6>Shipping Location</h6>
                    <p class="mb-0">
                        <strong>{{ $purchaseOrder->location->name }}</strong><br>
                        Address: {{ $purchaseOrder->location->address ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Line Items</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Department</th>
                        <th class="text-end">Quantity Ordered</th>
                        <th class="text-end">Quantity Received</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Line Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td><strong>{{ $item->item->sku }}</strong> - {{ $item->item->name }}</td>
                        <td>{{ $item->department->name ?? '-' }}</td>
                        <td class="text-end">{{ $item->quantity_ordered }}</td>
                        <td class="text-end text-success">{{ $item->quantity_received ?? 0 }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end text-success"><strong>${{ number_format($purchaseOrder->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
