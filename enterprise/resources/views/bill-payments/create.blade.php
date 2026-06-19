@extends('layouts.app')

@section('page-title', 'Pay Vendor Bill')

@section('content')
@php $balanceDue = max(0, $bill->total - $bill->amount_paid); @endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Record Payment for {{ $bill->bill_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Vendor</small>
                                <strong>{{ $bill->vendor->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Bill Total</small>
                                <strong>${{ number_format($bill->total, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Balance Due</small>
                                <strong class="text-danger">${{ number_format($balanceDue, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bill-payments.store', $bill) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount *</label>
                                <input type="number" name="amount" id="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    min="0.01" max="{{ $balanceDue }}" step="0.01"
                                    value="{{ old('amount', $balanceDue) }}" required>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                    <option value="check" @selected(old('payment_method') === 'check')>Check</option>
                                    <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                    <option value="credit_card" @selected(old('payment_method') === 'credit_card')>Credit Card</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="cash_account_id" class="form-label">Pay From Account *</label>
                                <select name="cash_account_id" id="cash_account_id" class="form-select" required>
                                    <option value="">Select Cash Account</option>
                                    @foreach($cashAccounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('cash_account_id') == $account->id)>
                                            {{ $account->number }} - {{ $account->name }} (${{ number_format($account->balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="reference_no" class="form-label">Payment Reference</label>
                                <input type="text" name="reference_no" id="reference_no" class="form-control"
                                    value="{{ old('reference_no') }}" placeholder="Transfer or check number">
                            </div>
                            <div class="col-md-6">
                                <label for="memo" class="form-label">Memo</label>
                                <input type="text" name="memo" id="memo" class="form-control" value="{{ old('memo') }}">
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            Posting this payment reduces Cash, Accounts Payable, the bill balance, and vendor credit used.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Post Vendor Payment</button>
                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
