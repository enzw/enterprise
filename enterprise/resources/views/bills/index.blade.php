@extends('layouts.app')

@section('page-title', 'Vendor Bills')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Vendor Bills</h3>
        </div>
        @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst']))
        <div class="col-md-6 text-end">
            <a href="{{ route('bills.create') }}" class="btn btn-success">+ Create Vendor Bill</a>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Bill Number</th>
                        <th>PO Ref</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td><strong>{{ $bill->bill_number }}</strong></td>
                        <td>
                            @if($bill->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $bill->purchaseOrder) }}">{{ $bill->purchaseOrder->po_number }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $bill->vendor->name }}</td>
                        <td>
                            @if($bill->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($bill->status === 'pending_approval')
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                            @elseif($bill->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($bill->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $bill->bill_date }}</td>
                        <td>{{ $bill->due_date ?? '-' }}</td>
                        <td>${{ number_format($bill->total, 2) }}</td>
                        <td>
                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No Vendor Bills found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bills->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
