@extends('layouts.app')

<<<<<<< HEAD
@section('page-title', 'Sales Order: ' . $salesOrder->so_number)
=======
@section('page-title', 'Sales Order Details')
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
<<<<<<< HEAD
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
=======
            <h3>SO: {{ $salesOrder->so_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Back to List</a>

            @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative']) && $salesOrder->status === 'draft')
                <form method="POST" action="{{ route('sales-orders.request-approval', $salesOrder) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'sales_manager']) && $salesOrder->status === 'pending_approval')
                <form method="POST" action="{{ route('sales-orders.approve', $salesOrder) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this Sales Order?')">Approve SO</button>
                </form>
                <form method="POST" action="{{ route('sales-orders.reject', $salesOrder) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this Sales Order?')">Reject SO</button>
                </form>
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
=======
        <!-- SO Header Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">SO Header Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Status:</td>
                            <td>
                                @if($salesOrder->status === 'draft')
                                    <span class="badge bg-secondary text-white">Draft</span>
                                @elseif($salesOrder->status === 'pending_approval')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @elseif($salesOrder->status === 'approved')
                                    <span class="badge bg-success text-white">Approved</span>
                                @else
                                    <span class="badge bg-dark text-white">{{ ucfirst($salesOrder->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Order Date:</td>
                            <td><strong>{{ $salesOrder->order_date }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Requested Delivery:</td>
                            <td>{{ $salesOrder->requested_delivery_date ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Currency:</td>
                            <td>{{ $salesOrder->currency_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By:</td>
                            <td>{{ $salesOrder->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo:</td>
                            <td>{{ $salesOrder->memo ?? '-' }}</td>
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
=======
        <!-- Customer & Subsidiary Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Customer & Subsidiary Info</h5>
                </div>
                <div class="card-body">
                    <h6>Customer Information</h6>
                    <p class="mb-3">
                        <strong>{{ $salesOrder->customer->name }}</strong><br>
                        Email: {{ $salesOrder->customer->email ?? '-' }}<br>
                        Phone: {{ $salesOrder->customer->phone ?? '-' }}<br>
                        Address: {{ $salesOrder->customer->address ?? '-' }}
                    </p>
                    <hr>
                    <h6>Subsidiary</h6>
                    <p class="mb-0">
                        <strong>{{ $salesOrder->subsidiary->name ?? 'United States - West' }}</strong>
                    </p>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
=======

    <!-- Items Card -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Sales Order Items</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Quantity Ordered</th>
                        <th class="text-end">Quantity Fulfilled</th>
                        <th class="text-end">Quantity Invoiced</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Line Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesOrder->items as $item)
                    <tr>
                        <td><strong>{{ $item->item->sku }}</strong> - {{ $item->item->name }}</td>
                        <td class="text-end">{{ $item->quantity_ordered }}</td>
                        <td class="text-end text-success">{{ $item->quantity_fulfilled }}</td>
                        <td class="text-end text-info">{{ $item->quantity_invoiced }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end text-success"><strong>${{ number_format($salesOrder->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
</div>
@endsection
