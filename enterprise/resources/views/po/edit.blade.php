@extends('layouts.app')

@section('page-title', 'Edit Purchase Order - ' . $purchaseOrder->po_number)

@section('content')
@php
    $initialLines = old('items', $purchaseOrder->items->map(fn ($line) => [
        'item_id' => $line->item_id,
        'department_id' => $line->department_id,
        'quantity' => $line->quantity_ordered,
        'unit_price' => $line->unit_price,
    ])->values()->all());
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit {{ $purchaseOrder->po_number }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}" id="po-form">
                        @csrf
                        @method('PUT')
                        @include('po.partials.form', [
                            'initialLines' => $initialLines,
                            'submitLabel' => 'Update Purchase Order',
                            'cancelUrl' => route('purchase-orders.show', $purchaseOrder),
                        ])
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Draft Editing</h6>
                    <hr>
                    <p class="small mb-0">Vendor, destination, dates, quantities, prices, and line items can be changed while the PO is still draft.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('po.partials.form-script', ['initialLines' => $initialLines])
@endsection
