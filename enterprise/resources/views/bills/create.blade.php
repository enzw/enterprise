@extends('layouts.app')

@section('page-title', 'Create Vendor Bill')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Create New Vendor Bill</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('bills.store') }}" id="billForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor *</label>
                                    <select name="vendor_id" id="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @if(old('vendor_id') == $vendor->id) selected @endif>
                                            {{ $vendor->name }} ({{ $vendor->vendor_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="bill_number" class="form-label">Bill Number *</label>
                                    <input type="text" name="bill_number" id="bill_number" class="form-control @error('bill_number') is-invalid @enderror" value="{{ old('bill_number') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reference_no" class="form-label">Reference No *</label>
                                    <input type="text" name="reference_no" id="reference_no" class="form-control @error('reference_no') is-invalid @enderror" value="{{ old('reference_no') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="purchase_order_id" class="form-label">Related Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-control">
                                        <option value="">None</option>
                                        @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" @if(old('purchase_order_id') == $po->id) selected @endif>
                                            {{ $po->po_number }} - ${{ number_format($po->total, 2) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="bill_date" class="form-label">Bill Date *</label>
                                    <input type="date" name="bill_date" id="bill_date" class="form-control @error('bill_date') is-invalid @enderror" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="memo" class="form-label">Memo</label>
                            <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                        </div>

                        <hr>
                        <h6 class="mb-3">Bill Line Items</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Description *</th>
                                        <th style="width: 15%">Quantity</th>
                                        <th style="width: 15%">Unit Price</th>
                                        <th style="width: 20%">Line Amount *</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemRows">
                                    <tr class="item-row">
                                        <td>
                                            <input type="text" name="items[0][description]" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control qty-input" min="1" value="1">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][unit_price]" class="form-control price-input" min="0" step="0.01" value="0">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][line_amount]" class="form-control line-amount" min="0" step="0.01" value="0" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove">&times;</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong id="totalAmount">$0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addRow">+ Add Line Item</button>

                        <div class="mb-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Create Bill</button>
                                <a href="{{ route('bills.index') }}" class="btn btn-secondary">Cancel</a>
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
                    <p><small>Enter the vendor's bill number and reference. Optionally link to a received Purchase Order.</small></p>
                    <p><small>The bill will be submitted for approval by an Accounting Manager.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;

document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('itemRows');
    const firstRow = tbody.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll('[name]').forEach(function(el) {
        el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
        if (el.type === 'text') el.value = '';
        if (el.type === 'number') el.value = el.classList.contains('qty-input') ? 1 : 0;
    });

    tbody.appendChild(newRow);
    attachRowEvents(newRow);
    rowIndex++;
});

function calculateLine(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    row.querySelector('.line-amount').value = (qty * price).toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.line-amount').forEach(function(el) {
        total += parseFloat(el.value) || 0;
    });
    document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
}

function attachRowEvents(row) {
    row.querySelector('.qty-input').addEventListener('input', function() { calculateLine(row); });
    row.querySelector('.price-input').addEventListener('input', function() { calculateLine(row); });
    row.querySelector('.line-amount').addEventListener('input', calculateTotal);
    row.querySelector('.remove-row').addEventListener('click', function() {
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            calculateTotal();
        }
    });
}

document.querySelectorAll('.item-row').forEach(attachRowEvents);
</script>
@endsection
