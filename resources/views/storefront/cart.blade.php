@extends('layouts.app')

@section('title', 'Shopping Cart - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-cart3 me-2 text-primary"></i>Your Shopping Cart</h3>

    @if($cart->items->count() > 0)
        <div class="row g-4">
            <!-- Cart Items List (Grouped by Shop) -->
            <div class="col-lg-8">
                @foreach($groupedItems as $shopId => $items)
                    @php $shop = $items->first()->variant?->product?->shop; @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shop me-2 text-primary fs-5"></i>
                                <span class="fw-bold text-dark">Fulfilled by: {{ $shop ? $shop->name : 'Merchant' }}</span>
                            </div>
                            <span class="badge bg-white text-muted border">{{ $items->count() }} item(s)</span>
                        </div>

                        <div class="card-body p-0">
                            @foreach($items as $item)
                                @php $variant = $item->variant; @endphp
                                <div class="p-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-2 overflow-hidden text-center" style="width: 75px; height: 75px; flex-shrink: 0;">
                                            <img src="{{ $variant->product->primary_image_url }}" alt="{{ $variant->product->name }}" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <a href="{{ route('catalog.product', $variant->product->slug) }}" class="text-decoration-none text-dark">
                                                    {{ $variant->product->name }}
                                                </a>
                                            </h6>
                                            <div class="small text-muted mb-1">
                                                Variant: <span class="text-dark fw-semibold">{{ $variant->name }}</span> | SKU: <code>{{ $variant->sku }}</code>
                                            </div>
                                            <div class="fw-bold text-dark">
                                                {{ $item->formatted_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3">
                                        <!-- Quantity stepper form -->
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <div class="input-group input-group-sm rounded-pill border overflow-hidden" style="width: 105px;">
                                                <button type="button" class="btn btn-light px-2 py-1 text-dark border-0" onclick="let input = this.nextElementSibling; if(input.value > 1){ input.value--; this.form.submit(); }">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="50" class="form-control text-center fw-bold border-0 bg-transparent px-1 py-1" style="font-size: 0.9rem;" onchange="this.form.submit()">
                                                <button type="button" class="btn btn-light px-2 py-1 text-dark border-0" onclick="let input = this.previousElementSibling; input.value++; this.form.submit();">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <div class="fw-bold fs-6 text-dark text-end" style="min-width: 90px;">
                                            {{ $item->formatted_total }}
                                        </div>

                                        <!-- Delete item form -->
                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Remove item">
                                                <i class="bi bi-trash3 fs-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Financials Summary Card -->
            <div class="col-lg-4">
                @php
                    $freeShippingThreshold = 49900; // ₹499 in paise
                    $subtotal = $totals['subtotal_paise'];
                    $freeShippingProgress = min(100, round(($subtotal / $freeShippingThreshold) * 100));
                    $amountLeftForFreeShipping = max(0, $freeShippingThreshold - $subtotal);
                @endphp
                <!-- Free Shipping Progress Meter -->
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                    @if($amountLeftForFreeShipping > 0)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark">
                                <i class="bi bi-truck text-warning me-1"></i> Add <strong>₹{{ number_format($amountLeftForFreeShipping / 100, 2) }}</strong> more for <strong class="text-success">FREE Delivery</strong>!
                            </span>
                            <span class="badge bg-warning text-dark font-monospace">{{ $freeShippingProgress }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $freeShippingProgress }}%;"></div>
                        </div>
                    @else
                        <div class="d-flex align-items-center text-success fw-bold small">
                            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                            <span>बधाई! You've unlocked FREE Standard Delivery! 🚚</span>
                        </div>
                    @endif
                </div>

                <!-- Promotional Coupon Box -->
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                    <h6 class="fw-bold mb-2">Promotional Coupon</h6>
                    @if($cart->coupon_code)
                        <div class="alert alert-success d-flex justify-content-between align-items-center p-2 mb-0 rounded-3">
                            <div>
                                <i class="bi bi-tag-fill me-1"></i>
                                <strong>{{ $cart->coupon_code }}</strong> applied
                            </div>
                            <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Remove</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('cart.coupon.apply') }}" method="POST" class="input-group">
                            @csrf
                            <input type="text" name="coupon_code" class="form-control text-uppercase font-monospace" placeholder="e.g. WELCOME100" required>
                            <button type="submit" class="btn btn-outline-dark">Apply</button>
                        </form>
                    @endif
                </div>

                <!-- Order Summary Breakdown -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Order Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items Subtotal ({{ $totals['items_count'] }})</span>
                        <span class="fw-semibold">{{ \App\Support\Money::format($totals['subtotal_paise']) }}</span>
                    </div>

                    @if($totals['discount_paise'] > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Coupon Discount</span>
                            <span class="fw-bold">- {{ \App\Support\Money::format($totals['discount_paise']) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estimated Shipping Fee</span>
                        @if($totals['shipping_fee_paise'] == 0)
                            <span class="text-success fw-bold">FREE</span>
                        @else
                            <span class="fw-semibold">{{ \App\Support\Money::format($totals['shipping_fee_paise']) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Estimated GST (5%)</span>
                        <span class="fw-semibold">{{ \App\Support\Money::format($totals['tax_paise']) }}</span>
                    </div>

                    <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-baseline">
                        <span class="fs-5 fw-bold text-dark">Order Total</span>
                        <span class="fs-4 fw-extrabold text-primary" style="font-weight: 800;">
                            {{ \App\Support\Money::format($totals['total_amount_paise']) }}
                        </span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn btn-dm-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-sm">
                        Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
                    </a>

                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Safe & Secure 256-bit Encrypted Checkout</small>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-4 p-5 text-center shadow-sm border my-4">
            <div class="d-inline-flex bg-light text-primary rounded-circle p-4 mb-3">
                <i class="bi bi-cart-x fs-1"></i>
            </div>
            <h4 class="fw-bold mb-2">Your Shopping Cart is Empty</h4>
            <p class="text-muted mb-4">Discover verified smartphones, ethnic handlooms, and fresh organics on DhuriyaMart.</p>
            <a href="{{ route('home') }}" class="btn btn-dm-primary btn-lg rounded-pill px-5 fw-bold">
                Start Shopping Now
            </a>
        </div>
    @endif
</div>
@endsection

