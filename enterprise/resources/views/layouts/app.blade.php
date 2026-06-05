<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP System') - Modern ERP</title>
    <!-- Modern Typography: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS frameworks: Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            /* Semantic Harmonious Palette using HSL */
            --primary: hsl(224, 76%, 48%);
            --primary-hover: hsl(224, 76%, 40%);
            --primary-light: hsl(224, 76%, 95%);
            --secondary: hsl(220, 9%, 46%);
            --secondary-light: hsl(220, 9%, 95%);
            
            --success: hsl(142, 72%, 29%);
            --success-light: hsl(142, 72%, 95%);
            --danger: hsl(346, 84%, 49%);
            --danger-light: hsl(346, 84%, 95%);
            --warning: hsl(37, 90%, 51%);
            --warning-light: hsl(37, 90%, 94%);
            --info: hsl(199, 89%, 48%);
            --info-light: hsl(199, 89%, 95%);

            --bg-main: hsl(210, 20%, 98%);
            --bg-card: hsl(0, 0%, 100%);
            --border-color: hsl(210, 14%, 89%);
            
            --text-main: hsl(224, 71%, 12%);
            --text-muted: hsl(220, 9%, 45%);
            
            --sidebar-bg: linear-gradient(180deg, hsl(222, 47%, 11%) 0%, hsl(224, 64%, 6%) 100%);
            --sidebar-text: hsl(215, 20%, 75%);
            --sidebar-text-hover: hsl(0, 0%, 100%);
            --sidebar-active-bg: rgba(255, 255, 255, 0.08);
            --sidebar-active-border: hsl(224, 76%, 60%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 270px;
            background: var(--sidebar-bg);
            color: white;
            padding: 24px;
            overflow-y: auto;
            position: fixed;
            height: 100vh;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: white;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-brand i {
            font-size: 1.5rem;
            background: linear-gradient(135deg, hsl(224, 76%, 65%), hsl(224, 76%, 45%));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-profile {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, hsl(224, 76%, 55%), hsl(199, 89%, 48%));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .user-details {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .role-badge {
            background-color: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .role-switcher-form {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .role-switcher-form label {
            font-size: 11px;
            color: var(--sidebar-text);
            margin-bottom: 6px;
            display: block;
            font-weight: 500;
        }

        .role-switcher-form select {
            background-color: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.8rem;
            border-radius: 8px;
            padding: 6px 12px;
        }

        .role-switcher-form select:focus {
            background-color: #1e293b;
            color: white;
            border-color: var(--sidebar-active-border);
            box-shadow: none;
        }

        .sidebar .nav-section {
            margin-bottom: 24px;
        }

        .sidebar .nav-section h6 {
            color: var(--sidebar-text);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 16px;
            margin-bottom: 8px;
            opacity: 0.6;
        }

        .sidebar a {
            color: var(--sidebar-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .sidebar a i {
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        .sidebar a:hover {
            color: var(--sidebar-text-hover);
            background-color: var(--sidebar-active-bg);
        }

        .sidebar a:hover i {
            transform: translateX(3px);
        }

        .sidebar a.active {
            color: white;
            background-color: var(--sidebar-active-bg);
            border-left: 3px solid var(--sidebar-active-border);
            padding-left: 11px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--sidebar-text);
            cursor: pointer;
            text-align: left;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            margin-top: auto;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .logout-btn:hover {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }

        /* Main Layout Content */
        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 32px 40px;
            min-height: 100vh;
            background-color: var(--bg-main);
        }

        /* Top Bar Styling */
        .top-bar {
            background: var(--bg-card);
            padding: 16px 24px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        .top-bar h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0;
            letter-spacing: -0.025em;
        }

        .top-bar-time {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Premium Components */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
            background-color: var(--bg-card);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 24px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 18px 24px;
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .card-body {
            padding: 24px;
        }

        /* Tables */
        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            background: white;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.725rem;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            background-color: var(--bg-main);
            border-bottom: 1px solid var(--border-color);
            padding: 14px 20px;
        }

        .table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            font-size: 0.875rem;
            color: var(--text-main);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: var(--primary-light);
            cursor: pointer;
        }

        /* Buttons styling */
        .btn {
            border-radius: 10px;
            padding: 9px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: -0.01em;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(47, 84, 235, 0.15);
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(47, 84, 235, 0.25);
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
        }

        .btn-success:hover {
            background-color: hsl(142, 72%, 24%);
            border-color: hsl(142, 72%, 24%);
            transform: translateY(-1px);
        }

        .btn-light {
            background-color: var(--secondary-light);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .btn-light:hover {
            background-color: hsl(220, 9%, 90%);
            border-color: var(--border-color);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        /* Forms inputs override */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 10px 16px;
            font-size: 0.875rem;
            color: var(--text-main);
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(47, 84, 235, 0.12);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.825rem;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        /* Badges */
        .badge {
            font-weight: 700;
            font-size: 0.725rem;
            padding: 5px 10px;
            border-radius: 6px;
            letter-spacing: -0.01em;
        }

        .bg-primary { background-color: var(--primary) !important; color: white !important; }
        .bg-secondary { background-color: var(--secondary) !important; color: white !important; }
        .bg-success { background-color: var(--success-light) !important; color: var(--success) !important; }
        .bg-danger { background-color: var(--danger-light) !important; color: var(--danger) !important; }
        .bg-warning { background-color: var(--warning-light) !important; color: var(--warning) !important; }
        .bg-info { background-color: var(--info-light) !important; color: var(--info) !important; }

        /* Custom Alert Styling */
        .alert {
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid transparent;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success {
            background-color: var(--success-light);
            border-color: rgba(34, 197, 94, 0.15);
            color: var(--success);
        }

        .alert-danger {
            background-color: var(--danger-light);
            border-color: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .alert-info {
            background-color: var(--info-light);
            border-color: rgba(14, 165, 233, 0.15);
            color: var(--info);
        }

        .btn-close {
            margin-left: auto;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .btn-close:hover {
            opacity: 1;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
                padding: 16px;
            }
            .main-content {
                margin-left: 0;
                padding: 24px 16px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(auth()->check())
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-rocket-takeoff-fill"></i>
            <span>Enterprise ERP</span>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="role-badge">{{ str_replace('_', ' ', auth()->user()->current_role) }}</div>
            </div>
        </div>

        @if(count(auth()->user()->available_roles) > 1)
        <div class="role-switcher-form">
            <form method="POST" action="{{ route('switch-role') }}">
                @csrf
                <label><i class="bi bi-arrow-left-right"></i> Active Role</label>
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(auth()->user()->available_roles as $role)
                    <option value="{{ $role }}" @if(auth()->user()->current_role === $role) selected @endif>
                        {{ str_replace('_', ' ', ucfirst($role)) }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif

        <div class="nav-section">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </div>

        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager']))
        <div class="nav-section">
            <h6>Item Management</h6>
            <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.index') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Items List
            </a>
            <a href="{{ route('items.create') }}" class="{{ request()->routeIs('items.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Create Item
            </a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'purchasing_manager', 'inventory_manager']))
        <div class="nav-section">
            <h6>Procurement (PTP)</h6>
            <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Purchase Orders
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="{{ request()->routeIs('purchase-orders.create') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-plus"></i> Create PO
            </a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'ap_analyst', 'accounting_manager']))
        <div class="nav-section">
            <h6>Bills & Payments</h6>
            <a href="{{ route('bills.index') }}" class="{{ request()->routeIs('bills.index') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Vendor Bills
            </a>
            <a href="{{ route('bills.create') }}" class="{{ request()->routeIs('bills.create') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i> Create Bill
            </a>
        </div>
        @endif

        @if(in_array(auth()->user()->current_role, ['admin', 'sales_representative', 'sales_manager']))
        <div class="nav-section">
            <h6>Sales (OTC)</h6>
            <a href="{{ route('sales-orders.index') }}" class="{{ request()->routeIs('sales-orders.index') ? 'active' : '' }}">
                <i class="bi bi-cart"></i> Sales Orders
            </a>
            <a href="{{ route('sales-orders.create') }}" class="{{ request()->routeIs('sales-orders.create') ? 'active' : '' }}">
                <i class="bi bi-cart-plus"></i> Create SO
            </a>
        </div>
        @endif

        <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout System
            </button>
        </form>
    </div>

    <div class="main-content">
        @endif
        <div class="top-bar">
            @if(auth()->check())
            <h2>@yield('page-title', 'Dashboard')</h2>
            <div class="top-bar-time">
                <i class="bi bi-clock-history"></i>
                <span>{{ date('Y-m-d H:i:s') }}</span>
            </div>
            @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span><i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div>
                <i class="bi bi-x-circle-fill me-2"></i>
                <ul class="mb-0 d-inline-block ps-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
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
