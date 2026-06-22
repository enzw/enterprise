@extends('layouts.app')

@section('page-title', 'Edit Stock')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Stock for {{ $item->name }} ({{ $item->sku }}) at {{ $location->name ?? 'Default' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('items.updateStock', ['item' => $item->id, 'location' => $location->id]) }}">
                        @csrf
                        @method('POST')
                        <div class="mb-3">
                            <label for="quantity_on_hand" class="form-label">Quantity On Hand *</label>
                            <input type="number" name="quantity_on_hand" id="quantity_on_hand" class="form-control @error('quantity_on_hand') is-invalid @enderror"
                                   value="{{ old('quantity_on_hand', $stock->quantity_on_hand) }}" required min="0">
                            @error('quantity_on_hand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Stock</button>
                            <a href="{{ route('items.manageStocks', $item) }}" class="btn btn-secondary">Back to Stock List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
