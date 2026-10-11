<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Seller Portal - DhuriyaMart')</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #1e293b;
            color: #cbd5e1;
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
        .seller-content {
            flex-grow: 1;
            min-height: 100vh;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex">

    <!-- Seller Sidebar -->
    <aside class="sidebar p-3 d-flex flex-column flex-shrink-0">
        <div class="mb-4 px-2">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                    <i class="bi bi-shop fs-5"></i>
                </div>
                <div>
                    <span class="fs-6 fw-bold text-white d-block text-truncate" style="max-width: 170px;">
                        {{ auth()->user()->shop ? auth()->user()->shop->name : 'Merchant Hub' }}
                    </span>
                    <span class="d-block text-muted small" style="font-size: 0.7rem;">DhuriyaMart Seller</span>
                </div>
            </div>

            @php $shop = auth()->user()->shop; @endphp
            @if($shop)
                <div class="mt-2">
                    <x-status-badge :status="$shop->status" />
                    @if($shop->status === 'approved')
                        <span class="small text-muted ms-1">({{ $shop->commission_rate }}% Comm.)</span>
                    @endif
                </div>
            @endif
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('seller.dashboard') }}" class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            @if($shop && $shop->status === 'approved')
                <li class="nav-item">
                    <a href="{{ route('seller.orders.index') }}" class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i> Orders & Dispatch
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.products.index') }}" class="nav-link {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> My Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.inventory.index') }}" class="nav-link {{ request()->routeIs('seller.inventory.*') ? 'active' : '' }}">
                        <i class="bi bi-boxes"></i> Inventory Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.settlements.index') }}" class="nav-link {{ request()->routeIs('seller.settlements.*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i> Ledger & Payouts
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('seller.profile') }}" class="nav-link {{ request()->routeIs('seller.profile') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Shop & Bank Profile
                </a>
            </li>
        </ul>

        <div class="border-top border-secondary pt-3 mt-3">
            @if($shop && $shop->status === 'approved')
                <a href="{{ route('catalog.shop', $shop->slug) }}" class="nav-link text-white" target="_blank">
                    <i class="bi bi-eye text-info"></i> View Public Shop
                </a>
            @endif
            <a href="{{ route('home') }}" class="nav-link text-white" target="_blank">
                <i class="bi bi-box-arrow-up-right text-warning"></i> Storefront
            </a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Seller Content -->
    <div class="seller-content d-flex flex-column">
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
            <h5 class="mb-0 fw-bold">@yield('page_title', 'Seller Portal')</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                    <i class="bi bi-person-fill text-primary me-1"></i> {{ auth()->user()->name }}
                </span>
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

