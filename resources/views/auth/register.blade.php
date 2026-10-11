@extends('layouts.app')

@section('title', 'Create Customer Account - DhuriyaMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Create Account</h3>
                    <p class="text-muted small">Join DhuriyaMart to track orders, save addresses & earn discounts</p>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Rahul Verma" value="{{ old('name') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mobile Phone (Optional)</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('phone') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat your password" required>
                    </div>

                    <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill w-100 fw-bold shadow-sm mb-3">
                        Register Account <i class="bi bi-person-check ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    Already registered? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign In here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

