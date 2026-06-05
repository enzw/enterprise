@extends('layouts.app')

@section('page-title', 'Vendor Bills')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Vendor Bills</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('bills.create') }}" class="btn btn-success">+ Create Bill</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Bill Number</th>
                        <th>Reference</th>
                        <th>Vendor</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td><strong>{{ $bill->bill_number }}</strong></td>
                        <td>{{ $bill->reference_no }}</td>
                        <td>{{ $bill->vendor->name ?? 'N/A' }}</td>
                        <td>{{ $bill->bill_date }}</td>
                        <td>{{ $bill->due_date ?? '-' }}</td>
                        <td>${{ number_format($bill->total, 2) }}</td>
                        <td>
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
                        </td>
                        <td>
                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No vendor bills found</td>
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
