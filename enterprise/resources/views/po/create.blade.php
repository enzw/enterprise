@extends('layouts.app')

@section('page-title', 'Create Purchase Order')

@section('content')
@php
    $subsidiaries = \App\Models\Subsidiary::all();
@endphp
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">New Purchase Order</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('purchase-orders.store') }}" id="po-form">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="subsidiary_id" class="form-label">Subsidiary *</label>
                            <select name="subsidiary_id" id="subsidiary_id" class="form-control" required>
                                <option value="">Select Subsidiary</option>
                                @foreach($subsidiaries as $sub)
                                <option value="{{ $sub->id }}" @if(old('subsidiary_id') == $sub->id) selected @endif>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor *</label>
                            <select name="vendor_id" id="vendor_id" class="form-control" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @if(old('vendor_id') == $vendor->id) selected @endif>{{ $vendor->name }} ({{ $vendor->vendor_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="location_id" class="form-label">Location *</label>
                            <select name="location_id" id="location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" @if(old('location_id') == $loc->id) selected @endif>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="order_date" class="form-label">Order Date *</label>
                            <input type="date" name="order_date" id="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                            <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date') }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="memo" class="form-label">Memo</label>
                    <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Order Items</h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Item *</th>
                                <th style="width: 20%;">Department</th>
                                <th style="width: 15%;">Quantity *</th>
                                <th style="width: 15%;">Unit Price *</th>
                                <th style="width: 15%;">Amount</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <!-- Dynamic rows will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-item-btn">+ Add Line</button>

                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong id="subtotal-display">$0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total:</span>
                                    <strong id="total-display" class="text-success">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Purchase Order</button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const items = @json($items);
    const departments = @json($departments);
    let rowIndex = 0;

    function addItemRow() {
        const tbody = document.getElementById('items-tbody');
        const row = document.createElement('tr');
        row.setAttribute('id', `row-${rowIndex}`);

        let itemOptions = '<option value="">Select Item</option>';
        items.forEach(item => {
            itemOptions += `<option value="${item.id}" data-price="${item.purchase_price || 0}">${item.sku} - ${item.name}</option>`;
        });

        let deptOptions = '<option value="">None</option>';
        departments.forEach(dept => {
            deptOptions += `<option value="${dept.id}">${dept.name}</option>`;
        });

        row.innerHTML = `
            <td>
                <select name="items[${rowIndex}][item_id]" class="form-control item-select" required onchange="handleItemChange(${rowIndex})">
                    ${itemOptions}
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][department_id]" class="form-control">
                    ${deptOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" min="1" required oninput="calculateRowAmount(${rowIndex})">
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][unit_price]" class="form-control price-input" step="0.01" min="0" required oninput="calculateRowAmount(${rowIndex})">
            </td>
            <td class="align-middle text-end font-weight-bold row-amount">
                $0.00
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${rowIndex})">✕</button>
            </td>
        `;

        tbody.appendChild(row);
        rowIndex++;
        calculateTotal();
    }

    function handleItemChange(index) {
        const row = document.getElementById(`row-${index}`);
        const select = row.querySelector('.item-select');
        const priceInput = row.querySelector('.price-input');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.dataset.price) {
            priceInput.value = selectedOption.dataset.price;
        } else {
            priceInput.value = 0;
        }
        calculateRowAmount(index);
    }

    function calculateRowAmount(index) {
        const row = document.getElementById(`row-${index}`);
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const amountCell = row.querySelector('.row-amount');
        
        const amount = qty * price;
        amountCell.textContent = `$${amount.toFixed(2)}`;
        calculateTotal();
    }

    function removeRow(index) {
        const row = document.getElementById(`row-${index}`);
        row.remove();
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.row-amount').forEach(cell => {
            const amount = parseFloat(cell.textContent.replace('$', '')) || 0;
            total += amount;
        });

        document.getElementById('subtotal-display').textContent = `$${total.toFixed(2)}`;
        document.getElementById('total-display').textContent = `$${total.toFixed(2)}`;
    }

    document.getElementById('add-item-btn').addEventListener('click', addItemRow);

    // Add initial row
    addItemRow();
</script>
@endsection
