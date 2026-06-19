@extends('layouts.app')

@section('page-title', 'Create Purchase Order')

@section('content')
@php
    $initialLines = old('items', [['item_id' => '', 'department_id' => '', 'quantity' => 1, 'unit_price' => 0]]);
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Create New Purchase Order</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchase-orders.store') }}" id="po-form">
                        @csrf
                        @include('po.partials.form', [
                            'purchaseOrder' => null,
                            'initialLines' => $initialLines,
                            'submitLabel' => 'Create Purchase Order',
                            'cancelUrl' => route('purchase-orders.index'),
                        ])
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Purchase Order</h6>
                    <hr>
                    <p class="small">Select one subsidiary, vendor, location, and at least one item.</p>
                    <p class="small mb-0">After creation, approve the PO before the warehouse can receive it.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('po.partials.form-script', ['initialLines' => $initialLines])
@endsection
