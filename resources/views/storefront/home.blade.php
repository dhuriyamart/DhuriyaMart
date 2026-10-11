@extends('layouts.app')

@section('title', 'DhuriyaMart - All-in-One Village Smart Mart | Kirana & Hardware')

@section('content')
<div class="container pb-5">

    <!-- Hero Section -->
    <div class="row g-3 mb-5 align-items-stretch">
        <div class="col-lg-8">
            <div id="heroCarousel" class="carousel slide h-100 shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                <div class="carousel-inner h-100">
                    @forelse($heroBanners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }} position-relative" style="min-height: 380px; max-height: 420px; background-color: #1e293b;">
                            <img src="{{ $banner->resolved_image_url }}" class="d-block w-100 h-100 object-fit-cover opacity-75" alt="{{ $banner->title }}">
                            <div class="carousel-caption text-start d-flex flex-column justify-content-center p-4 p-md-5 top-0 bottom-0 start-0 end-0">
                                <span class="badge bg-warning text-dark text-uppercase fw-bold rounded-pill mb-2 px-3 py-1 align-self-start" style="font-size: 0.75rem;">
                                    {{ $index === 0 ? 'Kirana Wholesale Rates' : 'Hardware & Construction' }}
                                </span>
                                <h2 class="display-6 fw-bold text-white mb-2">{{ $banner->title }}</h2>
                                <p class="lead text-light mb-4 d-none d-md-block fs-6">{{ $banner->subtitle }}</p>
                                <div>
                                    <a href="{{ $banner->link_url ?? route('catalog.search') }}" class="btn btn-dm-primary btn-lg rounded-pill px-4 shadow">
                                        Shop Department <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="carousel-item active position-relative p-5 text-white text-center bg-dark" style="min-height: 380px;">
                            <h2 class="display-6 fw-bold text-white mb-3">Welcome to DhuriyaMart Village Smart Mart</h2>
                            <p class="lead mb-4">Fresh Kirana groceries, daily staples, hardware, plumbing & electrical tools with unified multi-vendor billing.</p>
                            <a href="{{ route('catalog.search') }}" class="btn btn-dm-primary rounded-pill px-4">Explore Products</a>
                        </div>
                    @endforelse
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-label="Previous"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-label="Next"></span>
                </button>
            </div>
        </div>

        <!-- Hero Side Promo Banner - Kirana & Daily Staples -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative text-white p-4 d-flex flex-column justify-content-between" 
                 style="background: linear-gradient(135deg, #166534 0%, #064e3b 100%);">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 text-uppercase fw-bold">Daily Kirana &amp; Staples</span>
                        <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-1 small">Mandi Rates</span>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Dhuriya Kirana &amp; Daily Needs</h4>
                    <p class="text-light small mb-3">Fresh Sharbati wheat aata, premium rice, certified pulses, pure mustard oil &amp; spices sourced directly from wholesale distributors.</p>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-3 p-2 text-center">
                                <small class="text-warning-emphasis d-block text-white-50" style="font-size: 0.72rem;">Wheat Aata</small>
                                <span class="text-white fw-bold">₹38 / kg</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-3 p-2 text-center">
                                <small class="text-white-50 d-block" style="font-size: 0.72rem;">Mustard Oil</small>
                                <span class="text-white fw-bold">₹200 / L</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="p-3 bg-white bg-opacity-10 rounded-3 mb-3 backdrop-blur">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small text-warning fw-bold"><i class="bi bi-percent me-1"></i>Use Coupon</span>
                            <span class="badge bg-warning text-dark font-monospace fw-bold">WELCOME100</span>
                        </div>
                        <div class="small text-white-50">Flat ₹100 off on your order above ₹499</div>
                    </div>
                    <a href="{{ route('catalog.category', 'kirana-groceries') }}" class="btn btn-warning text-dark rounded-pill w-100 fw-bold shadow-sm">
                        Shop Kirana &amp; Ration <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop By Department (Kirana & Hardware) -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Store Departments</h4>
                <p class="text-muted small mb-0">Two complete wholesale departments under one trusted roof</p>
            </div>
            <a href="{{ route('catalog.search') }}" class="text-primary text-decoration-none fw-semibold small">All Products &rarr;</a>
        </div>
        <div class="row g-4 mb-4">
            <!-- Kirana Department Card -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden p-4 bg-white" style="border-left: 5px solid #ff6a00 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px; background-color: #fff7ed; color: #ea580c;">
                                <i class="bi bi-basket3-fill fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Kirana &amp; Groceries</h5>
                                <small class="text-muted">48 Everyday Essentials in Stock</small>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1">Department 1</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        Fresh Sharbati wheat aata, premium rice, certified dals, pure mustard oil, spices, daily snacks &amp; home hygiene essentials at wholesale mandi rates.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('catalog.category', 'grains-flour-pulses') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🌾 Grains &amp; Aata</a>
                        <a href="{{ route('catalog.category', 'oils-sugar-salt-spices') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🫒 Oils &amp; Spices</a>
                        <a href="{{ route('catalog.category', 'packaged-food-snacks') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🍪 Snacks &amp; Biscuits</a>
                        <a href="{{ route('catalog.category', 'cleaning-personal-care') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🧼 Soaps &amp; Cleaning</a>
                    </div>
                    <div class="mt-auto pt-2">
                        <a href="{{ route('catalog.category', 'kirana-groceries') }}" class="btn btn-dm-primary rounded-pill px-4 fw-bold w-100">
                            Explore Kirana Mart <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hardware Department Card -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden p-4 bg-white" style="border-left: 5px solid #1a237e !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px; background-color: #eef2ff; color: #1a237e;">
                                <i class="bi bi-tools fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Hardware &amp; Electricals</h5>
                                <small class="text-muted">66 Construction &amp; Repair Items</small>
                            </div>
                        </div>
                        <span class="badge bg-primary text-white fw-bold rounded-pill px-3 py-1">Department 2</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        CPVC plumbing pipes, brass taps, fasteners, Havells LED bulbs, Finolex wires, Asian Paints &amp; durable hand tools for construction and repairs.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('catalog.category', 'screws-nails-fittings') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🔩 Screws &amp; Fasteners</a>
                        <a href="{{ route('catalog.category', 'plumbing-sanitary') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🚰 Pipes &amp; Taps</a>
                        <a href="{{ route('catalog.category', 'electrical-goods') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">💡 LED &amp; Wire</a>
                        <a href="{{ route('catalog.category', 'paint-adhesives') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🎨 Paint &amp; Primer</a>
                        <a href="{{ route('catalog.category', 'hand-tools-construction') }}" class="badge text-decoration-none bg-light text-dark border px-2 py-1">🔨 Hand Tools</a>
                    </div>
                    <div class="mt-auto pt-2">
                        <a href="{{ route('catalog.category', 'hardware-tools') }}" class="btn btn-outline-dark rounded-pill px-4 fw-bold w-100" style="background-color: #1a237e; color: #fff; border: none;">
                            Explore Hardware Mart <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- All 9 Store Aisles -->
        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.78rem; letter-spacing: 0.5px;">All Store Aisles</h6>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2">
            @foreach($categories as $parentCat)
                @foreach($parentCat->children as $subCat)
                    <div class="col">
                        <a href="{{ route('catalog.category', $subCat->slug) }}" class="card h-100 text-decoration-none border-0 shadow-sm rounded-3 p-2 text-center bg-white hover-shadow transition-all">
                            <div class="fw-bold text-dark small text-truncate">{{ $subCat->name }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $subCat->products()->count() }} items</small>
                        </a>
                    </div>
                @endforeach
            @endforeach
            <div class="col">
                <a href="{{ route('pos.kiosk') }}" class="card h-100 text-decoration-none border border-success border-opacity-25 shadow-sm rounded-3 p-2 text-center bg-success bg-opacity-10 hover-shadow transition-all">
                    <div class="fw-bold text-success small text-truncate">📱 Store Kiosk</div>
                    <small class="text-success" style="font-size: 0.72rem;">Self-Service</small>
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Featured Essentials</h4>
                <p class="text-muted small mb-0">Handpicked daily Kirana staples &amp; genuine construction hardware at mandi rates</p>
            </div>
            <a href="{{ route('catalog.search', ['sort' => 'rating']) }}" class="text-primary text-decoration-none fw-semibold small">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($featuredProducts as $product)
                <div class="col">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>

    <!-- Promotional Callout -->
    <div class="card border-0 rounded-4 overflow-hidden mb-5 text-white shadow-sm" style="background: linear-gradient(90deg, #ff6a00 0%, #ee0979 100%);">
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div>
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold text-uppercase mb-2">Special Festival Offer</span>
                <h3 class="fw-bold text-white mb-1">Get 20% OFF on Orders Over ₹999</h3>
                <p class="mb-0 text-white-50">Apply promotional coupon code <strong>FESTIVE20</strong> during checkout to save up to ₹500 instantly.</p>
            </div>
            <a href="{{ route('catalog.search') }}" class="btn btn-light text-dark btn-lg rounded-pill px-4 fw-bold shadow text-nowrap">
                Shop Festival Sale
            </a>
        </div>
    </div>

    <!-- Bestsellers -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Bestsellers &amp; Fast-Moving Stock</h4>
                <p class="text-muted small mb-0">High-demand Kirana groceries &amp; construction hardware trusted by local families and contractors</p>
            </div>
            <a href="{{ route('catalog.search', ['sort' => 'popularity']) }}" class="text-primary text-decoration-none fw-semibold small">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($bestsellers as $product)
                <div class="col">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>

    <!-- New Arrivals -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Fresh Replenished Arrivals</h4>
                <p class="text-muted small mb-0">Freshly received shipments direct from authorized wholesale distributors</p>
            </div>
            <a href="{{ route('catalog.search', ['sort' => 'newest']) }}" class="text-primary text-decoration-none fw-semibold small">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($newArrivals as $product)
                <div class="col">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

