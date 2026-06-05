@extends('layouts.app')

@section('page-title', 'Vendor Bill Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Bill: {{ $bill->bill_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('bills.index') }}" class="btn btn-secondary">Back to List</a>

            @if(in_array(auth()->user()->current_role, ['admin', 'accounting_manager']) && $bill->status === 'pending_approval')
                <form method="POST" action="{{ route('bills.approve', $bill) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this Vendor Bill?')">Approve Bill</button>
                </form>
                <form method="POST" action="{{ route('bills.reject', $bill) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this Vendor Bill?')">Reject Bill</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Bill Header Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Bill Header Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Status:</td>
                            <td>
                                @if($bill->status === 'draft')
                                    <span class="badge bg-secondary text-white">Draft</span>
                                @elseif($bill->status === 'pending_approval')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @elseif($bill->status === 'approved')
                                    <span class="badge bg-success text-white">Approved</span>
                                @else
                                    <span class="badge bg-dark text-white">{{ ucfirst($bill->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Vendor Invoice / Ref:</td>
                            <td><strong>{{ $bill->reference_no }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">PO Reference:</td>
                            <td>
                                @if($bill->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $bill->purchaseOrder) }}">{{ $bill->purchaseOrder->po_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bill Date:</td>
                            <td>{{ $bill->bill_date }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Due Date:</td>
                            <td>{{ $bill->due_date ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo:</td>
                            <td>{{ $bill->memo ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vendor & Subsidiary Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Vendor & Company Info</h5>
                </div>
                <div class="card-body">
                    <h6>Vendor Information</h6>
                    <p class="mb-3">
                        <strong>{{ $bill->vendor->name }}</strong><br>
                        Email: {{ $bill->vendor->email ?? '-' }}<br>
                        Phone: {{ $bill->vendor->phone ?? '-' }}<br>
                        Address: {{ $bill->vendor->address ?? '-' }}
                    </p>
                    <hr>
                    <h6>Subsidiary</h6>
                    <p class="mb-0">
                        <strong>{{ $bill->subsidiary->name ?? 'United States - West' }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Card -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Bill Items</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Line Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ $item->quantity ?? '-' }}</td>
                        <td class="text-end">{{ $item->unit_price ? '$'.number_format($item->unit_price, 2) : '-' }}</td>
                        <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end text-success"><strong>${{ number_format($bill->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
