<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - DhuriyaMart')</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #0f172a;
            color: #94a3b8;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.9rem;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            background-color: #ff6a00;
            color: #fff;
            font-weight: 600;
        }
        .sidebar .nav-link i {
            font-size: 1.15rem;
            margin-right: 0.75rem;
        }
        .admin-content {
            flex-grow: 1;
            min-height: 100vh;
        }
        .card-stat {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-2px);
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex">

    <!-- Admin Sidebar -->
    <aside class="sidebar p-3 d-flex flex-column flex-shrink-0">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="bg-warning text-dark rounded-3 p-2 d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </div>
            <div>
                <span class="fs-5 fw-bold text-white">DhuriyaMart</span>
                <span class="d-block text-warning small text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Admin Console</span>
            </div>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i> Products & Review
                    @php $pendingCount = \App\Models\Product::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.sellers.index') }}" class="nav-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                    <i class="bi bi-shop"></i> Sellers & KYC
                    @php $pendingSellers = \App\Models\Shop::where('status', 'pending')->count(); @endphp
                    @if($pendingSellers > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingSellers }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> Payments & Refunds
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.settlements.index') }}" class="nav-link {{ request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i> Seller Settlements
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i> Coupons
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reports.sales') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Sales Reports
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.price-monitoring') }}" class="nav-link {{ request()->routeIs('admin.price-monitoring') ? 'active' : '' }}">
                    <i class="bi bi-calculator"></i> Price &amp; Stock Budget
                </a>
            </li>

            <li class="nav-item mt-3 mb-1">
                <span class="text-uppercase text-warning px-3" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.5px;">Village Smart Mart</span>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.cash-desk.index') }}" class="nav-link {{ request()->routeIs('admin.cash-desk.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack text-success"></i> Cash Desk &amp; Reconcile
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.purchase-orders.index') }}" class="nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check text-primary"></i> Auto Purchase Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.village-stores.index') }}" class="nav-link {{ request()->routeIs('admin.village-stores.*') ? 'active' : '' }}">
                    <i class="bi bi-shop text-info"></i> Village Stores Network
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pos.index') }}" class="nav-link text-white" target="_blank">
                    <i class="bi bi-calculator-fill text-warning"></i> Open Staff POS <i class="bi bi-arrow-up-right small ms-auto"></i>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pos.exit-verification') }}" class="nav-link text-white" target="_blank">
                    <i class="bi bi-shield-check text-success"></i> Exit Verification Desk <i class="bi bi-arrow-up-right small ms-auto"></i>
                </a>
            </li>

            <li class="nav-item mt-2">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Audit Logs
                </a>
            </li>
        </ul>

        <div class="border-top border-secondary pt-3 mt-3">
            <a href="{{ route('home') }}" class="nav-link text-white" target="_blank">
                <i class="bi bi-box-arrow-up-right text-info"></i> View Storefront
            </a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <div class="admin-content d-flex flex-column">
        <!-- Admin Header -->
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
            <h5 class="mb-0 fw-bold">@yield('page_title', 'Admin Overview')</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-person-check-fill me-1"></i> {{ auth()->user()->name }}</span>
            </div>
        </header>

        <div class="p-4 flex-grow-1">
            <x-alerts />
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

