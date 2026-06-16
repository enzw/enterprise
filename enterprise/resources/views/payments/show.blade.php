@extends('layouts.app')

@section('page-title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Payment: {{ $payment->payment_number }}</h3>
        </div>
        <div class="col-md-6 text-end">
            @if($unallocatedAmount > 0 && in_array(auth()->user()->current_role, ['admin', 'ar_analyst']))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal">+ Allocate Payment</button>
            @endif
            @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']))
            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-warning">Edit</a>
            @endif
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <!-- Payment Details -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment Number:</strong> {{ $payment->payment_number }}</p>
                            <p><strong>Customer:</strong> {{ $payment->customer->name }}</p>
                            <p><strong>Payment Date:</strong> {{ $payment->payment_date->format('M d, Y') }}</p>
                            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> <span class="text-success h5">${{ number_format($payment->amount, 2) }}</span></p>
                            <p><strong>Reference No:</strong> {{ $payment->reference_no ?? '-' }}</p>
                            <p><strong>Created By:</strong> {{ $payment->createdBy->name }}</p>
                            <p><strong>Created:</strong> {{ $payment->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @if($payment->memo)
                    <hr>
                    <p><strong>Memo:</strong></p>
                    <p>{{ $payment->memo }}</p>
                    @endif
                </div>
            </div>

            <!-- Allocations -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Allocations</h5>
                </div>
                <div class="card-body">
                    @if($payment->allocations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Invoice Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Allocated Amount</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->allocations as $allocation)
                                <tr>
                                    <td>
                                        <a href="#" class="text-decoration-none">{{ $allocation->invoice->invoice_number }}</a>
                                    </td>
                                    <td>${{ number_format($allocation->invoice->total, 2) }}</td>
                                    <td>${{ number_format($allocation->invoice->amount_paid, 2) }}</td>
                                    <td><strong>${{ number_format($allocation->amount_allocated, 2) }}</strong></td>
                                    <td>
                                        @if($allocation->invoice->amount_paid >= $allocation->invoice->total)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning">Partial</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']))
                                        <form method="POST" action="{{ route('payments.remove-allocation', $allocation) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this allocation?')">Remove</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No allocations yet. Click "Allocate Payment" to assign this payment to invoices.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small">Total Amount</label>
                        </div>
                        <div class="col-6 text-end">
                            <h6>${{ number_format($payment->amount, 2) }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small">Allocated</label>
                        </div>
                        <div class="col-6 text-end">
                            <h6>${{ number_format($allocatedAmount, 2) }}</h6>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small"><strong>Unallocated</strong></label>
                        </div>
                        <div class="col-6 text-end">
                            @if($unallocatedAmount > 0)
                                <h6 class="text-warning"><strong>${{ number_format($unallocatedAmount, 2) }}</strong></h6>
                            @else
                                <h6 class="text-success"><strong>Complete</strong></h6>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Open Invoices -->
            @if($openInvoices->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Open Invoices</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($openInvoices as $invoice)
                        <div class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="text-decoration-none">{{ $invoice->invoice_number }}</strong>
                                    <p class="small text-muted mb-0">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                                </div>
                                <div class="col-auto text-end">
                                    <p class="small mb-0">${{ number_format($invoice->total - $invoice->amount_paid, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Allocate Payment Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Allocate Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('payments.allocate', $payment) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Invoice *</label>
                        <select name="invoice_id" class="form-control" id="invoiceSelect" required>
                            <option value="">Select Invoice</option>
                            @foreach($openInvoices as $invoice)
                            @php
                                $balance = $invoice->total - $invoice->amount_paid;
                            @endphp
                            <option value="{{ $invoice->id }}" data-balance="{{ $balance }}">
                                {{ $invoice->invoice_number }} - ${{ number_format($balance, 2) }} due
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount to Allocate *</label>
                        <input type="number" name="amount" class="form-control" id="allocateAmount" step="0.01" min="0" max="{{ $unallocatedAmount }}" placeholder="0.00" required>
                        <small class="text-muted">Available: ${{ number_format($unallocatedAmount, 2) }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoiceSelect');
    const allocateAmount = document.getElementById('allocateAmount');

    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', function() {
            if (this.value) {
                const selected = this.options[this.selectedIndex];
                const balance = parseFloat(selected.dataset.balance) || 0;
                allocateAmount.max = Math.min({{ $unallocatedAmount }}, balance);
                allocateAmount.placeholder = `Max: $${balance.toFixed(2)}`;
            }
        });
    }
});
</script>
@endsection
