@extends('layouts.app')

@section('page-title', 'Create Sales Order')

@section('content')
@php
    $subsidiaries = \App\Models\Subsidiary::all();
@endphp
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-10">
            <h3>Create New Sales Order</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Sales Order Entry Form</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('sales-orders.store') }}" id="so-form">
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
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <label for="currency_code" class="form-label">Currency Code *</label>
                            <select name="currency_code" id="currency_code" class="form-control @error('currency_code') is-invalid @enderror" required>
                                <option value="USD" @if(old('currency_code', 'USD') == 'USD') selected @endif>USD</option>
                                <option value="IDR" @if(old('currency_code') == 'IDR') selected @endif>IDR</option>
                                <option value="EUR" @if(old('currency_code') == 'EUR') selected @endif>EUR</option>
                                <option value="GBP" @if(old('currency_code') == 'GBP') selected @endif>GBP</option>
                            </select>
                            @error('currency_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="order_date" class="form-label">Order Date *</label>
                            <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
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

                <hr class="my-4">
                <h5 class="mb-3">Order Line Items</h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="so-items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Item *</th>
                                <th style="width: 15%;" class="text-end">Quantity *</th>
                                <th style="width: 15%;" class="text-end">Unit Price *</th>
                                <th style="width: 20%;" class="text-end">Line Amount</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="so-items-tbody">
                            <!-- Dynamic rows will be inserted here -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong id="subtotal-display">$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-so-item-btn">+ Add Line Item</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Sales Order</button>
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const items = @json($items);
    let soRowIndex = 0;

    // Handle customer selection to auto-populate subsidiary
    document.getElementById('customer_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const subsidiaryId = selected.getAttribute('data-subsidiary');
        document.getElementById('subsidiary_id').value = subsidiaryId || '';
        document.getElementById('subsidiary_display').value = subsidiaryId ? 'Subsidiary #' + subsidiaryId : '';
    });
    document.getElementById('customer_id').dispatchEvent(new Event('change'));

    function addSORow() {
        const tbody = document.getElementById('so-items-tbody');
        const row = document.createElement('tr');
        row.setAttribute('id', `so-row-${soRowIndex}`);

        let itemOptions = '<option value="">Select Item</option>';
        items.forEach(item => {
            itemOptions += `<option value="${item.id}" data-price="${item.base_price || 0}">${item.sku} - ${item.name}</option>`;
        });

        row.innerHTML = `
            <td>
                <select name="items[${soRowIndex}][item_id]" class="form-control item-select" required onchange="handleItemChange(${soRowIndex})">
                    ${itemOptions}
                </select>
            </td>
            <td class="text-end">
                <input type="number" name="items[${soRowIndex}][quantity]" class="form-control qty-input" value="1" min="1" required oninput="calculateRowAmount(${soRowIndex})">
            </td>
            <td class="text-end">
                <input type="number" name="items[${soRowIndex}][unit_price]" class="form-control price-input" step="0.01" min="0" required oninput="calculateRowAmount(${soRowIndex})">
            </td>
            <td class="text-end align-middle">
                <strong class="row-amount">$0.00</strong>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSORow(${soRowIndex})">×</button>
            </td>
        `;

        tbody.appendChild(row);
        soRowIndex++;
        calculateTotal();
    }

    function handleItemChange(index) {
        const row = document.getElementById(`so-row-${index}`);
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
        const row = document.getElementById(`so-row-${index}`);
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const amountCell = row.querySelector('.row-amount');
        
        const amount = qty * price;
        amountCell.textContent = `$${amount.toFixed(2)}`;
        calculateTotal();
    }

    function removeSORow(index) {
        if (document.querySelectorAll('#so-items-tbody tr').length > 1) {
            const row = document.getElementById(`so-row-${index}`);
            row.remove();
            calculateTotal();
        }
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.row-amount').forEach(cell => {
            const amount = parseFloat(cell.textContent.replace('$', '')) || 0;
            total += amount;
        });

        document.getElementById('subtotal-display').textContent = `$${total.toFixed(2)}`;
    }

    document.getElementById('add-so-item-btn').addEventListener('click', addSORow);

    // Add initial row
    addSORow();
</script>
@endsection
