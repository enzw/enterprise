@extends('layouts.app')

@section('page-title', 'Receive Items - ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Item Receipt for {{ $purchaseOrder->po_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <small class="text-muted d-block">Vendor</small>
                                <strong>{{ $purchaseOrder->vendor->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <small class="text-muted d-block">Receiving Location</small>
                                <strong>{{ $purchaseOrder->location->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <small class="text-muted d-block">PO Status</small>
                                <span class="badge {{ $purchaseOrder->status === 'partial' ? 'bg-warning text-dark' : 'bg-info' }}">
                                    {{ $purchaseOrder->status === 'partial' ? 'Partially Received' : 'Approved' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('item-receipts.store', $purchaseOrder) }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="receipt_date" class="form-label">Receipt Date *</label>
                                <input type="date" name="receipt_date" id="receipt_date"
                                    class="form-control @error('receipt_date') is-invalid @enderror"
                                    value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label for="memo" class="form-label">Memo</label>
                                <input type="text" name="memo" id="memo" class="form-control"
                                    value="{{ old('memo') }}" placeholder="Delivery note, courier, or receiving notes">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Items to Receive</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Ordered</th>
                                        <th class="text-end">Received</th>
                                        <th class="text-end">Remaining</th>
                                        <th style="width: 180px;">Receive Now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasReceiveable = false; @endphp
                                    @foreach($purchaseOrder->items as $index => $line)
                                        @php $remaining = $line->quantity_ordered - $line->quantity_received; @endphp
                                        <tr class="{{ $remaining === 0 ? 'table-light text-muted' : '' }}">
                                            <td>
                                                <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                                @if($line->item->type !== 'inventory')
                                                    <span class="badge bg-light text-dark border ms-1">{{ str_replace('_', ' ', $line->item->type) }}</span>
                                                @endif
                                                <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $line->id }}">
                                            </td>
                                            <td class="text-end">{{ $line->quantity_ordered }}</td>
                                            <td class="text-end text-success">{{ $line->quantity_received }}</td>
                                            <td class="text-end"><strong>{{ $remaining }}</strong></td>
                                            <td>
                                                @if($remaining > 0)
                                                    @php $hasReceiveable = true; @endphp
                                                    <input type="number"
                                                        name="items[{{ $index }}][quantity_received]"
                                                        class="form-control"
                                                        min="0"
                                                        max="{{ $remaining }}"
                                                        value="{{ old("items.$index.quantity_received", $remaining) }}">
                                                @else
                                                    <span class="badge bg-success">Fully Received</span>
                                                    <input type="hidden" name="items[{{ $index }}][quantity_received]" value="0">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($hasReceiveable)
                            <div class="alert alert-info">
                                You may enter less than the remaining quantity to test a partial receipt. Enter 0 for lines not received in this delivery.
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Post Item Receipt</button>
                                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">All PO quantities have been received.</div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Receiving Rules</h6>
                    <hr>
                    <p class="small">A receipt cannot exceed the remaining PO quantity.</p>
                    <p class="small mb-0">Only inventory items update warehouse stock. Services and non-inventory lines remain traceable without increasing stock.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
