@extends('layouts.app')

@section('page-title', 'Edit Purchase Order: ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Purchase Order: {{ $purchaseOrder->po_number }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Vendor</label>
                                    <input type="text" class="form-control" value="{{ $purchaseOrder->vendor->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" value="{{ $purchaseOrder->location->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">Order Date *</label>
                                    <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $purchaseOrder->order_date) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date) }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="memo" class="form-label">Memo</label>
                            <textarea name="memo" id="memo" class="form-control" rows="3">{{ old('memo', $purchaseOrder->memo) }}</textarea>
                        </div>

                        <hr>
                        <h6 class="mb-3">Line Items <small class="text-muted">(read-only)</small></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty Ordered</th>
                                        <th>Unit Price</th>
                                        <th>Line Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $line->item->name ?? 'N/A' }} ({{ $line->item->sku ?? '' }})</td>
                                        <td>{{ $line->quantity_ordered }}</td>
                                        <td>${{ number_format($line->unit_price, 2) }}</td>
                                        <td>${{ number_format($line->line_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>${{ number_format($purchaseOrder->total, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted">Line items cannot be modified after creation. To change items, delete and re-create the PO.</small>

                        <div class="mt-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Purchase Order</button>
                                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6>PO Details</h6>
                    <hr>
                    <p><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-secondary">{{ ucfirst($purchaseOrder->status) }}</span></p>
                    <p><small><strong>Created:</strong> {{ $purchaseOrder->created_at->format('M d, Y H:i') }}</small></p>
                    <p><small><strong>Updated:</strong> {{ $purchaseOrder->updated_at->format('M d, Y H:i') }}</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
