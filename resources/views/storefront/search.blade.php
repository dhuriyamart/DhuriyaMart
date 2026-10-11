@extends('layouts.app')

@section('title', 'Search: ' . ($query ?: 'All Products') . ' - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <!-- Search Bar & Results Heading -->
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border">
        <h3 class="fw-bold text-dark mb-1">
            Search Results for <span class="text-primary">"{{ $query }}"</span>
        </h3>
        <p class="text-muted small mb-0">Found {{ $products->total() }} matching item(s) in DhuriyaMart inventory</p>
    </div>

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
        <div class="bg-white rounded-4 p-5 text-center shadow-sm border my-3">
            <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold">No products found for "{{ $query }}"</h5>
            <p class="text-muted small">Please check spelling or search with terms like "atta", "rice", "dal", "oil", "pipe", "screws", "wire", "paint".</p>
            <a href="{{ route('home') }}" class="btn btn-dm-primary rounded-pill px-4">Browse Homepage</a>
        </div>
    @endif
</div>
@endsection

