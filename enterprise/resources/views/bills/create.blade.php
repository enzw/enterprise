@extends('layouts.app')

@section('page-title', 'Create Vendor Bill')

@section('content')
@php
    $posWithItems = \App\Models\PurchaseOrder::with('items.item')->where('status', 'received')->get();
@endphp
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-10">
            <h3>Create New Vendor Bill</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Vendor Bill Entry Form</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bills.store') }}" id="bill-form">
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
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="purchase_order_id" class="form-label">Link to Received PO (Optional)</label>
                            <select name="purchase_order_id" id="purchase_order_id" class="form-control" onchange="handlePOSelection()">
                                <option value="">-- None --</option>
                                @foreach($posWithItems as $po)
                                <option value="{{ $po->id }}" data-vendor-id="{{ $po->vendor_id }}">
                                    {{ $po->po_number }} - {{ $po->vendor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bill_number" class="form-label">Bill Number *</label>
                            <input type="text" name="bill_number" id="bill_number" class="form-control @error('bill_number') is-invalid @enderror" value="{{ old('bill_number') }}" placeholder="e.g. BILL-9999" required>
                            @error('bill_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label">Vendor Invoice / Reference *</label>
                            <input type="text" name="reference_no" id="reference_no" class="form-control @error('reference_no') is-invalid @enderror" value="{{ old('reference_no') }}" placeholder="e.g. INV-12345" required>
                            @error('reference_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bill_date" class="form-label">Bill Date *</label>
                            <input type="date" name="bill_date" id="bill_date" class="form-control @error('bill_date') is-invalid @enderror" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                            @error('bill_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="memo" class="form-label">Memo</label>
                    <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Bill Line Items</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Description *</th>
                                <th style="width: 15%;" class="text-end">Quantity</th>
                                <th style="width: 15%;" class="text-end">Unit Price</th>
                                <th style="width: 20%;" class="text-end">Line Amount *</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="bill-items-tbody">
                            <!-- Dynamic rows loaded here -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong id="total-display">$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-bill-item-btn">+ Add Line Item</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Vendor Bill</button>
                    <a href="{{ route('bills.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const pos = @json($posWithItems);
    let billRowIndex = 0;

    function addBillRow(description = '', quantity = 1, unitPrice = 0, amount = 0) {
        const tbody = document.getElementById('bill-items-tbody');
        const row = document.createElement('tr');
        row.setAttribute('id', `bill-row-${billRowIndex}`);

        row.innerHTML = `
            <td>
                <input type="text" name="items[${billRowIndex}][description]" class="form-control" value="${description}" required>
            </td>
            <td class="text-end">
                <input type="number" name="items[${billRowIndex}][quantity]" class="form-control qty-input" value="${quantity}" min="1" oninput="calculateRow(${billRowIndex})">
            </td>
            <td class="text-end">
                <input type="number" name="items[${billRowIndex}][unit_price]" class="form-control price-input" step="0.01" min="0" value="${unitPrice}" oninput="calculateRow(${billRowIndex})">
            </td>
            <td class="text-end">
                <input type="number" name="items[${billRowIndex}][line_amount]" class="form-control amount-input" step="0.01" min="0" value="${amount}" required oninput="calculateTotal()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeBillRow(${billRowIndex})">×</button>
            </td>
        `;

        tbody.appendChild(row);
        billRowIndex++;
        calculateTotal();
    }

    function calculateRow(index) {
        const row = document.getElementById(`bill-row-${index}`);
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const amountInput = row.querySelector('.amount-input');
        
        amountInput.value = (qty * price).toFixed(2);
        calculateTotal();
    }

    function removeBillRow(index) {
        const row = document.getElementById(`bill-row-${index}`);
        if (document.querySelectorAll('#bill-items-tbody tr').length > 1) {
            row.remove();
            calculateTotal();
        }
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total-display').textContent = `$${total.toFixed(2)}`;
    }

    function handlePOSelection() {
        const poSelect = document.getElementById('purchase_order_id');
        const vendorSelect = document.getElementById('vendor_id');
        const selectedPOId = poSelect.value;
        const tbody = document.getElementById('bill-items-tbody');

        tbody.innerHTML = '';
        billRowIndex = 0;

        if (selectedPOId) {
            const selectedPO = pos.find(p => p.id == selectedPOId);
            if (selectedPO) {
                vendorSelect.value = selectedPO.vendor_id;
                selectedPO.items.forEach(poItem => {
                    const desc = `${poItem.item.sku} - ${poItem.item.name}`;
                    const qty = poItem.quantity_received || poItem.quantity_ordered;
                    const price = poItem.unit_price;
                    const amount = qty * price;
                    addBillRow(desc, qty, price, amount);
                });
            }
        } else {
            addBillRow();
        }
    }

    document.getElementById('add-bill-item-btn').addEventListener('click', () => addBillRow());
    
    // Initial row
    addBillRow();
</script>
@endsection
