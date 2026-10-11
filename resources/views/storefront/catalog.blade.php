@extends('layouts.app')

@section('title', $category->name . ' - DhuriyaMart')
@section('meta_description', $category->description ?? 'Browse ' . $category->name . ' on DhuriyaMart.')

@section('content')
<div class="container pb-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            @if($category->parent)
                <li class="breadcrumb-item"><a href="{{ route('catalog.category', $category->parent->slug) }}" class="text-decoration-none">{{ $category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header -->
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border">
        <h2 class="fw-bold mb-1 text-dark">{{ $category->name }}</h2>
        @if($category->description)
            <p class="text-muted mb-0 small">{{ $category->description }}</p>
        @endif
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Filter Products</h6>

                <form action="{{ route('catalog.category', $category->slug) }}" method="GET">
                    <!-- Price Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Price (₹)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="{{ request('min_price') }}">
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="{{ request('max_price') }}">
                        </div>
                    </div>

                    <!-- Stock Filter -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStockCheck" {{ request('in_stock') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="inStockCheck">
                            In Stock Only
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-sm btn-dm-primary rounded-pill">Apply Filters</button>
                        <a href="{{ route('catalog.category', $category->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
                    </div>
                </form>

                <!-- Subcategories -->
                @if($category->children->count() > 0)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold small mb-2 text-uppercase text-muted">Subcategories</h6>
                        <ul class="list-unstyled mb-0 small d-flex flex-column gap-2">
                            @foreach($category->children as $sub)
                                <li>
                                    <a href="{{ route('catalog.category', $sub->slug) }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>{{ $sub->name }}</span>
                                        <span class="badge bg-light text-muted">{{ $sub->products()->count() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </aside>

        <!-- Product Listings -->
        <div class="col-lg-9">
            <!-- Sorting & Results bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm border mb-4 gap-2">
                <span class="text-muted small">
                    Showing <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong> items
                </span>

                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-nowrap">Sort by:</label>
                    <form method="GET" class="d-inline">
                        @foreach(request()->except('sort') as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Popularity</option>
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Customer Rating</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
                    @foreach($products as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="bg-white rounded-3 p-5 text-center shadow-sm border">
                    <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                    <h5>No products match your selection</h5>
                    <p class="text-muted small">Try broadening your price filters or checking other subcategories.</p>
                    <a href="{{ route('catalog.category', $category->slug) }}" class="btn btn-sm btn-dm-primary rounded-pill px-4">Clear Filters</a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

