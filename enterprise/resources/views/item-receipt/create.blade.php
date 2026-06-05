@extends('layouts.app')

@section('page-title', 'Receive Items')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Receive Items for PO: {{ $purchaseOrder->po_number }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('item-receipts.store', $purchaseOrder) }}">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->vendor->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Receiving Warehouse Location</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->location->name }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="receipt_date" class="form-label">Receipt Date *</label>
                            <input type="date" name="receipt_date" id="receipt_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="memo" class="form-label">Memo</label>
                            <input type="text" name="memo" id="memo" class="form-control" placeholder="Optional receipt notes">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Items to Receive</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item Description</th>
                                <th class="text-end" style="width: 15%;">Qty Ordered</th>
                                <th class="text-end" style="width: 15%;">Qty Already Received</th>
                                <th class="text-end" style="width: 15%;">Remaining</th>
                                <th style="width: 20%;">Qty to Receive *</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasReceiveable = false; @endphp
                            @foreach($purchaseOrder->items as $index => $poItem)
                                @php
                                    $remaining = $poItem->quantity_ordered - $poItem->quantity_received;
                                @endphp
                                @if($remaining > 0)
                                    @php $hasReceiveable = true; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $poItem->item->sku }}</strong> - {{ $poItem->item->name }}
                                            <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $poItem->id }}">
                                        </td>
                                        <td class="text-end align-middle">{{ $poItem->quantity_ordered }}</td>
                                        <td class="text-end align-middle text-success">{{ $poItem->quantity_received }}</td>
                                        <td class="text-end align-middle text-warning font-weight-bold">{{ $remaining }}</td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity_received]" class="form-control" value="{{ $remaining }}" min="1" max="{{ $remaining }}" required>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="table-light text-muted">
                                        <td>
                                            <strong>{{ $poItem->item->sku }}</strong> - {{ $poItem->item->name }} (Fully Received)
                                        </td>
                                        <td class="text-end align-middle">{{ $poItem->quantity_ordered }}</td>
                                        <td class="text-end align-middle text-success">{{ $poItem->quantity_received }}</td>
                                        <td class="text-end align-middle">0</td>
                                        <td>
                                            <span class="badge bg-success">Received</span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($hasReceiveable)
                    <button type="submit" class="btn btn-primary">Submit Item Receipt</button>
                @else
                    <div class="alert alert-info">All items have been fully received for this Purchase Order.</div>
                @endif
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
