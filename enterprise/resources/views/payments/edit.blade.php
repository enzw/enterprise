@extends('layouts.app')

@section('page-title', 'Edit Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3>Edit Payment: {{ $payment->payment_number }}</h3>
        </div>
    </div>

    <form method="POST" action="{{ route('payments.update', $payment) }}" id="paymentForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Payment Information Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer</label>
                                    <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Date *</label>
                                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
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
                                    <input type="number" name="amount" id="payment_amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0" value="{{ old('amount', $payment->amount) }}" required>
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
                                        <option value="check" @if(old('payment_method', $payment->payment_method) == 'check') selected @endif>Check</option>
                                        <option value="bank_transfer" @if(old('payment_method', $payment->payment_method) == 'bank_transfer') selected @endif>Bank Transfer</option>
                                        <option value="cash" @if(old('payment_method', $payment->payment_method) == 'cash') selected @endif>Cash</option>
                                        <option value="credit_card" @if(old('payment_method', $payment->payment_method) == 'credit_card') selected @endif>Credit Card</option>
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
                                        <option value="{{ $acc->id }}" @if(old('cash_account_id', $payment->cash_account_id) == $acc->id) selected @endif>
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
                                    <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $payment->reference_no) }}" placeholder="Check #, Transfer ID, etc.">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Memo</label>
                            <textarea name="memo" class="form-control" rows="3" placeholder="Additional notes">{{ old('memo', $payment->memo) }}</textarea>
                        </div>
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
                            <h6>$<span id="display_amount">{{ number_format($payment->amount, 2) }}</span></h6>
                        </div>
                        <hr>
                        @php
                            $allocatedAmount = $payment->allocations()->sum('amount_allocated');
                        @endphp
                        <div class="mb-3">
                            <label class="text-muted small">Total Allocated</label>
                            <h6>${{ number_format($allocatedAmount, 2) }}</h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Unallocated Balance</label>
                            <h6 class="text-warning">${{ number_format($payment->amount - $allocatedAmount, 2) }}</h6>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-success w-100">Update Payment</button>
                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-secondary w-100 mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentAmountInput = document.getElementById('payment_amount');

    paymentAmountInput.addEventListener('change', function() {
        document.getElementById('display_amount').textContent = (parseFloat(this.value) || 0).toFixed(2);
    });
});
</script>
@endsection
