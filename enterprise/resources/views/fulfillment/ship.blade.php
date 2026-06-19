@extends('layouts.app')

@section('page-title', 'Ship Items - ' . $salesOrder->so_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-dark text-white"><h5 class="mb-0">Ship Packed Items for {{ $salesOrder->so_number }}</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales-orders.ship.store', $salesOrder) }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Ship Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Carrier</label>
                                <input type="text" name="carrier" class="form-control" value="{{ old('carrier') }}" placeholder="DHL, FedEx, JNE">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control" value="{{ old('tracking_number') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr><th>Item</th><th class="text-end">Packed</th><th class="text-end">Shipped</th><th class="text-end">Available</th><th style="width:180px">Ship Now</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrder->items as $index => $line)
                                        @php $available = $line->quantity_packed - $line->quantity_shipped; @endphp
                                        @if($available > 0)
                                            <tr>
                                                <td>
                                                    <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                                    <input type="hidden" name="items[{{ $index }}][so_item_id]" value="{{ $line->id }}">
                                                </td>
                                                <td class="text-end">{{ $line->quantity_packed }}</td>
                                                <td class="text-end">{{ $line->quantity_shipped }}</td>
                                                <td class="text-end"><strong>{{ $available }}</strong></td>
                                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="0" max="{{ $available }}" value="{{ old("items.$index.quantity", $available) }}"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-warning">
                            Posting the shipment reduces on-hand and reserved inventory, then posts inventory cost to COGS.
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark" type="submit">Complete Shipment</button>
                            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
