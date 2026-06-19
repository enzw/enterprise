@extends('layouts.app')

@section('page-title', 'Create Sales Order')

@section('content')
@php
    $initialLines = old('items', [['item_id' => '', 'quantity' => 1, 'unit_price' => 0]]);
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Create New Sales Order</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales-orders.store') }}">
                        @csrf
                        @include('sales-orders.partials.form', [
                            'salesOrder' => null,
                            'initialLines' => $initialLines,
                            'submitLabel' => 'Create Sales Order',
                            'cancelUrl' => route('sales-orders.index'),
                        ])
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Order Entry</h6>
                    <hr>
                    <p class="small">Choose the warehouse that will fulfill this order.</p>
                    <p class="small mb-0">Credit and available inventory are checked when the Sales Manager approves the order.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('sales-orders.partials.form-script', ['initialLines' => $initialLines])
@endsection
