@extends('layouts.app')

@section('page-title', 'Edit Purchase Order: ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3>Edit Purchase Order: {{ $purchaseOrder->po_number }}</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Update Purchase Order Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
                @csrf
                @method('PUT')

                <!-- Immutable Fields -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Subsidiary</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->vendor->subsidiary->name ?? '-' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->vendor->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Ship To Location</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->location->name }}" disabled>
                        </div>
                    </div>
                </div>

                <!-- Editable Fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="order_date" class="form-label">Order Date *</label>
                            <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $purchaseOrder->order_date) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                            <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date) }}">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="memo" class="form-label">Memo</label>
                    <textarea name="memo" id="memo" class="form-control" rows="3">{{ old('memo', $purchaseOrder->memo) }}</textarea>
                </div>

                <hr class="my-4">

                <!-- Line Items (Read-Only) -->
                <h5 class="mb-3">Order Items <small class="text-muted">(read-only after creation)</small></h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Department</th>
                                <th class="text-end">Quantity Ordered</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Line Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->items as $item)
                            <tr>
                                <td><strong>{{ $item->item->sku }}</strong> - {{ $item->item->name }}</td>
                                <td>{{ $item->department->name ?? '-' }}</td>
                                <td class="text-end">{{ $item->quantity_ordered }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end"><strong>${{ number_format($item->line_amount, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No items in this purchase order</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>${{ number_format($purchaseOrder->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <small class="text-muted d-block mb-4">
                    <strong>Note:</strong> Order items cannot be modified after creation. To change items, you'll need to delete this PO and create a new one.
                </small>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Purchase Order</button>
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
