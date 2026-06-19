<div class="row">
    <div class="col-md-4">
        <label for="vendor_id" class="form-label">Vendor *</label>
        <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
            <option value="">Select Vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}"
                    data-subsidiary="{{ $vendor->subsidiary_id }}"
                    data-subsidiary-name="{{ $vendor->subsidiary->name ?? 'Subsidiary #' . $vendor->subsidiary_id }}"
                    @selected(old('vendor_id', $purchaseOrder->vendor_id ?? '') == $vendor->id)>
                    {{ $vendor->name }} ({{ $vendor->vendor_code }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="location_id" class="form-label">Ship To Location *</label>
        <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
            <option value="">Select Location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id', $purchaseOrder->location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Subsidiary *</label>
        <input type="hidden" name="subsidiary_id" id="subsidiary_id"
            value="{{ old('subsidiary_id', $purchaseOrder->subsidiary_id ?? '') }}">
        <input type="text" id="subsidiary_display" class="form-control" readonly>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label for="order_date" class="form-label">Order Date *</label>
        <input type="date" name="order_date" id="order_date"
            class="form-control @error('order_date') is-invalid @enderror"
            value="{{ old('order_date', isset($purchaseOrder) ? $purchaseOrder->order_date->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
        <input type="date" name="expected_delivery_date" id="expected_delivery_date"
            class="form-control"
            value="{{ old('expected_delivery_date', isset($purchaseOrder) && $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="mt-3">
    <label for="memo" class="form-label">Memo</label>
    <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo', $purchaseOrder->memo ?? '') }}</textarea>
</div>

<hr class="my-4">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Line Items</h5>
    <button type="button" class="btn btn-outline-primary btn-sm" id="add-row">+ Add Line Item</button>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Item *</th>
                <th style="width: 190px;">Department</th>
                <th style="width: 120px;">Quantity *</th>
                <th style="width: 150px;">Unit Price *</th>
                <th style="width: 160px;" class="text-end">Line Amount</th>
                <th style="width: 55px;"></th>
            </tr>
        </thead>
        <tbody id="item-rows"></tbody>
        <tfoot>
            <tr class="table-light">
                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                <td class="text-end"><strong id="subtotal">$0.00</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
    <a href="{{ $cancelUrl }}" class="btn btn-secondary">Cancel</a>
</div>
