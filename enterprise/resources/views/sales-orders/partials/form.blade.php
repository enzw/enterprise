<div class="row">
    <div class="col-md-4">
        <label for="customer_id" class="form-label">Customer *</label>
        <select name="customer_id" id="customer_id" class="form-select" required>
            <option value="">Select Customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}"
                    data-subsidiary="{{ $customer->subsidiary_id }}"
                    data-subsidiary-name="{{ $customer->subsidiary->name ?? 'Subsidiary #' . $customer->subsidiary_id }}"
                    @selected(old('customer_id', $salesOrder->customer_id ?? '') == $customer->id)>
                    {{ $customer->name }} ({{ $customer->customer_code }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="location_id" class="form-label">Fulfillment Warehouse *</label>
        <select name="location_id" id="location_id" class="form-select" required>
            <option value="">Select Warehouse</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id', $salesOrder->location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Subsidiary *</label>
        <input type="hidden" name="subsidiary_id" id="subsidiary_id"
            value="{{ old('subsidiary_id', $salesOrder->subsidiary_id ?? '') }}">
        <input type="text" id="subsidiary_display" class="form-control" readonly>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <label for="order_date" class="form-label">Order Date *</label>
        <input type="date" name="order_date" id="order_date" class="form-control"
            value="{{ old('order_date', isset($salesOrder) ? $salesOrder->order_date->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3">
        <label for="requested_delivery_date" class="form-label">Requested Delivery</label>
        <input type="date" name="requested_delivery_date" id="requested_delivery_date" class="form-control"
            value="{{ old('requested_delivery_date', isset($salesOrder) && $salesOrder->requested_delivery_date ? $salesOrder->requested_delivery_date->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-3">
        <label for="currency_code" class="form-label">Currency *</label>
        <select name="currency_code" id="currency_code" class="form-select" required>
            @foreach(['USD', 'IDR', 'EUR', 'GBP'] as $currency)
                <option value="{{ $currency }}" @selected(old('currency_code', $salesOrder->currency_code ?? 'USD') === $currency)>{{ $currency }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label for="po_reference" class="form-label">Customer PO Reference</label>
        <input type="text" name="po_reference" id="po_reference" class="form-control"
            value="{{ old('po_reference', $salesOrder->po_reference ?? '') }}">
    </div>
</div>

<div class="mt-3">
    <label for="memo" class="form-label">Memo</label>
    <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo', $salesOrder->memo ?? '') }}</textarea>
</div>

<hr class="my-4">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Order Line Items</h5>
    <button type="button" class="btn btn-outline-primary btn-sm" id="add-so-row">+ Add Line Item</button>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Item *</th>
                <th style="width: 140px;">Quantity *</th>
                <th style="width: 170px;">Unit Price *</th>
                <th style="width: 170px;" class="text-end">Line Amount</th>
                <th style="width: 55px;"></th>
            </tr>
        </thead>
        <tbody id="so-item-rows"></tbody>
        <tfoot>
            <tr class="table-light">
                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                <td class="text-end"><strong id="so-subtotal">$0.00</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
    <a href="{{ $cancelUrl }}" class="btn btn-secondary">Cancel</a>
</div>
