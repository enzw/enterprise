@extends('layouts.app')

@section('page-title', 'Bill: ' . $bill->bill_number)

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

            @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst']) && $bill->status === 'draft')
                <form method="POST" action="{{ route('bills.submit', $bill) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">Resubmit Bill</button>
                </form>
            @endif

            @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst']) && in_array($bill->status, ['approved', 'partial']))
                <a href="{{ route('bill-payments.create', $bill) }}" class="btn btn-success">Record Payment</a>
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
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Status:</td>
                            <td>
                                @if($bill->status === 'draft')
                                    <span class="badge bg-secondary text-white">Draft</span>
                                @elseif($bill->status === 'pending_approval')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @elseif($bill->status === 'approved')
                                    <span class="badge bg-success text-white">Approved</span>
                                @elseif($bill->status === 'partial')
                                    <span class="badge bg-info text-white">Partially Paid</span>
                                @elseif($bill->status === 'paid')
                                    <span class="badge bg-primary text-white">Paid</span>
                                @else
                                    <span class="badge bg-dark text-white">{{ ucfirst($bill->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Vendor Invoice Ref:</td>
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
                            <td class="text-muted">Created By:</td>
                            <td>{{ $bill->createdBy->name ?? '-' }}</td>
                        </tr>
                        @if($bill->memo)
                        <tr>
                            <td class="text-muted">Memo:</td>
                            <td>{{ $bill->memo }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Vendor & Subsidiary Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Vendor & Subsidiary Info</h5>
                </div>
                <div class="card-body">
                    <h6>Vendor Information</h6>
                    <p class="mb-3">
                        <strong>{{ $bill->vendor->name }}</strong><br>
                        Code: {{ $bill->vendor->vendor_code ?? '-' }}<br>
                        Email: {{ $bill->vendor->email ?? '-' }}<br>
                        Phone: {{ $bill->vendor->phone ?? '-' }}<br>
                        Address: {{ $bill->vendor->address ?? '-' }}
                    </p>
                    <hr>
                    <h6>Subsidiary</h6>
                    <p class="mb-0">
                        <strong>{{ $bill->subsidiary->name ?? 'Main' }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Items Card -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Bill Items</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Line Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bill->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ $item->quantity ?? '-' }}</td>
                        <td class="text-end">{{ $item->unit_price ? '$' . number_format($item->unit_price, 2) : '-' }}</td>
                        <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No items in this bill</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                        <td class="text-end"><strong>${{ number_format($bill->subtotal, 2) }}</strong></td>
                    </tr>
                    @if($bill->tax_amount > 0)
                    <tr class="table-light">
                        <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                        <td class="text-end"><strong>${{ number_format($bill->tax_amount, 2) }}</strong></td>
                    </tr>
                    @endif
                    <tr class="table-light">
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end text-success"><strong>${{ number_format($bill->total, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Amount Paid:</strong></td>
                        <td class="text-end"><strong>${{ number_format($bill->amount_paid, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Balance Due:</strong></td>
                        <td class="text-end text-danger"><strong>${{ number_format(max(0, $bill->total - $bill->amount_paid), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payment History</h5>
            <span class="badge bg-light text-dark">{{ $bill->payments->count() }} payment(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Cash Account</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bill->payments as $payment)
                        <tr>
                            <td><a href="{{ route('bill-payments.show', $payment) }}">BP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                            <td>{{ $payment->cashAccount->number }} - {{ $payment->cashAccount->name }}</td>
                            <td>{{ $payment->reference_no ?? '-' }}</td>
                            <td class="text-end"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No payments recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
