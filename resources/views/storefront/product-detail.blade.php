@extends('layouts.app')

@section('title', $product->name . ' - DhuriyaMart')
@section('meta_description', Str::limit($product->short_description, 160))

@section('content')
<div class="container pb-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('catalog.category', $product->category->slug) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border mb-5">
        <div class="row g-4 g-lg-5">
            
            <!-- Product Images Gallery -->
            <div class="col-lg-5">
                <div class="border rounded-3 overflow-hidden bg-light mb-3 text-center" style="max-height: 440px;">
                    <img id="mainProductImg" 
                         src="{{ $product->primary_image_url }}" 
                         alt="{{ $product->name }}" 
                         class="img-fluid w-100 object-fit-contain" 
                         style="height: 400px;">
                </div>

                @if($product->images->count() > 1)
                    <div class="d-flex gap-2 overflow-auto pb-2">
                        @foreach($product->images as $img)
                            <div class="border rounded-2 p-1 cursor-pointer bg-white" style="width: 70px; height: 70px; flex-shrink: 0;" onclick="document.getElementById('mainProductImg').src = '{{ $img->url }}'">
                                <img src="{{ $img->url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-1">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Purchase Details -->
            <div class="col-lg-7">
                <!-- Seller badge -->
                @if($product->shop)
                    <div class="mb-2">
                        <a href="{{ route('catalog.shop', $product->shop->slug) }}" class="badge bg-light text-primary border text-decoration-none px-3 py-2 fw-semibold">
                            <i class="bi bi-shop me-1"></i> Sold by {{ $product->shop->name }}
                            <i class="bi bi-patch-check-fill text-success ms-1"></i>
                        </a>
                    </div>
                @endif

                <h2 class="fw-bold text-dark mb-2 fs-3">{{ $product->name }}</h2>

                <!-- Rating -->
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($product->average_rating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                    <span class="fw-bold me-2">{{ number_format($product->average_rating, 1) }}</span>
                    <span class="text-muted small">({{ $product->reviews_count }} verified ratings)</span>
                </div>

                <!-- Price Box -->
                @php
                    $defaultVariant = $product->default_variant;
                @endphp
                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="display-6 fw-bold text-dark" id="variantPrice">{{ $defaultVariant ? $defaultVariant->formatted_price : $product->formatted_price }}</span>
                        @if($defaultVariant && $defaultVariant->formatted_compare_price)
                            <span class="text-muted text-decoration-line-through fs-5" id="variantComparePrice">
                                {{ $defaultVariant->formatted_compare_price }}
                            </span>
                            <span class="badge bg-danger rounded-pill fw-bold" id="variantDiscount">
                                {{ $defaultVariant->discount_percentage }}% OFF
                            </span>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i> Inclusive of all taxes. Free delivery on orders over ₹499.</small>
                </div>

                <p class="text-secondary mb-4">{{ $product->short_description }}</p>

                <!-- Purchase Form -->
                <form action="{{ route('cart.add') }}" method="POST" class="mb-4">
                    @csrf
                    
                    <!-- Variant Options Selector -->
                    @if($product->variants->count() > 1)
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Select Variant / Model:</label>
                            <div class="row row-cols-2 g-2">
                                @foreach($product->variants as $variant)
                                    <div class="col">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="variant_id" 
                                               id="variant_{{ $variant->id }}" 
                                               value="{{ $variant->id }}" 
                                               {{ $loop->first ? 'checked' : '' }}
                                               onchange="updateVariantDetails({{ json_encode($variant) }})">
                                        <label class="btn btn-outline-secondary w-100 text-start p-2 rounded-3" for="variant_{{ $variant->id }}">
                                            <div class="fw-bold small">{{ $variant->name }}</div>
                                            <div class="text-primary fw-semibold">{{ $variant->formatted_price }}</div>
                                            @if($variant->stock_quantity <= 5 && $variant->stock_quantity > 0)
                                                <small class="text-danger" style="font-size: 0.7rem;">Only {{ $variant->stock_quantity }} left!</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="variant_id" value="{{ $defaultVariant ? $defaultVariant->id : '' }}">
                    @endif

                    <!-- Stock Status Alert -->
                    <div class="mb-3">
                        @if($defaultVariant && $defaultVariant->isInStock())
                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> In Stock & Ready to Dispatch
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-x-circle me-1"></i> Currently Out of Stock
                            </span>
                        @endif
                    </div>

                    <!-- Quantity & Buttons -->
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div style="width: 110px;">
                            <input type="number" name="quantity" class="form-control form-control-lg text-center fw-bold" value="1" min="1" max="20">
                        </div>

                        <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill px-4 flex-grow-1 shadow-sm" {{ (!$defaultVariant || !$defaultVariant->isInStock()) ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus-fill me-2"></i> Add to Cart
                        </button>
                    </div>
                </form>

                <!-- Delivery Pincode Checker -->
                <div class="card border-0 bg-light rounded-3 p-3 my-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Check Delivery to Your Pincode</span>
                    </div>
                    <div class="input-group">
                        <input type="text" id="pincodeInput" class="form-control" placeholder="Enter 6-digit Pincode (e.g. 275101)" maxlength="6">
                        <button type="button" class="btn btn-outline-dark fw-bold px-3" onclick="checkPincode()">Check</button>
                    </div>
                    <div id="pincodeResult" class="small mt-2" style="display: none;"></div>
                </div>

                <!-- Direct WhatsApp Assistance Button -->
                <div class="mb-4">
                    <a href="https://wa.me/919876543210?text={{ urlencode('Namaste! I would like to order ' . $product->name . ' on DhuriyaMart: ' . request()->url()) }}" 
                       target="_blank" 
                       class="btn btn-outline-success w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp fs-5"></i> Inquire / Order on WhatsApp
                    </a>
                </div>

                <!-- Value Props List -->
                <div class="row g-2 pt-3 border-top small text-muted">
                    <div class="col-sm-6"><i class="bi bi-truck text-primary me-2"></i> Standard Dispatch in 24-48 hrs</div>
                    <div class="col-sm-6"><i class="bi bi-shield-check text-success me-2"></i> Verified Indian Merchant</div>
                    <div class="col-sm-6"><i class="bi bi-arrow-counterclockwise text-info me-2"></i> 7-Day Easy Return Policy</div>
                    <div class="col-sm-6"><i class="bi bi-cash-coin text-warning me-2"></i> Cash on Delivery Available</div>
                </div>

            </div>
        </div>
    </div>

    <!-- Product Description & Specs Tabs -->
    <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border mb-5">
        <ul class="nav nav-tabs nav-fill mb-4" id="productTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold py-3" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">
                    Product Description
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                    Technical Specifications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                    Customer Reviews ({{ $product->reviews_count }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="productTabContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                <div class="lh-lg text-secondary">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            <!-- Specifications Tab -->
            <div class="tab-pane fade" id="specs" role="tabpanel">
                @if(!empty($product->specifications))
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <tbody>
                                @foreach($product->specifications as $specKey => $specVal)
                                    <tr>
                                        <th class="w-25 bg-light text-dark">{{ $specKey }}</th>
                                        <td>{{ $specVal }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No additional technical specifications provided for this product.</p>
                @endif
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <h6 class="fw-bold mb-3">Customer Feedback</h6>
                        @forelse($product->approvedReviews as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="fw-bold text-dark">{{ $review->user->name }}</div>
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-warning small mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                    @if($review->is_verified_purchase)
                                        <span class="badge bg-success bg-opacity-10 text-success ms-2 small">Verified Purchase</span>
                                    @endif
                                </div>
                                @if($review->title)
                                    <div class="fw-semibold small mb-1">{{ $review->title }}</div>
                                @endif
                                <p class="text-muted small mb-0">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <p class="text-muted small">No customer reviews yet. Be the first to review this product!</p>
                        @endforelse
                    </div>

                    <div class="col-lg-5">
                        <div class="card bg-light border-0 p-3 rounded-3">
                            <h6 class="fw-bold mb-3">Write a Customer Review</h6>
                            @auth
                                <form action="{{ route('products.reviews.store', $product->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Rating (1 to 5 Stars)</label>
                                        <select name="rating" class="form-select form-select-sm" required>
                                            <option value="5">5 - Excellent (⭐⭐⭐⭐⭐)</option>
                                            <option value="4">4 - Very Good (⭐⭐⭐⭐)</option>
                                            <option value="3">3 - Average (⭐⭐⭐)</option>
                                            <option value="2">2 - Poor (⭐⭐)</option>
                                            <option value="1">1 - Terrible (⭐)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Review Headline</label>
                                        <input type="text" name="title" class="form-control form-control-sm" placeholder="Summarize your review">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Review Details</label>
                                        <textarea name="comment" rows="3" class="form-control form-control-sm" required placeholder="What did you like or dislike?"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-dm-primary rounded-pill px-3">Submit Review</button>
                                </form>
                            @else
                                <p class="small text-muted mb-3">Please sign in with your customer account to submit a review.</p>
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-dark rounded-pill">Sign In to Review</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mb-4">
            <h4 class="fw-bold mb-3 text-dark">You May Also Like</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                @foreach($relatedProducts as $rel)
                    <div class="col">
                        <x-product-card :product="$rel" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function updateVariantDetails(variant) {
    if (!variant) return;
    const priceFormatted = '₹' + (variant.price_paise / 100).toFixed(2);
    document.getElementById('variantPrice').innerText = priceFormatted;
    
    const compareElem = document.getElementById('variantComparePrice');
    const discountElem = document.getElementById('variantDiscount');
    
    if (variant.compare_at_price_paise && variant.compare_at_price_paise > variant.price_paise) {
        const compareFormatted = '₹' + (variant.compare_at_price_paise / 100).toFixed(2);
        const discountPct = Math.round(((variant.compare_at_price_paise - variant.price_paise) / variant.compare_at_price_paise) * 100);
        if (compareElem) compareElem.innerText = compareFormatted;
        if (discountElem) discountElem.innerText = discountPct + '% OFF';
    }
}

function checkPincode() {
    const pin = document.getElementById('pincodeInput').value.trim();
    const res = document.getElementById('pincodeResult');
    if (pin.length === 6 && /^\d+$/.test(pin)) {
        res.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Delivery available to pincode <strong>' + pin + '</strong> in 2-3 business days! Cash on Delivery available.</span>';
        res.style.display = 'block';
    } else {
        res.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i> Please enter a valid 6-digit postal pincode (e.g. 275101).</span>';
        res.style.display = 'block';
    }
}
</script>
@endpush

