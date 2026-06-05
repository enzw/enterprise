@extends('layouts.app')

@section('page-title', 'Create Sales Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Create New Sales Order</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales-orders.store') }}" id="soForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer *</label>
                                    <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" data-subsidiary="{{ $customer->subsidiary_id }}" @if(old('customer_id') == $customer->id) selected @endif>
                                            {{ $customer->name }} ({{ $customer->customer_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="subsidiary_id" class="form-label">Subsidiary *</label>
                                    <input type="hidden" name="subsidiary_id" id="subsidiary_id" value="{{ old('subsidiary_id') }}">
                                    <input type="text" id="subsidiary_display" class="form-control" readonly placeholder="Auto-populated from customer">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="currency_code" class="form-label">Currency *</label>
                                    <select name="currency_code" id="currency_code" class="form-control @error('currency_code') is-invalid @enderror" required>
                                        <option value="USD" @if(old('currency_code', 'USD') == 'USD') selected @endif>USD</option>
                                        <option value="EUR" @if(old('currency_code') == 'EUR') selected @endif>EUR</option>
                                        <option value="GBP" @if(old('currency_code') == 'GBP') selected @endif>GBP</option>
                                        <option value="IDR" @if(old('currency_code') == 'IDR') selected @endif>IDR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="requested_delivery_date" class="form-label">Requested Delivery Date</label>
                                    <input type="date" name="requested_delivery_date" id="requested_delivery_date" class="form-control" value="{{ old('requested_delivery_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="memo" class="form-label">Memo</label>
                            <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                        </div>

                        <hr>
                        <h6 class="mb-3">Line Items</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Item *</th>
                                        <th style="width: 15%">Quantity *</th>
                                        <th style="width: 20%">Unit Price *</th>
                                        <th style="width: 20%">Line Amount</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemRows">
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][item_id]" class="form-control item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                <option value="{{ $item->id }}" data-price="{{ $item->base_price }}">{{ $item->name }} ({{ $item->sku }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control qty-input" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][unit_price]" class="form-control price-input" min="0" step="0.01" value="0" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control line-amount" readonly value="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove">&times;</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td><strong id="subtotal">$0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addRow">+ Add Line Item</button>

                        <div class="mb-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Create Sales Order</button>
                                <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Help</h6>
                    <hr>
                    <p><small>Select a customer to auto-populate the subsidiary. Add items with quantities and prices.</small></p>
                    <p><small>SO number will be generated automatically. The order starts as Draft.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;

document.getElementById('customer_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const subsidiaryId = selected.getAttribute('data-subsidiary');
    document.getElementById('subsidiary_id').value = subsidiaryId || '';
    document.getElementById('subsidiary_display').value = subsidiaryId ? 'Subsidiary #' + subsidiaryId : '';
});
document.getElementById('customer_id').dispatchEvent(new Event('change'));

document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('itemRows');
    const firstRow = tbody.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll('[name]').forEach(function(el) {
        el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
    });
    newRow.querySelector('.item-select').selectedIndex = 0;
    newRow.querySelector('.qty-input').value = 1;
    newRow.querySelector('.price-input').value = 0;
    newRow.querySelector('.line-amount').value = '0.00';

    tbody.appendChild(newRow);
    attachRowEvents(newRow);
    rowIndex++;
});

function calculateLine(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    row.querySelector('.line-amount').value = (qty * price).toFixed(2);
    calculateSubtotal();
}

function calculateSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('.line-amount').forEach(function(el) {
        subtotal += parseFloat(el.value) || 0;
    });
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
}

function attachRowEvents(row) {
    row.querySelector('.qty-input').addEventListener('input', function() { calculateLine(row); });
    row.querySelector('.price-input').addEventListener('input', function() { calculateLine(row); });
    row.querySelector('.item-select').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.getAttribute('data-price');
        if (price) {
            row.querySelector('.price-input').value = parseFloat(price).toFixed(2);
            calculateLine(row);
        }
    });
    row.querySelector('.remove-row').addEventListener('click', function() {
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            calculateSubtotal();
        }
    });
}

document.querySelectorAll('.item-row').forEach(attachRowEvents);
</script>
@endsection
