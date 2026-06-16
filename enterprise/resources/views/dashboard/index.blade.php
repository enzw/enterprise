@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title')
    <span class="d-flex align-items-center gap-2">
        <i class="bi bi-speedometer2"></i> Dashboard Overview
    </span>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Welcome Banner with a modern gradient -->
    <div class="card border-0 overflow-hidden mb-4" style="background: linear-gradient(135deg, hsl(224, 76%, 48%) 0%, hsl(199, 89%, 48%) 100%);">
        <div class="card-body p-4 p-md-5 text-white position-relative">
            <!-- Decorative circle shape -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255, 255, 255, 0.08); filter: blur(30px);"></div>
            <div style="position: absolute; right: 80px; bottom: -80px; width: 180px; height: 180px; border-radius: 50%; background: rgba(0, 0, 0, 0.1); filter: blur(20px);"></div>
            
            <div class="row align-items-center position-relative">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary mb-3 px-3 py-2" style="font-size: 0.75rem; border-radius: 20px;">Welcome Back!</span>
                    <h2 class="fw-800 text-white mb-2" style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.03em;">
                        Good Day, {{ auth()->user()->name }}
                    </h2>
                    <p class="text-white mb-0 opacity-90" style="font-size: 0.95rem;">
                        You are currently logged in with active role scope: 
                        <strong class="text-white text-decoration-underline">{{ str_replace('_', ' ', strtoupper(auth()->user()->current_role)) }}</strong>.
                    </p>
                </div>
                
                @if(count(auth()->user()->available_roles) > 1)
                <div class="col-md-4 text-md-end mt-4 mt-md-0">
                    <div class="d-inline-block p-3 rounded-4" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px);">
                        <small class="d-block text-white opacity-75 mb-2" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Switch Access Context</small>
                        <form method="POST" action="{{ route('switch-role') }}">
                            @csrf
                            <select name="role" class="form-select form-select-sm border-0 text-white bg-dark" onchange="this.form.submit()" style="background-color: rgba(0,0,0,0.4) !important; min-width: 180px; border-radius: 8px;">
                                @foreach(auth()->user()->available_roles as $role)
                                <option value="{{ $role }}" @if(auth()->user()->current_role === $role) selected @endif>
                                    {{ str_replace('_', ' ', ucfirst($role)) }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Modules Sections -->
    <h5 class="fw-700 mb-4" style="font-weight: 700; letter-spacing: -0.01em;">Available Modules</h5>
    
    <div class="row g-4">
        <!-- Item Management -->
        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 card-interactive">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Item Module</span>
                    </div>
                    
                    <h5 class="fw-700 text-main mb-2" style="font-weight: 700;">Item Management</h5>
                    <p class="text-muted small flex-grow-1">
                        Track and control inventories, service listings, stock structures, and configure custom price sheets.
                    </p>
                    
                    <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                        <a href="{{ route('items.index') }}" class="btn btn-sm btn-light flex-grow-1">
                            <i class="bi bi-list-nested me-1"></i> List
                        </a>
                        <a href="{{ route('items.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Procurement (PTP) -->
        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager', 'inventory_manager']))
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 card-interactive">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success">Procurement</span>
                    </div>
                    
                    <h5 class="fw-700 text-main mb-2" style="font-weight: 700;">Procurement (PTP)</h5>
                    <p class="text-muted small flex-grow-1">
                        Draft purchase orders, track vendor deliveries, receive item shipments, and view stock intake.
                    </p>
                    
                    <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-light flex-grow-1">
                            <i class="bi bi-list-nested me-1"></i> View POs
                        </a>
                        <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-plus-lg me-1"></i> Create PO
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Bills & Payments -->
        @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst', 'accounting_manager']))
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 card-interactive">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="p-3 rounded-3 bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Accounts Payable</span>
                    </div>
                    
                    <h5 class="fw-700 text-main mb-2" style="font-weight: 700;">Bills & Payments</h5>
                    <p class="text-muted small flex-grow-1">
                        Review vendor bills, allocate corporate payments, approve accounting schedules, and match purchase logs.
                    </p>
                    
                    <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                        <a href="{{ route('bills.index') }}" class="btn btn-sm btn-light flex-grow-1">
                            <i class="bi bi-list-nested me-1"></i> Bills List
                        </a>
                        <a href="{{ route('bills.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-plus-lg me-1"></i> Create Bill
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sales (OTC) -->
        @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative', 'sales_manager']))
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 card-interactive">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-cart fs-4"></i>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning">Order to Cash</span>
                    </div>
                    
                    <h5 class="fw-700 text-main mb-2" style="font-weight: 700;">Sales Orders (OTC)</h5>
                    <p class="text-muted small flex-grow-1">
                        Register sales orders, verify customer credit parameters, manage invoices, and record incoming customer payments.
                    </p>
                    
                    <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                        <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-light flex-grow-1">
                            <i class="bi bi-list-nested me-1"></i> View SOs
                        </a>
                        <a href="{{ route('sales-orders.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-plus-lg me-1"></i> Create SO
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer Payments (AR) -->
        @if(in_array(auth()->user()->current_role, ['admin', 'ar_analyst']))
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 card-interactive">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success">Accounts Receivable</span>
                    </div>
                    
                    <h5 class="fw-700 text-main mb-2" style="font-weight: 700;">Customer Payments (AR)</h5>
                    <p class="text-muted small flex-grow-1">
                        Record customer payments, allocate to invoices, track collections, and manage aged receivables.
                    </p>
                    
                    <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light flex-grow-1">
                            <i class="bi bi-list-nested me-1"></i> Payments
                        </a>
                        <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-plus-lg me-1"></i> Record Payment
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .card-interactive {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid var(--border-color);
    }
    .card-interactive:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        border-color: rgba(47, 84, 235, 0.2);
    }
    .fw-800 {
        font-weight: 800;
    }
    .fw-700 {
        font-weight: 700;
    }
</style>
@endsection
