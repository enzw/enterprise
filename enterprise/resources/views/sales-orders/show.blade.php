@extends('layouts.app')

@section('page-title', 'Sales Order: ' . $salesOrder->so_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>{{ $salesOrder->so_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            @if($salesOrder->status === 'draft')
            <form method="POST" action="{{ route('sales-orders.request-approval', $salesOrder) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Submit for approval?')">Submit for Approval</button>
            </form>
            @endif

            @if($salesOrder->status === 'pending_approval' && in_array(auth()->user()->current_role, ['sales_manager', 'admin']))
            <form method="POST" action="{{ route('sales-orders.approve', $salesOrder) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this order?')">Approve</button>
            </form>
            <form method="POST" action="{{ route('sales-orders.reject', $salesOrder) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this order?')">Reject</button>
            </form>
            @endif

            <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Order Details</h6>
                        @switch($salesOrder->status)
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
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}</span>
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Customer:</strong> {{ $salesOrder->customer->name ?? 'N/A' }}</p>
                            <p><strong>Currency:</strong> {{ $salesOrder->currency_code }}</p>
                            <p><strong>Created By:</strong> {{ $salesOrder->createdBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> {{ $salesOrder->order_date }}</p>
                            <p><strong>Requested Delivery:</strong> {{ $salesOrder->requested_delivery_date ?? '-' }}</p>
                            @if($salesOrder->approved_at)
                            <p><strong>Approved By:</strong> {{ $salesOrder->approvedBy->name ?? 'N/A' }}</p>
                            <p><strong>Approved At:</strong> {{ $salesOrder->approved_at->format('M d, Y H:i') }}</p>
                            @endif
                            @if($salesOrder->memo)
                            <p><strong>Memo:</strong> {{ $salesOrder->memo }}</p>
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
                                <th>Qty Ordered</th>
                                <th>Qty Fulfilled</th>
                                <th>Unit Price</th>
                                <th>Line Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesOrder->items as $index => $line)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->item->name ?? 'N/A' }} <small class="text-muted">({{ $line->item->sku ?? '' }})</small></td>
                                <td>{{ $line->quantity_ordered }}</td>
                                <td>{{ $line->quantity_fulfilled ?? 0 }}</td>
                                <td>${{ number_format($line->unit_price, 2) }}</td>
                                <td>${{ number_format($line->line_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>${{ number_format($salesOrder->subtotal, 2) }}</strong></td>
                            </tr>
                            @if($salesOrder->tax_amount)
                            <tr>
                                <td colspan="5" class="text-end"><strong>Tax:</strong></td>
                                <td><strong>${{ number_format($salesOrder->tax_amount, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td><strong>${{ number_format($salesOrder->total, 2) }}</strong></td>
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
                    <p><strong>SO Number:</strong> {{ $salesOrder->so_number }}</p>
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}</p>
                    <p><strong>Line Items:</strong> {{ $salesOrder->items->count() }}</p>
                    <p><strong>Total:</strong> ${{ number_format($salesOrder->total, 2) }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Timestamps</h6>
                    <hr>
                    <p><small><strong>Created:</strong> {{ $salesOrder->created_at->format('M d, Y H:i') }}</small></p>
                    <p><small><strong>Updated:</strong> {{ $salesOrder->updated_at->format('M d, Y H:i') }}</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
