@extends('layouts.app')

@section('title', $shop->name . ' - Verified DhuriyaMart Seller')

@section('content')
<div class="container pb-5">
    <!-- Shop Header Profile Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="bg-dark p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                <div class="bg-white text-dark rounded-4 p-3 d-flex align-items-center justify-content-center shadow" style="width: 80px; height: 80px;">
                    <i class="bi bi-shop-window fs-1 text-primary"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h2 class="fw-bold mb-0 text-white">{{ $shop->name }}</h2>
                        <span class="badge bg-success rounded-pill px-3 py-1 small">
                            <i class="bi bi-patch-check-fill me-1"></i> Verified Merchant
                        </span>
                    </div>
                    @if($shop->legal_name)
                        <div class="small text-white-50 mb-2">{{ $shop->legal_name }}</div>
                    @endif
                    <p class="small text-light mb-0" style="max-width: 700px;">{{ $shop->description }}</p>
                </div>
            </div>
        </div>

        <div class="card-body p-3 bg-light d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small">
            <div>
                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                <span>Dispatches from: {{ $shop->profile?->pickup_address ?? 'India' }}</span>
            </div>
            <div>
                <i class="bi bi-box-seam me-1 text-primary"></i>
                <span>{{ $shop->products()->where('status', 'approved')->count() }} Products Listed</span>
            </div>
        </div>
    </div>

    <!-- Shop Products Grid -->
    <h4 class="fw-bold text-dark mb-3">All Products from {{ $shop->name }}</h4>

    @if($products->count() > 0)
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
            @foreach($products as $product)
                <div class="col">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="bg-white rounded-3 p-5 text-center shadow-sm border">
            <p class="text-muted mb-0">No active products listed by this seller yet.</p>
        </div>
    @endif
</div>
@endsection

