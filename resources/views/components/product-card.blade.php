@props(['product'])

@php
    $defaultVariant = $product->default_variant;
    $discount = $defaultVariant ? $defaultVariant->discount_percentage : 0;
    $inStock = $defaultVariant ? $defaultVariant->isInStock() : false;
    $rating = $product->average_rating;
    $reviewsCount = $product->reviews_count;
@endphp

<div class="card h-100 product-card border-0 shadow-sm rounded-3 overflow-hidden position-relative">
    @if($discount > 0)
        <span class="position-absolute top-0 start-0 m-2 badge bg-danger fw-bold rounded-pill shadow-sm" style="z-index: 2;">
            {{ $discount }}% OFF
        </span>
    @endif

    <div class="product-img-wrapper position-relative overflow-hidden bg-light text-center" style="height: 220px;">
        <a href="{{ route('catalog.product', $product->slug) }}" class="d-block w-100 h-100">
            <img src="{{ $product->primary_image_url }}" 
                 alt="{{ $product->name }}" 
                 class="w-100 h-100 object-fit-cover product-img transition-all"
                 loading="lazy">
        </a>
        @auth
            <form action="{{ route('account.wishlist.toggle', $defaultVariant ? $defaultVariant->id : 1) }}" method="POST" class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                @csrf
                <button type="submit" class="btn btn-sm btn-light rounded-circle shadow-sm border p-1 text-danger" title="Save to Wishlist" style="width: 32px; height: 32px;">
                    <i class="bi {{ auth()->user()->inWishlist($defaultVariant?->id ?? 0) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                </button>
            </form>
        @endauth
    </div>

    <div class="card-body p-3 d-flex flex-column">
        @if($product->shop)
            <div class="mb-1 text-muted" style="font-size: 0.75rem;">
                <a href="{{ route('catalog.shop', $product->shop->slug) }}" class="text-decoration-none text-muted">
                    <i class="bi bi-shop me-1 text-primary"></i>{{ Str::limit($product->shop->name, 22) }}
                </a>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mb-1">
            @if($product->category)
                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.68rem;">
                    {{ $product->category->name }}
                </span>
            @endif
            @if($defaultVariant && $defaultVariant->name)
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                    {{ $defaultVariant->name }}
                </span>
            @endif
        </div>

        <h6 class="card-title mb-1 product-title" style="font-size: 0.95rem; line-height: 1.35; height: 2.7rem; overflow: hidden;">
            <a href="{{ route('catalog.product', $product->slug) }}" class="text-dark text-decoration-none fw-semibold">
                {{ $product->name }}
            </a>
        </h6>

        <!-- Rating -->
        <div class="d-flex align-items-center mb-2" style="font-size: 0.8rem;">
            <div class="text-warning me-1">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= round($rating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                @endfor
            </div>
            <span class="text-muted">({{ $reviewsCount }})</span>
            @if($inStock && $defaultVariant && $defaultVariant->stock_quantity <= 10)
                <span class="badge bg-warning bg-opacity-25 text-dark ms-auto" style="font-size: 0.65rem;">
                    Only {{ $defaultVariant->stock_quantity }} left
                </span>
            @endif
        </div>

        <!-- Price & Action -->
        <div class="mt-auto pt-2 border-top d-flex align-items-baseline justify-content-between">
            <div>
                <span class="fs-5 fw-bold text-dark">{{ $product->formatted_price }}</span>
                @if($defaultVariant && $defaultVariant->formatted_compare_price)
                    <span class="text-muted text-decoration-line-through ms-1 small" style="font-size: 0.8rem;">
                        {{ $defaultVariant->formatted_compare_price }}
                    </span>
                @endif
            </div>

            @if($inStock)
                <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-sm btn-dm-primary rounded-pill px-3 fw-bold shadow-sm" title="Add to Cart">
                        <i class="bi bi-cart-plus me-1"></i>+ Add
                    </button>
                </form>
            @else
                <span class="badge bg-light text-danger border">Out of Stock</span>
            @endif
        </div>
    </div>
</div>

