@extends('layouts.app')

@section('page-title', 'Purchase Order: ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
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
        </div>
    </div>

    <div class="row">
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
                    </table>
                </div>
            </div>
        </div>

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
        </div>
    </div>
</div>
@endsection
