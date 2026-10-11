@extends('layouts.app')

@section('title', 'Sign In - DhuriyaMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Sign In</h3>
                    <p class="text-muted small">Access your DhuriyaMart customer or seller account</p>
                </div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill w-100 fw-bold shadow-sm mb-3">
                        Sign In <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-4 small text-muted">
                    Don't have a customer account? <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Register here</a>
                    <div class="mt-2">
                        Want to sell products? <a href="{{ route('seller.register') }}" class="text-warning text-dark fw-bold text-decoration-none">Create a Seller Account &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

