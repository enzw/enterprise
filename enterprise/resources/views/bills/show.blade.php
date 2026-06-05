@extends('layouts.app')

@section('page-title', 'Bill: ' . $bill->bill_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
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
        </div>
    </div>

    <div class="row">
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
                    </table>
                </div>
            </div>
        </div>

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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
