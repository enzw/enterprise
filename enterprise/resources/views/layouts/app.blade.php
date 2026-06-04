<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            overflow-y: auto;
            position: fixed;
            height: 100vh;
        }
        .sidebar h5 {
            margin-bottom: 30px;
            border-bottom: 2px solid #34495e;
            padding-bottom: 10px;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px 0;
            transition: color 0.3s;
        }
        .sidebar a:hover {
            color: #3498db;
        }
        .sidebar .nav-section {
            margin-bottom: 25px;
        }
        .sidebar .nav-section h6 {
            color: #95a5a6;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .role-switcher {
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }
        .role-badge {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(auth()->check())
    <div class="sidebar">
        <h5>ERP System</h5>
        <div class="user-info" style="color: #ecf0f1; margin-bottom: 20px;">
            <div>
                <small>{{ auth()->user()->name }}</small>
                <div class="role-badge">{{ str_replace('_', ' ', strtoupper(auth()->user()->current_role)) }}</div>
            </div>
        </div>

        @if(count(auth()->user()->available_roles) > 1)
        <div class="role-switcher" style="margin-bottom: 20px; padding: 10px; border-radius: 5px; background-color: #34495e;">
            <form method="POST" action="{{ route('switch-role') }}">
                @csrf
                <label style="font-size: 12px; color: #95a5a6; margin-bottom: 8px; display: block;">Switch Role:</label>
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(json_decode(auth()->user()->available_roles, true) as $role)
                    <option value="{{ $role }}" @if(auth()->user()->current_role === $role) selected @endif>
                        {{ str_replace('_', ' ', ucfirst($role)) }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif

        <div class="nav-section">
            <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Dashboard</a>
        </div>

        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
        <div class="nav-section">
            <h6>Item Management</h6>
            <a href="{{ route('items.index') }}">Items</a>
            <a href="{{ route('items.create') }}">Create Item</a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager', 'inventory_manager']))
        <div class="nav-section">
            <h6>Procurement (PTP)</h6>
            <a href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
            <a href="{{ route('purchase-orders.create') }}">Create PO</a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst', 'accounting_manager']))
        <div class="nav-section">
            <h6>Bills & Payments</h6>
            <a href="{{ route('bills.index') }}">Vendor Bills</a>
            <a href="{{ route('bills.create') }}">Create Bill</a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative', 'sales_manager']))
        <div class="nav-section">
            <h6>Sales (OTC)</h6>
            <a href="{{ route('sales-orders.index') }}">Sales Orders</a>
            <a href="{{ route('sales-orders.create') }}">Create SO</a>
        </div>
        @endif

        <div class="nav-section" style="margin-top: auto; padding-top: 20px; border-top: 1px solid #34495e;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ecf0f1; cursor: pointer; text-align: left; padding: 10px 0;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        @endif
        <div class="top-bar">
            @if(auth()->check())
            <h2>@yield('page-title', 'Dashboard')</h2>
            <span style="color: #7f8c8d;">{{ date('Y-m-d H:i:s') }}</span>
            @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')

        @if(auth()->check())
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
