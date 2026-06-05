@extends('layouts.app')

<<<<<<< HEAD
@section('page-title', 'Purchase Order: ' . $purchaseOrder->po_number)
=======
@section('page-title', 'Purchase Order Details')
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
<<<<<<< HEAD
            <h3>{{ $purchaseOrder->po_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            @if($purchaseOrder->status === 'draft')
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">Edit</a>
            <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this Purchase Order?')">Approve PO</button>
            </form>
            @endif
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Back to List</a>
=======
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
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
        </div>
    </div>

    <div class="row">
<<<<<<< HEAD
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Order Details</h6>
                        @switch($purchaseOrder->status)
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
                                <span class="badge bg-info">{{ ucfirst($purchaseOrder->status) }}</span>
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Vendor:</strong> {{ $purchaseOrder->vendor->name ?? 'N/A' }}</p>
                            <p><strong>Location:</strong> {{ $purchaseOrder->location->name ?? 'N/A' }}</p>
                            <p><strong>Created By:</strong> {{ $purchaseOrder->createdBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date }}</p>
                            <p><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ?? '-' }}</p>
                            @if($purchaseOrder->memo)
                            <p><strong>Memo:</strong> {{ $purchaseOrder->memo }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Line Items</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Department</th>
                                <th>Qty Ordered</th>
                                <th>Qty Received</th>
                                <th>Unit Price</th>
                                <th>Line Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $line)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->item->name ?? 'N/A' }} <small class="text-muted">({{ $line->item->sku ?? '' }})</small></td>
                                <td>{{ $line->department->name ?? '-' }}</td>
                                <td>{{ $line->quantity_ordered }}</td>
                                <td>{{ $line->quantity_received ?? 0 }}</td>
                                <td>${{ number_format($line->unit_price, 2) }}</td>
                                <td>${{ number_format($line->line_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>${{ number_format($purchaseOrder->subtotal, 2) }}</strong></td>
                            </tr>
                            @if($purchaseOrder->tax_amount)
                            <tr>
                                <td colspan="6" class="text-end"><strong>Tax:</strong></td>
                                <td><strong>${{ number_format($purchaseOrder->tax_amount, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                <td><strong>${{ number_format($purchaseOrder->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
=======
        <!-- PO Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">PO Header Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Status:</td>
                            <td>
                                @if($purchaseOrder->status === 'draft')
                                    <span class="badge bg-secondary text-white">Draft</span>
                                @elseif($purchaseOrder->status === 'approved')
                                    <span class="badge bg-info text-white">Approved</span>
                                @elseif($purchaseOrder->status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial Received</span>
                                @elseif($purchaseOrder->status === 'received')
                                    <span class="badge bg-success text-white">Received</span>
                                @else
                                    <span class="badge bg-dark text-white">{{ ucfirst($purchaseOrder->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Order Date:</td>
                            <td><strong>{{ $purchaseOrder->order_date }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Expected Delivery:</td>
                            <td>{{ $purchaseOrder->expected_delivery_date ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By:</td>
                            <td>{{ $purchaseOrder->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo:</td>
                            <td>{{ $purchaseOrder->memo ?? '-' }}</td>
                        </tr>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                    </table>
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Order Summary</h6>
                    <hr>
                    <p><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</p>
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}</p>
                    <p><strong>Line Items:</strong> {{ $purchaseOrder->items->count() }}</p>
                    <p><strong>Total:</strong> ${{ number_format($purchaseOrder->total, 2) }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Timestamps</h6>
                    <hr>
                    <p><small><strong>Created:</strong> {{ $purchaseOrder->created_at->format('M d, Y H:i') }}</small></p>
                    <p><small><strong>Updated:</strong> {{ $purchaseOrder->updated_at->format('M d, Y H:i') }}</small></p>
                </div>
            </div>

            @if($purchaseOrder->status === 'approved')
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Actions</h6>
                    <hr>
                    <a href="{{ route('item-receipts.create', $purchaseOrder) }}" class="btn btn-primary btn-sm w-100">Create Item Receipt</a>
                </div>
            </div>
            @endif
=======
        <!-- Vendor & Location Details Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Vendor & Location Details</h5>
                </div>
                <div class="card-body">
                    <h6>Vendor Information</h6>
                    <p class="mb-3">
                        <strong>{{ $purchaseOrder->vendor->name }}</strong><br>
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
                        <th class="text-end">Quantity Billed</th>
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
                        <td class="text-end text-success">{{ $item->quantity_received }}</td>
                        <td class="text-end text-info">{{ $item->quantity_billed }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end text-success"><strong>${{ number_format($purchaseOrder->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
        </div>
    </div>
</div>
@endsection
