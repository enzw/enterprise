@extends('layouts.app')

@section('page-title', 'Pack Items - ' . $salesOrder->so_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-info text-white"><h5 class="mb-0">Pack Picked Items for {{ $salesOrder->so_number }}</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales-orders.pack.store', $salesOrder) }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Pack Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Packaging type or handling notes">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr><th>Item</th><th class="text-end">Picked</th><th class="text-end">Packed</th><th class="text-end">Available</th><th style="width:180px">Pack Now</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrder->items as $index => $line)
                                        @php $available = $line->quantity_fulfilled - $line->quantity_packed; @endphp
                                        @if($available > 0)
                                            <tr>
                                                <td>
                                                    <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                                    <input type="hidden" name="items[{{ $index }}][so_item_id]" value="{{ $line->id }}">
                                                </td>
                                                <td class="text-end">{{ $line->quantity_fulfilled }}</td>
                                                <td class="text-end">{{ $line->quantity_packed }}</td>
                                                <td class="text-end"><strong>{{ $available }}</strong></td>
                                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="0" max="{{ $available }}" value="{{ old("items.$index.quantity", $available) }}"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-info text-white" type="submit">Complete Pack</button>
                            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
