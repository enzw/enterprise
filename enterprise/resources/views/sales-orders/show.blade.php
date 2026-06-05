@extends('layouts.app')

@section('page-title', 'Sales Order Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
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
        </div>
    </div>

    <div class="row">
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
                    </table>
                </div>
            </div>
        </div>

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
                </div>
            </div>
        </div>
    </div>

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
</div>
@endsection
