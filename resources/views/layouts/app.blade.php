<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DhuriyaMart - All-in-One Village Smart Mart')</title>
    <meta name="description" content="@yield('meta_description', 'DhuriyaMart - Fresh Kirana, Groceries, Daily Staples, Hardware, Electricals, Plumbing & Tools Smart Mart.')">
    
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --dm-primary: #ff6a00;
            --dm-primary-hover: #e65f00;
            --dm-secondary: #1a237e;
            --dm-accent: #2e7d32;
            --dm-dark: #1e293b;
            --dm-light: #f8fafc;
            --dm-border: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            background-color: #f8fafc;
        }

        .bg-dm-primary { background-color: var(--dm-primary) !important; }
        .text-dm-primary { color: var(--dm-primary) !important; }
        .btn-dm-primary {
            background-color: var(--dm-primary);
            color: #fff;
            border: none;
            font-weight: 600;
        }
        .btn-dm-primary:hover {
            background-color: var(--dm-primary-hover);
            color: #fff;
        }

        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }
        .product-img {
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-img {
            transform: scale(1.05);
        }

        .top-bar {
            background-color: #0f172a;
            color: #cbd5e1;
            font-size: 0.8rem;
        }
        .top-bar a {
            color: #cbd5e1;
            text-decoration: none;
        }
        .top-bar a:hover {
            color: #fff;
        }

        .category-nav {
            background-color: #fff;
            border-bottom: 1px solid var(--dm-border);
            font-size: 0.9rem;
        }

        .footer {
            background-color: #0f172a;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .footer h6 {
            color: #f8fafc;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }
        .footer a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer a:hover {
            color: var(--dm-primary);
        }

        /* Mobile Bottom App Bar */
        @media (max-width: 991.98px) {
            body {
                padding-bottom: 68px;
            }
            .mobile-bottom-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1040;
                background-color: #ffffff;
                border-top: 1px solid #e2e8f0;
                box-shadow: 0 -3px 12px rgba(0, 0, 0, 0.08);
            }
            .mobile-bottom-bar .bottom-nav-item {
                flex: 1;
                text-align: center;
                padding: 7px 2px;
                color: #64748b;
                text-decoration: none;
                font-size: 0.72rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                position: relative;
            }
            .mobile-bottom-bar .bottom-nav-item i {
                font-size: 1.25rem;
                margin-bottom: 1px;
            }
            .mobile-bottom-bar .bottom-nav-item.active {
                color: var(--dm-primary);
                font-weight: 700;
            }
        }

        /* Category Scroll Strip */
        .category-scroll-strip {
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .category-scroll-strip::-webkit-scrollbar {
            display: none;
        }
        .category-pill-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 13px;
            border-radius: 50px;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            margin: 5px 3px;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }
        .category-pill-chip:hover {
            background: var(--dm-primary);
            color: #ffffff !important;
            border-color: var(--dm-primary);
            transform: translateY(-1px);
        }
        .category-pill-chip.kirana-chip:hover {
            background: #ff6a00;
        }
        .category-pill-chip.hardware-chip:hover {
            background: #1a237e;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Info Bar -->
    <div class="top-bar py-1 d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="me-3"><i class="bi bi-truck me-1 text-warning"></i> Free delivery on orders above ₹499</span>
                <span><i class="bi bi-shop me-1 text-success"></i> All-in-One Village Smart Mart | Kirana &amp; Hardware</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('pos.kiosk') }}" class="badge bg-success text-white text-decoration-none fw-bold px-2 py-1">
                    <i class="bi bi-display me-1"></i>@lang('Self-Service POS')
                </a>
                <a href="{{ route('catalog.page', 'about-us') }}" class="badge bg-warning text-dark text-decoration-none fw-bold px-2 py-1">
                    <i class="bi bi-geo-alt-fill me-1"></i>Village Smart Mart
                </a>
                <span class="text-secondary">|</span>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('locale.set', 'hi') }}" class="btn btn-sm py-0 px-2 {{ app()->getLocale() == 'hi' ? 'btn-primary' : 'btn-outline-light' }}" style="font-size: 0.75rem;">हिन्दी</a>
                    <a href="{{ route('locale.set', 'en') }}" class="btn btn-sm py-0 px-2 {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-light' }}" style="font-size: 0.75rem;">English</a>
                </div>
                <span class="fw-bold text-white ms-1">₹ INR</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white sticky-top shadow-sm border-bottom">
        <div class="container py-2">
            <div class="d-flex align-items-center justify-content-between gap-3">
                
                <!-- Logo -->
                <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="bg-dm-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center me-2" style="width: 42px; height: 42px;">
                            <i class="bi bi-bag-check-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="fs-4 fw-extrabold text-dark tracking-tight" style="font-weight: 800;">Dhuriya<span class="text-dm-primary">Mart</span></span>
                            <span class="d-block text-dm-primary text-uppercase fw-bold" style="font-size: 0.62rem; letter-spacing: 1.5px; margin-top: -3px;">Smart Mart</span>
                        </div>
                    </div>
                </a>

                <!-- Search Bar -->
                <div class="flex-grow-1 mx-lg-4 d-none d-md-block" style="max-width: 650px;">
                    <form action="{{ route('catalog.search') }}" method="GET" class="input-group">
                        <input type="text" 
                               name="q" 
                               class="form-control border-end-0 py-2 ps-3" 
                               placeholder="Search Kirana, Aata, Rice, Dals, Oils, Spices, Hardware, Plumbing, Tools, Electricals..." 
                               value="{{ request('q') }}">
                        <button class="btn btn-dm-primary px-4" type="submit">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </form>
                </div>

                <!-- Right Action Icons -->
                <div class="d-flex align-items-center gap-3">
                    
                    <!-- Account Dropdown -->
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill px-3 py-2 dropdown-toggle d-flex align-items-center border" type="button" data-bs-dismiss="dropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 me-2 text-primary"></i>
                                <span class="d-none d-sm-inline fw-semibold small">{{ Str::limit(auth()->user()->name, 12) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted">{{ auth()->user()->email }}</div>
                                </li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.dashboard') }}"><i class="bi bi-speedometer2 me-2 text-primary"></i>My Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.qr-card') }}"><i class="bi bi-qr-code-scan me-2 text-warning"></i>@lang('Your Digital Mart Card')</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.orders') }}"><i class="bi bi-box-seam me-2 text-success"></i>My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.wishlist') }}"><i class="bi bi-heart me-2 text-danger"></i>Wishlist</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.addresses') }}"><i class="bi bi-geo-alt me-2 text-info"></i>Saved Addresses</a></li>
                                
                                @if(auth()->user()->isAdmin() || auth()->user()->hasRole(['store-manager', 'cashier']))
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header small text-uppercase">Village Smart Mart</h6></li>
                                    <li><a class="dropdown-item py-2 text-success fw-bold" href="{{ route('pos.index') }}"><i class="bi bi-calculator me-2"></i>Staff Billing POS</a></li>
                                    <li><a class="dropdown-item py-2 text-dark fw-bold" href="{{ route('pos.exit-verification') }}"><i class="bi bi-shield-check me-2 text-warning"></i>Exit Verification Desk</a></li>
                                    <li><a class="dropdown-item py-2 text-primary fw-bold" href="{{ route('admin.cash-desk.index') }}"><i class="bi bi-cash-stack me-2"></i>Cash Drawer Register</a></li>
                                @endif

                                @if(auth()->user()->isAdmin())
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 text-primary fw-bold" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock me-2"></i>Admin Dashboard</a></li>
                                @endif

                                @if(auth()->user()->isSeller())
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 text-warning fw-bold text-dark" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop me-2"></i>Seller Portal</a></li>
                                @endif

                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold small">
                            <i class="bi bi-person me-1"></i> Sign In
                        </a>
                    @endauth

                    <!-- Wishlist Button -->
                    @auth
                        <a href="{{ route('account.wishlist') }}" class="btn btn-light rounded-circle p-2 border position-relative text-dark d-none d-sm-flex" title="Wishlist">
                            <i class="bi bi-heart fs-5"></i>
                            @if(auth()->user()->wishlists()->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    {{ auth()->user()->wishlists()->count() }}
                                </span>
                            @endif
                        </a>
                    @endauth

                    <!-- Cart Button -->
                    @php
                        $cartCount = 0;
                        if (auth()->check()) {
                            $userCart = \App\Models\Cart::where('user_id', auth()->id())->first();
                            $cartCount = $userCart ? $userCart->items_count : 0;
                        } else {
                            $guestCart = \App\Models\Cart::where('session_id', session()->getId())->first();
                            $cartCount = $guestCart ? $guestCart->items_count : 0;
                        }
                    @endphp
                    <a href="{{ route('cart.index') }}" class="btn btn-dm-primary rounded-pill px-3 py-2 d-flex align-items-center shadow-sm position-relative">
                        <i class="bi bi-cart3 fs-5 me-1"></i>
                        <span class="d-none d-sm-inline ms-1 fw-bold">Cart</span>
                        <span class="badge bg-white text-dark rounded-pill ms-2 fw-bold" style="font-size: 0.75rem;">
                            {{ $cartCount }}
                        </span>
                    </a>

                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="d-md-none mt-2">
                <form action="{{ route('catalog.search') }}" method="GET" class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search Kirana, Ration, Hardware & Tools..." value="{{ request('q') }}">
                    <button class="btn btn-dm-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Categories Secondary Nav -->
        <nav class="category-nav py-2 d-none d-lg-block">
            <div class="container d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-sm btn-dark rounded-pill px-3 py-1 dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid-fill me-1"></i> All Categories
                    </button>
                    <ul class="dropdown-menu shadow border-0 mt-2" style="min-width: 280px;">
                        <li class="dropdown-header text-uppercase text-dark fw-bold small"><i class="bi bi-basket3 text-warning me-1"></i> Kirana & Groceries</li>
                        <li><a class="dropdown-item ps-3" href="{{ route('catalog.category', 'kirana-groceries') }}"><strong>All Kirana & Staples</strong></a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'grains-flour-pulses') }}"><i class="bi bi-egg-fried me-2 text-warning"></i>Grains, Flour & Pulses</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'oils-sugar-salt-spices') }}"><i class="bi bi-cup-hot me-2 text-warning"></i>Oils, Sugar, Salt & Spices</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'packaged-food-snacks') }}"><i class="bi bi-cookie me-2 text-warning"></i>Packaged Food & Snacks</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'cleaning-personal-care') }}"><i class="bi bi-stars me-2 text-warning"></i>Cleaning & Personal Care</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header text-uppercase text-dark fw-bold small"><i class="bi bi-tools text-primary me-1"></i> Hardware & Electricals</li>
                        <li><a class="dropdown-item ps-3" href="{{ route('catalog.category', 'hardware-tools') }}"><strong>All Hardware & Tools</strong></a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'screws-nails-fittings') }}"><i class="bi bi-shield-lock me-2 text-primary"></i>Screws, Nails & Locks</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'plumbing-sanitary') }}"><i class="bi bi-droplet me-2 text-primary"></i>Plumbing & Sanitary Fittings</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'electrical-goods') }}"><i class="bi bi-lightning me-2 text-primary"></i>Electrical Goods & Lighting</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'paint-adhesives') }}"><i class="bi bi-paint-bucket me-2 text-primary"></i>Paint & Adhesives</a></li>
                        <li><a class="dropdown-item ps-4 small" href="{{ route('catalog.category', 'hand-tools-construction') }}"><i class="bi bi-wrench me-2 text-primary"></i>Hand Tools & Essentials</a></li>
                    </ul>
                </div>

                <a href="{{ route('catalog.category', 'kirana-groceries') }}" class="text-dark text-decoration-none fw-semibold">
                    <i class="bi bi-basket3 text-warning me-1"></i>Kirana & Ration
                </a>
                <a href="{{ route('catalog.category', 'grains-flour-pulses') }}" class="text-dark text-decoration-none fw-semibold">
                    Aata, Rice & Dal
                </a>
                <a href="{{ route('catalog.category', 'oils-sugar-salt-spices') }}" class="text-dark text-decoration-none fw-semibold">
                    Oils & Spices
                </a>
                <a href="{{ route('catalog.category', 'hardware-tools') }}" class="text-dark text-decoration-none fw-semibold">
                    <i class="bi bi-tools text-primary me-1"></i>Hardware & Tools
                </a>
                <a href="{{ route('catalog.category', 'plumbing-sanitary') }}" class="text-dark text-decoration-none fw-semibold">
                    Plumbing
                </a>
                <a href="{{ route('catalog.category', 'electrical-goods') }}" class="text-dark text-decoration-none fw-semibold">
                    Electricals
                </a>
                <a href="{{ route('pos.kiosk') }}" class="text-success text-decoration-none fw-bold">
                    <i class="bi bi-display me-1"></i>Village Kiosk
                </a>
                <a href="{{ route('catalog.search', ['sort' => 'popularity']) }}" class="text-danger text-decoration-none fw-bold">
                    <i class="bi bi-fire me-1"></i>Opening Deals
                </a>
                <a href="https://wa.me/919876543210" target="_blank" class="text-success text-decoration-none fw-semibold ms-auto">
                    <i class="bi bi-whatsapp me-1"></i>Helpline: +91 98765 43210
                </a>
            </div>
        </nav>

        <!-- Visual Category Quick Scroll Strip -->
        <div class="category-scroll-strip py-1 shadow-sm">
            <div class="container d-flex align-items-center gap-1">
                <a href="{{ route('catalog.category', 'grains-flour-pulses') }}" class="category-pill-chip kirana-chip">
                    <span>🌾</span> Grains &amp; Aata
                </a>
                <a href="{{ route('catalog.category', 'oils-sugar-salt-spices') }}" class="category-pill-chip kirana-chip">
                    <span>🫒</span> Oils &amp; Spices
                </a>
                <a href="{{ route('catalog.category', 'packaged-food-snacks') }}" class="category-pill-chip kirana-chip">
                    <span>🍪</span> Snacks &amp; Biscuits
                </a>
                <a href="{{ route('catalog.category', 'cleaning-personal-care') }}" class="category-pill-chip kirana-chip">
                    <span>🧼</span> Soaps &amp; Cleaning
                </a>
                <span class="text-muted small px-1">|</span>
                <a href="{{ route('catalog.category', 'screws-nails-fittings') }}" class="category-pill-chip hardware-chip">
                    <span>🔩</span> Screws &amp; Fasteners
                </a>
                <a href="{{ route('catalog.category', 'plumbing-sanitary') }}" class="category-pill-chip hardware-chip">
                    <span>🚰</span> Pipes &amp; Plumbing
                </a>
                <a href="{{ route('catalog.category', 'electrical-goods') }}" class="category-pill-chip hardware-chip">
                    <span>💡</span> Wire &amp; LED Lighting
                </a>
                <a href="{{ route('catalog.category', 'paint-adhesives') }}" class="category-pill-chip hardware-chip">
                    <span>🎨</span> Paint &amp; Adhesives
                </a>
                <a href="{{ route('catalog.category', 'hand-tools-construction') }}" class="category-pill-chip hardware-chip">
                    <span>🔨</span> Hand Tools &amp; Hammers
                </a>
                <a href="{{ route('pos.kiosk') }}" class="category-pill-chip text-success border-success bg-success bg-opacity-10 ms-auto">
                    <span>📱</span> Store Kiosk
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        <div class="container py-3">
            <x-alerts />
        </div>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto pt-5 pb-3">
        <div class="container">
            <!-- Trust badges row -->
            <div class="row g-4 pb-4 mb-4 border-bottom border-secondary text-center text-md-start">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check fs-2 text-warning me-3"></i>
                        <div>
                            <div class="fw-bold text-white">100% Genuine</div>
                            <div class="small">Direct Wholesale Stock</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck fs-2 text-primary me-3"></i>
                        <div>
                            <div class="fw-bold text-white">Free Delivery</div>
                            <div class="small">On all orders above ₹499</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-arrow-repeat fs-2 text-success me-3"></i>
                        <div>
                            <div class="fw-bold text-white">7-Day Returns</div>
                            <div class="small">Hassle-free guarantee</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-credit-card-2-front fs-2 text-info me-3"></i>
                        <div>
                            <div class="fw-bold text-white">Secure Payments</div>
                            <div class="small">UPI, Cards & COD Supported</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 pb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white fw-bold mb-3">Dhuriya<span class="text-dm-primary">Mart</span></h5>
                    <p class="small pe-lg-4">
                        DhuriyaMart is an automated, all-in-one Smart Mart network designed for rural and suburban communities. Providing fresh Kirana groceries, staples, home care, and reliable hardware, electrical & plumbing essentials.
                    </p>
                    <div class="small text-white mb-2"><i class="bi bi-telephone-fill me-2 text-warning"></i> +91 98765 43210</div>
                    <div class="small text-white"><i class="bi bi-envelope-fill me-2 text-warning"></i> support@dhuriyamart.com</div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('catalog.category', 'kirana-groceries') }}">Kirana & Groceries</a></li>
                        <li><a href="{{ route('catalog.category', 'hardware-tools') }}">Hardware & Tools</a></li>
                        <li><a href="{{ route('pos.kiosk') }}">Store Self-Kiosk</a></li>
                        <li><a href="{{ route('catalog.page', 'about-us') }}">About Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6>Customer Care & Policies</h6>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('catalog.page', 'shipping-policy') }}">Shipping & Delivery Policy</a></li>
                        <li><a href="{{ route('catalog.page', 'returns-policy') }}">7-Day Return & Cancellation</a></li>
                        <li><a href="{{ route('catalog.page', 'privacy-policy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('catalog.page', 'terms') }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('catalog.page', 'faq') }}">Help & FAQs</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6>Village Smart Mart System</h6>
                    <p class="small">Automated retail for rural &amp; suburban communities. Equipped with Self-Service Kiosks, Barcode Scanners, Cash Register &amp; Exit Verification.</p>
                    <a href="{{ route('pos.kiosk') }}" class="btn btn-sm btn-dm-primary rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-display me-1"></i> Open Store Kiosk
                    </a>
                </div>
            </div>

            <div class="border-top border-secondary pt-3 mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small">
                <div>&copy; {{ date('Y') }} DhuriyaMart Technologies Pvt Ltd. All rights reserved. Made in India.</div>
                <div class="d-flex gap-3 text-secondary">
                    <span><i class="bi bi-shield-lock-fill text-success"></i> 256-Bit SSL Encrypted</span>
                    <span>INR (₹) Standard</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-bar d-lg-none">
        <div class="d-flex justify-content-around align-items-center">
            <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house-door{{ request()->routeIs('home') ? '-fill' : '' }}"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('catalog.category', 'kirana-groceries') }}" class="bottom-nav-item {{ request()->is('categories/kirana*') ? 'active' : '' }}">
                <i class="bi bi-basket3{{ request()->is('categories/kirana*') ? '-fill' : '' }} text-warning"></i>
                <span>Kirana</span>
            </a>
            <a href="{{ route('catalog.category', 'hardware-tools') }}" class="bottom-nav-item {{ request()->is('categories/hardware*') ? 'active' : '' }}">
                <i class="bi bi-tools text-primary"></i>
                <span>Hardware</span>
            </a>
            <a href="{{ route('cart.index') }}" class="bottom-nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }} position-relative">
                <i class="bi bi-cart3{{ request()->routeIs('cart.*') ? '-fill' : '' }}"></i>
                <span>Cart</span>
                @if($cartCount > 0)
                    <span class="position-absolute badge rounded-pill bg-danger" style="top: 3px; right: 18px; font-size: 0.65rem;">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
            @auth
                <a href="{{ route('account.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
                    <i class="bi bi-person{{ request()->routeIs('account.*') ? '-fill' : '' }}"></i>
                    <span>Account</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="bottom-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                    <i class="bi bi-person"></i>
                    <span>Login</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

