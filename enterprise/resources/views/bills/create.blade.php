@extends('layouts.app')

@section('page-title', 'Create Vendor Bill')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Vendor Bill Entry</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('bills.store') }}" id="bill-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <label for="purchase_order_id" class="form-label">Received Purchase Order</label>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-select">
                                    <option value="">Standalone Bill</option>
                                    @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" @selected(old('purchase_order_id', $selectedPurchaseOrderId) == $po->id)>
                                            {{ $po->po_number }} - {{ $po->vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Only POs with received, unbilled quantities appear here.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="vendor_id" class="form-label">Vendor *</label>
                                <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>
                                            {{ $vendor->name }} ({{ $vendor->vendor_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="bill_number" class="form-label">Internal Bill Number *</label>
                                <input type="text" name="bill_number" id="bill_number"
                                    class="form-control @error('bill_number') is-invalid @enderror"
                                    value="{{ old('bill_number') }}" placeholder="BILL-0001" required>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label for="reference_no" class="form-label">Vendor Invoice Reference *</label>
                                <input type="text" name="reference_no" id="reference_no"
                                    class="form-control @error('reference_no') is-invalid @enderror"
                                    value="{{ old('reference_no') }}" placeholder="INV-VENDOR-001" required>
                            </div>
                            <div class="col-md-4">
                                <label for="bill_date" class="form-label">Bill Date *</label>
                                <input type="date" name="bill_date" id="bill_date"
                                    class="form-control" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date"
                                    class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="memo" class="form-label">Memo</label>
                            <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-0">Bill Line Items</h5>
                                <small class="text-muted">PO lines are limited to received quantities that have not been billed.</small>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-bill-item-btn">+ Add Standalone Line</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th style="width: 150px;" class="text-end">Available Qty</th>
                                        <th style="width: 150px;" class="text-end">Bill Qty</th>
                                        <th style="width: 160px;" class="text-end">Unit Price</th>
                                        <th style="width: 170px;" class="text-end">Line Amount</th>
                                        <th style="width: 55px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="bill-items-tbody"></tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong id="total-display">$0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success">Create & Submit Bill</button>
                            <a href="{{ route('bills.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>3-Way Match</h6>
                    <hr>
                    <p class="small">The application compares the PO quantity, received quantity, and billed quantity.</p>
                    <p class="small mb-0">Linked PO prices are locked to prevent accidental invoice overstatement.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const purchaseOrders = @json($purchaseOrders);
const oldItems = @json(old('items', []));
let rowIndex = 0;

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function addStandaloneRow(values = {}) {
    const tbody = document.getElementById('bill-items-tbody');
    const index = rowIndex++;
    const row = document.createElement('tr');
    row.dataset.kind = 'standalone';
    row.innerHTML = `
        <td>
            <input type="text" name="items[${index}][description]" class="form-control"
                value="${escapeHtml(values.description ?? '')}" required>
        </td>
        <td class="text-end text-muted">—</td>
        <td>
            <input type="number" name="items[${index}][quantity]" class="form-control qty-input"
                min="0" value="${escapeHtml(values.quantity ?? 1)}">
        </td>
        <td>
            <input type="number" name="items[${index}][unit_price]" class="form-control price-input"
                min="0" step="0.01" value="${escapeHtml(values.unit_price ?? 0)}">
        </td>
        <td>
            <input type="number" name="items[${index}][line_amount]" class="form-control amount-input"
                min="0" step="0.01" value="${escapeHtml(values.line_amount ?? 0)}" required>
        </td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
    `;
    tbody.appendChild(row);
    attachRowEvents(row);
    calculateRow(row);
}

function addPoRow(poItem) {
    const tbody = document.getElementById('bill-items-tbody');
    const index = rowIndex++;
    const available = poItem.quantity_received - poItem.quantity_billed;
    const amount = available * Number(poItem.unit_price);
    const row = document.createElement('tr');
    row.dataset.kind = 'po';
    row.innerHTML = `
        <td>
            <strong>${escapeHtml(poItem.item.sku)}</strong> - ${escapeHtml(poItem.item.name)}
            <input type="hidden" name="items[${index}][po_item_id]" value="${poItem.id}">
            <input type="hidden" name="items[${index}][item_id]" value="${poItem.item_id}">
            <input type="hidden" name="items[${index}][description]" value="${escapeHtml(poItem.item.sku + ' - ' + poItem.item.name)}">
        </td>
        <td class="text-end"><strong>${available}</strong></td>
        <td>
            <input type="number" name="items[${index}][quantity]" class="form-control qty-input"
                min="0" max="${available}" value="${available}">
        </td>
        <td>
            <input type="number" name="items[${index}][unit_price]" class="form-control price-input"
                value="${Number(poItem.unit_price).toFixed(2)}" readonly>
        </td>
        <td>
            <input type="number" name="items[${index}][line_amount]" class="form-control amount-input"
                value="${amount.toFixed(2)}" readonly>
        </td>
        <td></td>
    `;
    tbody.appendChild(row);
    attachRowEvents(row);
}

function attachRowEvents(row) {
    row.querySelector('.qty-input')?.addEventListener('input', () => calculateRow(row));
    row.querySelector('.price-input')?.addEventListener('input', () => calculateRow(row));
    row.querySelector('.amount-input')?.addEventListener('input', calculateTotal);
    row.querySelector('.remove-row')?.addEventListener('click', () => {
        row.remove();
        if (!document.querySelector('#bill-items-tbody tr')) {
            addStandaloneRow();
        }
        calculateTotal();
    });
}

function calculateRow(row) {
    const qty = Number(row.querySelector('.qty-input')?.value || 0);
    const price = Number(row.querySelector('.price-input')?.value || 0);
    const amount = row.querySelector('.amount-input');
    if (amount) {
        amount.value = (qty * price).toFixed(2);
    }
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.amount-input').forEach(input => total += Number(input.value || 0));
    document.getElementById('total-display').textContent = '$' + total.toFixed(2);
}

function loadPurchaseOrder() {
    const poId = document.getElementById('purchase_order_id').value;
    const tbody = document.getElementById('bill-items-tbody');
    tbody.innerHTML = '';
    rowIndex = 0;

    if (!poId) {
        document.getElementById('vendor_id').disabled = false;
        document.getElementById('add-bill-item-btn').classList.remove('d-none');
        addStandaloneRow();
        return;
    }

    const po = purchaseOrders.find(entry => String(entry.id) === String(poId));
    if (!po) return;

    document.getElementById('vendor_id').value = po.vendor_id;
    document.getElementById('vendor_id').disabled = false;
    document.getElementById('add-bill-item-btn').classList.add('d-none');
    po.items.forEach(addPoRow);
    calculateTotal();
}

document.getElementById('purchase_order_id').addEventListener('change', loadPurchaseOrder);
document.getElementById('add-bill-item-btn').addEventListener('click', () => addStandaloneRow());

if (document.getElementById('purchase_order_id').value) {
    loadPurchaseOrder();
} else if (oldItems.length) {
    oldItems.forEach(addStandaloneRow);
} else {
    addStandaloneRow();
}
</script>
@endsection
