@extends('layouts.app')

@section('page-title', 'Sales Order: ' . $salesOrder->so_number)

@section('content')
@php
    $canPick = $salesOrder->items->contains(fn ($line) => $line->quantity_ordered > $line->quantity_fulfilled);
    $canPack = $salesOrder->items->contains(fn ($line) => $line->quantity_fulfilled > $line->quantity_packed);
    $canShip = $salesOrder->items->contains(fn ($line) => $line->quantity_packed > $line->quantity_shipped);
    $canInvoice = $salesOrder->items->contains(fn ($line) => $line->quantity_shipped > $line->quantity_invoiced);
    $statusClasses = [
        'draft' => 'bg-secondary',
        'pending_approval' => 'bg-warning text-dark',
        'approved' => 'bg-success',
        'partial' => 'bg-info',
        'fulfilled' => 'bg-primary',
        'packed' => 'bg-primary',
        'shipped' => 'bg-dark',
        'invoiced' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
@endphp
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-lg-4">
            <h3>SO: {{ $salesOrder->so_number }}</h3>
        </div>
        <div class="col-lg-8 text-end d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Back to List</a>

            @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative']) && $salesOrder->status === 'draft')
                <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="btn btn-warning">Edit SO</a>
                <form method="POST" action="{{ route('sales-orders.request-approval', $salesOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'sales_manager']) && $salesOrder->status === 'pending_approval')
                <form method="POST" action="{{ route('sales-orders.approve', $salesOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this Sales Order?')">Approve SO</button>
                </form>
                <form method="POST" action="{{ route('sales-orders.reject', $salesOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject SO</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative', 'sales_manager']) && in_array($salesOrder->status, ['draft', 'pending_approval', 'approved']))
                <form method="POST" action="{{ route('sales-orders.cancel', $salesOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Cancel this Sales Order?')">Cancel</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'inventory_manager']) && $canPick && in_array($salesOrder->status, ['approved', 'partial']))
                <a href="{{ route('sales-orders.pick.create', $salesOrder) }}" class="btn btn-primary">Pick Items</a>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'inventory_manager']) && $canPack)
                <a href="{{ route('sales-orders.pack.create', $salesOrder) }}" class="btn btn-info text-white">Pack Items</a>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'inventory_manager']) && $canShip)
                <a href="{{ route('sales-orders.ship.create', $salesOrder) }}" class="btn btn-dark">Ship Items</a>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']) && $canInvoice)
                <a href="{{ route('sales-invoices.create', $salesOrder) }}" class="btn btn-success">Create Invoice</a>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Order Information</h5></div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td><span class="badge {{ $statusClasses[$salesOrder->status] ?? 'bg-secondary' }}">{{ str_replace('_', ' ', ucfirst($salesOrder->status)) }}</span></td>
                        </tr>
                        <tr><td class="text-muted">Order Date</td><td>{{ $salesOrder->order_date->format('Y-m-d') }}</td></tr>
                        <tr><td class="text-muted">Requested Delivery</td><td>{{ $salesOrder->requested_delivery_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Customer PO</td><td>{{ $salesOrder->po_reference ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Currency</td><td>{{ $salesOrder->currency_code }}</td></tr>
                        <tr><td class="text-muted">Created By</td><td>{{ $salesOrder->createdBy->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Approved By</td><td>{{ $salesOrder->approvedBy->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Memo</td><td>{{ $salesOrder->memo ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Customer & Fulfillment</h5></div>
                <div class="card-body">
                    <h6>{{ $salesOrder->customer->name }}</h6>
                    <p class="mb-2">
                        {{ $salesOrder->customer->customer_code }}<br>
                        {{ $salesOrder->customer->email ?? '-' }}<br>
                        {{ $salesOrder->customer->address ?? '-' }}
                    </p>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Credit Available</small>
                                <strong>${{ number_format(max(0, $salesOrder->customer->credit_limit - $salesOrder->customer->credit_used), 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Warehouse</small>
                                <strong>{{ $salesOrder->location->name ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white"><h5 class="mb-0">Order & Fulfillment Quantities</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Ordered</th>
                        <th class="text-end">Picked</th>
                        <th class="text-end">Packed</th>
                        <th class="text-end">Shipped</th>
                        <th class="text-end">Invoiced</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesOrder->items as $line)
                        <tr>
                            <td>
                                <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $line->item->type) }}</span>
                            </td>
                            <td class="text-end">{{ $line->quantity_ordered }}</td>
                            <td class="text-end">{{ $line->quantity_fulfilled }}</td>
                            <td class="text-end">{{ $line->quantity_packed }}</td>
                            <td class="text-end">{{ $line->quantity_shipped }}</td>
                            <td class="text-end">{{ $line->quantity_invoiced }}</td>
                            <td class="text-end">${{ number_format($line->unit_price, 2) }}</td>
                            <td class="text-end"><strong>${{ number_format($line->line_amount, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="7" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end"><strong>${{ number_format($salesOrder->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white"><h5 class="mb-0">Pick History</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @forelse($salesOrder->picks as $pick)
                            <tr>
                                <td><strong>{{ $pick->pick_number }}</strong><br><small>{{ $pick->pick_date }} · {{ $pick->createdBy->name ?? '-' }}</small></td>
                                <td class="text-end">{{ $pick->items->sum('quantity_picked') }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">No picks</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white"><h5 class="mb-0">Pack History</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @forelse($salesOrder->packs as $pack)
                            <tr>
                                <td><strong>{{ $pack->pack_number }}</strong><br><small>{{ $pack->pack_date }} · {{ $pack->createdBy->name ?? '-' }}</small></td>
                                <td class="text-end">{{ $pack->items->sum('quantity_packed') }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">No packs</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Shipment History</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @forelse($salesOrder->ships as $ship)
                            <tr>
                                <td>
                                    <strong>{{ $ship->ship_number }}</strong><br>
                                    <small>{{ $ship->ship_date }} · {{ $ship->carrier ?? 'No carrier' }}</small><br>
                                    <small class="text-muted">{{ $ship->tracking_number ?? '-' }}</small>
                                </td>
                                <td class="text-end">{{ $ship->items->sum('quantity_shipped') }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">No shipments</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Sales Invoices</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Invoice</th><th>Date</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Paid</th></tr>
                </thead>
                <tbody>
                    @forelse($salesOrder->invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('sales-invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>
                                @if($invoice->status === 'pending_approval')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end">${{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No invoices</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
