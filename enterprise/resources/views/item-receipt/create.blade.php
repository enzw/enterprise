@extends('layouts.app')

@section('page-title', 'Receive Items - ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Item Receipt for {{ $purchaseOrder->po_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Vendor:</strong> {{ $purchaseOrder->vendor->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Location:</strong> {{ $purchaseOrder->location->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>PO Status:</strong> <span class="badge bg-success">{{ ucfirst($purchaseOrder->status) }}</span></p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('item-receipts.store', $purchaseOrder) }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="receipt_date" class="form-label">Receipt Date *</label>
                                <input type="date" name="receipt_date" id="receipt_date" class="form-control @error('receipt_date') is-invalid @enderror" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label for="memo" class="form-label">Memo</label>
                                <input type="text" name="memo" id="memo" class="form-control" value="{{ old('memo') }}">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Items to Receive</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Ordered</th>
                                        <th>Already Received</th>
                                        <th>Remaining</th>
                                        <th>Receive Qty *</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $index => $line)
                                    @php
                                        $remaining = $line->quantity_ordered - ($line->quantity_received ?? 0);
                                    @endphp
                                    @if($remaining > 0)
                                    <tr>
                                        <td>
                                            {{ $line->item->name ?? 'N/A' }} <small class="text-muted">({{ $line->item->sku ?? '' }})</small>
                                            <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $line->id }}">
                                        </td>
                                        <td>{{ $line->quantity_ordered }}</td>
                                        <td>{{ $line->quantity_received ?? 0 }}</td>
                                        <td><strong>{{ $remaining }}</strong></td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity_received]" class="form-control" min="1" max="{{ $remaining }}" value="{{ $remaining }}" required>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Receive Items</button>
                            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
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
                    <p><small>Enter the quantities received for each item. Only items with remaining quantities are shown.</small></p>
                    <p><small>Inventory stock will be updated automatically.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
