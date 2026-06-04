@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard - ' . auth()->user()->current_role)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Welcome, {{ auth()->user()->name }}!</h5>
                </div>
                <div class="card-body">
                    <p>Current Role: <span class="badge bg-info">{{ str_replace('_', ' ', strtoupper(auth()->user()->current_role)) }}</span></p>
                    
                    @if(count(json_decode(auth()->user()->available_roles, true)) > 1)
                    <div class="alert alert-info">
                        <h6>Available Roles:</h6>
                        <p>
                            @foreach(json_decode(auth()->user()->available_roles, true) as $role)
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($role)) }}</span>
                            @endforeach
                        </p>
                    </div>
                    @endif

                    <hr>

                    @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Item Management</h6>
                                    <p class="text-muted">Manage inventory, non-inventory, and service items</p>
                                    <a href="{{ route('items.index') }}" class="btn btn-sm btn-primary">View Items</a>
                                    <a href="{{ route('items.create') }}" class="btn btn-sm btn-success">Create Item</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager', 'inventory_manager']))
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Procurement (PTP)</h6>
                                    <p class="text-muted">Manage purchase orders and receipts</p>
                                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-primary">View POs</a>
                                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-success">Create PO</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst', 'accounting_manager']))
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Bills & Payments</h6>
                                    <p class="text-muted">Manage vendor bills and payments</p>
                                    <a href="{{ route('bills.index') }}" class="btn btn-sm btn-primary">View Bills</a>
                                    <a href="{{ route('bills.create') }}" class="btn btn-sm btn-success">Create Bill</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative', 'sales_manager']))
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Sales (OTC)</h6>
                                    <p class="text-muted">Manage sales orders and customer transactions</p>
                                    <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-primary">View SOs</a>
                                    <a href="{{ route('sales-orders.create') }}" class="btn btn-sm btn-success">Create SO</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
