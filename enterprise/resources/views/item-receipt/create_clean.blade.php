@extends('layouts.app')

@section('page-title', 'Receive Items - ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3>Receive Items for PO: {{ $purchaseOrder->po_number }}</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Item Receipt Entry</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('item-receipts.store', $purchaseOrder) }}">
                @csrf

                <!-- PO Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->vendor->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Receiving Warehouse Location</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->location->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">PO Status</label>
                            <div>
                                @switch($purchaseOrder->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-info">Approved</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning text-dark">Partially Received</span>
                                        @break
                                    @case('received')
                                        <span class="badge bg-success">Received</span>
                                        @break
                                    @default
                                        <span class="badge bg-info">{{ ucfirst($purchaseOrder->status) }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="receipt_date" class="form-label">Receipt Date *</label>
                            <input type="date" name="receipt_date" id="receipt_date" class="form-control @error('receipt_date') is-invalid @enderror" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                            @error('receipt_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="memo" class="form-label">Memo</label>
                            <input type="text" name="memo" id="memo" class="form-control" placeholder="Optional receipt notes" value="{{ old('memo') }}">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Items to Receive</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-end" style="width: 12%;">Qty Ordered</th>
                                <th class="text-end" style="width: 12%;">Already Received</th>
                                <th class="text-end" style="width: 12%;">Remaining</th>
                                <th style="width: 18%;">Qty to Receive *</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasReceiveable = false; @endphp
                            @foreach($purchaseOrder->items as $index => $poItem)
                                @php
                                    $remaining = $poItem->quantity_ordered - ($poItem->quantity_received ?? 0);
                                @endphp
                                @if($remaining > 0)
                                    @php $hasReceiveable = true; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $poItem->item->sku }}</strong> - {{ $poItem->item->name }}
                                            <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $poItem->id }}">
                                        </td>
                                        <td class="text-end align-middle">{{ $poItem->quantity_ordered }}</td>
                                        <td class="text-end align-middle text-success">{{ $poItem->quantity_received ?? 0 }}</td>
                                        <td class="text-end align-middle text-warning"><strong>{{ $remaining }}</strong></td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity_received]" class="form-control" value="{{ $remaining }}" min="1" max="{{ $remaining }}" required>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="table-light text-muted">
                                        <td>
                                            <strong>{{ $poItem->item->sku }}</strong> - {{ $poItem->item->name }}
                                        </td>
                                        <td class="text-end align-middle">{{ $poItem->quantity_ordered }}</td>
                                        <td class="text-end align-middle text-success">{{ $poItem->quantity_received }}</td>
                                        <td class="text-end align-middle">0</td>
                                        <td>
                                            <span class="badge bg-success">Fully Received</span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                @if($hasReceiveable)
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Submit Item Receipt</button>
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <strong>Info:</strong> All items have been fully received for this Purchase Order.
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary btn-sm ms-2">Back to PO</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
