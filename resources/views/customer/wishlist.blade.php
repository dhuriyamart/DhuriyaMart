@extends('layouts.app')

@section('title', 'My Wishlist - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">My Saved Wishlist</h3>
            <p class="text-muted small mb-0">Your favourite items saved for future purchase</p>
        </div>
        <a href="{{ route('account.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            &larr; Back to Dashboard
        </a>
    </div>

    @if($wishlists->count() > 0)
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
            @foreach($wishlists as $w)
                @if($w->variant && $w->variant->product)
                    <div class="col">
                        <x-product-card :product="$w->variant->product" />
                    </div>
                @endif
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $wishlists->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-heart fs-1 text-muted d-block mb-3"></i>
            <h5>Your Wishlist is Empty</h5>
            <p class="text-muted small">Tap the heart icon on any product card to save it here.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-dm-primary rounded-pill px-4">Explore Products</a>
            </div>
        </div>
    @endif
</div>
@endsection

