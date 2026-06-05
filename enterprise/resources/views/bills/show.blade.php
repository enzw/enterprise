@extends('layouts.app')

<<<<<<< HEAD
@section('page-title', 'Bill: ' . $bill->bill_number)
=======
@section('page-title', 'Vendor Bill Details')
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
<<<<<<< HEAD
            <h3>{{ $bill->bill_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            @if($bill->status === 'pending_approval')
                @if(in_array(auth()->user()->current_role, ['accounting_manager', 'admin']))
                <form method="POST" action="{{ route('bills.approve', $bill) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this bill?')">Approve</button>
                </form>
                <form method="POST" action="{{ route('bills.reject', $bill) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this bill?')">Reject</button>
                </form>
                @endif
            @endif
            <a href="{{ route('bills.index') }}" class="btn btn-secondary">Back to List</a>
=======
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
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
        </div>
    </div>

    <div class="row">
<<<<<<< HEAD
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Bill Details</h6>
                        @switch($bill->status)
                            @case('draft')
                                <span class="badge bg-secondary">Draft</span>
                                @break
                            @case('pending_approval')
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                                @break
                            @case('approved')
                                <span class="badge bg-success">Approved</span>
                                @break
                            @case('paid')
                                <span class="badge bg-primary">Paid</span>
                                @break
                            @default
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</span>
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Vendor:</strong> {{ $bill->vendor->name ?? 'N/A' }}</p>
                            <p><strong>Reference No:</strong> {{ $bill->reference_no }}</p>
                            @if($bill->purchaseOrder)
                            <p><strong>Purchase Order:</strong> {{ $bill->purchaseOrder->po_number }}</p>
                            @endif
                            <p><strong>Created By:</strong> {{ $bill->createdBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bill Date:</strong> {{ $bill->bill_date }}</p>
                            <p><strong>Due Date:</strong> {{ $bill->due_date ?? '-' }}</p>
                            @if($bill->approved_at)
                            <p><strong>Approved By:</strong> {{ $bill->approvedBy->name ?? 'N/A' }}</p>
                            <p><strong>Approved At:</strong> {{ $bill->approved_at->format('M d, Y H:i') }}</p>
                            @endif
                            @if($bill->memo)
                            <p><strong>Memo:</strong> {{ $bill->memo }}</p>
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
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Line Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->items as $index => $line)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->description }}</td>
                                <td>{{ $line->quantity ?? '-' }}</td>
                                <td>{{ $line->unit_price ? '$' . number_format($line->unit_price, 2) : '-' }}</td>
                                <td>${{ number_format($line->line_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>${{ number_format($bill->subtotal, 2) }}</strong></td>
                            </tr>
                            @if($bill->tax_amount)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                <td><strong>${{ number_format($bill->tax_amount, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>${{ number_format($bill->total, 2) }}</strong></td>
                            </tr>
                            @if($bill->amount_paid > 0)
                            <tr>
                                <td colspan="4" class="text-end"><strong>Amount Paid:</strong></td>
                                <td><strong>${{ number_format($bill->amount_paid, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Balance Due:</strong></td>
                                <td><strong>${{ number_format($bill->total - $bill->amount_paid, 2) }}</strong></td>
                            </tr>
                            @endif
                        </tfoot>
=======
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
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
                    </table>
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Bill Summary</h6>
                    <hr>
                    <p><strong>Bill Number:</strong> {{ $bill->bill_number }}</p>
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $bill->status)) }}</p>
                    <p><strong>Total:</strong> ${{ number_format($bill->total, 2) }}</p>
                    <p><strong>Paid:</strong> ${{ number_format($bill->amount_paid ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Timestamps</h6>
                    <hr>
                    <p><small><strong>Created:</strong> {{ $bill->created_at->format('M d, Y H:i') }}</small></p>
                    <p><small><strong>Updated:</strong> {{ $bill->updated_at->format('M d, Y H:i') }}</small></p>
=======
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
>>>>>>> d0cfde08c8fd425417abffed4d6dc072f9c9a618
</div>
@endsection
