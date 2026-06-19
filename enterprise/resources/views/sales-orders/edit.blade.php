@extends('layouts.app')

@section('page-title', 'Edit Sales Order - ' . $salesOrder->so_number)

@section('content')
@php
    $initialLines = old('items', $salesOrder->items->map(fn ($line) => [
        'item_id' => $line->item_id,
        'quantity' => $line->quantity_ordered,
        'unit_price' => $line->unit_price,
    ])->values()->all());
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit {{ $salesOrder->so_number }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales-orders.update', $salesOrder) }}">
                        @csrf
                        @method('PUT')
                        @include('sales-orders.partials.form', [
                            'initialLines' => $initialLines,
                            'submitLabel' => 'Update Sales Order',
                            'cancelUrl' => route('sales-orders.show', $salesOrder),
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('sales-orders.partials.form-script', ['initialLines' => $initialLines])
@endsection
