@extends('layouts.app')

@section('page-title', 'Record Customer Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3>Record Customer Payment</h3>
        </div>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
        @csrf

        <div class="row">
            <!-- Payment Information Card -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer *</label>
                                    <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" @if(old('customer_id', $selectedCustomerId) == $customer->id) selected @endif>
                                            {{ $customer->name }} ({{ $customer->customer_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Date *</label>
                                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Amount *</label>
                                    <input type="number" name="amount" id="payment_amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Method</option>
                                        <option value="check" @if(old('payment_method') == 'check') selected @endif>Check</option>
                                        <option value="bank_transfer" @if(old('payment_method') == 'bank_transfer') selected @endif>Bank Transfer</option>
                                        <option value="cash" @if(old('payment_method') == 'cash') selected @endif>Cash</option>
                                        <option value="credit_card" @if(old('payment_method') == 'credit_card') selected @endif>Credit Card</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cash Account *</label>
                                    <select name="cash_account_id" class="form-control @error('cash_account_id') is-invalid @enderror" required>
                                        <option value="">Select Cash Account</option>
                                        @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" @if(old('cash_account_id') == $acc->id) selected @endif>
                                            {{ $acc->number }} - {{ $acc->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('cash_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Reference No.</label>
                                    <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="Check #, Transfer ID, etc.">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Memo</label>
                            <textarea name="memo" class="form-control" rows="3" placeholder="Additional notes">{{ old('memo') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Allocation Section -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Allocate to Invoices (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">You can allocate this payment to customer invoices here. You can also add allocations after creating the payment.</p>
                        
                        <div id="allocations_container">
                            <!-- Allocations will be added here by JavaScript -->
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addAllocationBtn" disabled>
                            <i class="bi bi-plus"></i> Add Invoice Allocation
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Total Payment Amount</label>
                            <h6>$<span id="display_amount">0.00</span></h6>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Total Allocated</label>
                            <h6>$<span id="display_allocated">0.00</span></h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Unallocated Balance</label>
                            <h6 class="text-warning">$<span id="display_unallocated">0.00</span></h6>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-success w-100">Record & Post Payment</button>
                        <a href="{{ route('payments.index') }}" class="btn btn-secondary w-100 mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    const paymentAmountInput = document.getElementById('payment_amount');
    const addAllocationBtn = document.getElementById('addAllocationBtn');
    const allocationsContainer = document.getElementById('allocations_container');

    // Update display amounts
    function updateAmounts() {
        const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
        document.getElementById('display_amount').textContent = paymentAmount.toFixed(2);
        
        // Calculate allocated amount
        const allocatedInputs = document.querySelectorAll('[name^="allocations"][name$="[amount]"]');
        let totalAllocated = 0;
        allocatedInputs.forEach(input => {
            totalAllocated += parseFloat(input.value) || 0;
        });
        
        document.getElementById('display_allocated').textContent = totalAllocated.toFixed(2);
        document.getElementById('display_unallocated').textContent = (paymentAmount - totalAllocated).toFixed(2);
    }

    // Load customer invoices when customer is selected
    customerSelect.addEventListener('change', function() {
        if (this.value) {
            fetch(`/payments/customer/${this.value}/invoices`)
                .then(response => response.json())
                .then(invoices => {
                    addAllocationBtn.disabled = invoices.length === 0;
                    if (invoices.length === 0) {
                        allocationsContainer.innerHTML = '<p class="text-muted small">No open invoices for this customer.</p>';
                    } else {
                        allocationsContainer.innerHTML = '';
                        addAllocationBtn.style.display = 'inline-block';
                    }
                });
        } else {
            addAllocationBtn.disabled = true;
            allocationsContainer.innerHTML = '';
        }
    });

    // Add allocation row
    addAllocationBtn.addEventListener('click', function() {
        const index = allocationsContainer.querySelectorAll('[name^="allocations"]').length / 2;
        
        fetch(`/payments/customer/${customerSelect.value}/invoices`)
            .then(response => response.json())
            .then(invoices => {
                const allocationRow = document.createElement('div');
                allocationRow.className = 'row mb-3 allocation-row';
                allocationRow.innerHTML = `
                    <div class="col-md-8">
                        <select name="allocations[${index}][invoice_id]" class="form-control invoice-select" required>
                            <option value="">Select Invoice</option>
                            ${invoices.map(inv => `<option value="${inv.id}" data-balance="${inv.total - inv.amount_paid}">
                                ${inv.invoice_number} - $${(inv.total - inv.amount_paid).toFixed(2)} due
                            </option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="allocations[${index}][amount]" class="form-control allocation-amount" step="0.01" min="0" placeholder="Amount" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-allocation">×</button>
                    </div>
                `;
                allocationsContainer.appendChild(allocationRow);

                // Add event listeners
                const removeBtn = allocationRow.querySelector('.remove-allocation');
                removeBtn.addEventListener('click', () => {
                    allocationRow.remove();
                    updateAmounts();
                });

                const amountInput = allocationRow.querySelector('.allocation-amount');
                amountInput.addEventListener('change', updateAmounts);

                updateAmounts();
            });
    });

    // Update amounts on input change
    paymentAmountInput.addEventListener('change', updateAmounts);

    // Initial update
    updateAmounts();
    if (customerSelect.value) {
        customerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
