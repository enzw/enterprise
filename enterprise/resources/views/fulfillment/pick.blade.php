@extends('layouts.app')

@section('page-title', 'Pick Items - ' . $salesOrder->so_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white"><h5 class="mb-0">Pick Items for {{ $salesOrder->so_number }}</h5></div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Warehouse: <strong>{{ $salesOrder->location->name }}</strong>. Picking inventory items reserves stock until shipment.
                    </div>
                    <form method="POST" action="{{ route('sales-orders.pick.store', $salesOrder) }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Pick Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr><th>Item</th><th class="text-end">Ordered</th><th class="text-end">Picked</th><th class="text-end">Remaining</th><th style="width:180px">Pick Now</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrder->items as $index => $line)
                                        @php $remaining = $line->quantity_ordered - $line->quantity_fulfilled; @endphp
                                        @if($remaining > 0)
                                            <tr>
                                                <td>
                                                    <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                                    <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $line->item->type) }}</span>
                                                    <input type="hidden" name="items[{{ $index }}][so_item_id]" value="{{ $line->id }}">
                                                </td>
                                                <td class="text-end">{{ $line->quantity_ordered }}</td>
                                                <td class="text-end">{{ $line->quantity_fulfilled }}</td>
                                                <td class="text-end"><strong>{{ $remaining }}</strong></td>
                                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="0" max="{{ $remaining }}" value="{{ old("items.$index.quantity", $remaining) }}"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Complete Pick</button>
                            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
