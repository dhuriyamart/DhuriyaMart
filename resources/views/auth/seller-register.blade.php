@extends('layouts.app')

@section('title', 'Become a Seller - DhuriyaMart Merchant Registration')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="badge bg-warning text-dark text-uppercase fw-bold rounded-pill mb-2 px-3 py-1">Merchant Onboarding</span>
                    <h3 class="fw-bold text-dark">Sell on DhuriyaMart</h3>
                    <p class="text-muted small">Register your shop, provide verification coordinates, and start receiving orders from across India</p>
                </div>

                <form action="{{ route('seller.register') }}" method="POST">
                    @csrf

                    <!-- 1. Account Owner Info -->
                    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Merchant Representative</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Person Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Full Name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Business Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="sales@yourbrand.in" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Direct Mobile / WhatsApp *</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98765 00000" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Portal Password (min 8 characters) *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <!-- 2. Shop Details -->
                    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Shop & Brand Profile</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Display Shop Name *</label>
                            <input type="text" name="shop_name" class="form-control" placeholder="e.g. Modern Kirana & Hardware Mart" value="{{ old('shop_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Registered Entity Legal Name</label>
                            <input type="text" name="legal_name" class="form-control" placeholder="Pvt Ltd / LLP / Proprietary" value="{{ old('legal_name') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Shop Description & Specialization</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Tell buyers about your products and experience...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- 3. Verification & Taxation -->
                    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Business Taxation & Verification</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Permanent Account Number (PAN) *</label>
                            <input type="text" name="pan_number" class="form-control text-uppercase font-monospace" placeholder="ABCDE1234F" value="{{ old('pan_number') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">GSTIN (Optional for un-registered artisans)</label>
                            <input type="text" name="gstin" class="form-control text-uppercase font-monospace" placeholder="29ABCDE1234F1Z5" value="{{ old('gstin') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Dispatch / Pickup Address for Couriers *</label>
                            <input type="text" name="pickup_address" class="form-control" placeholder="Warehouse / Factory / Shop street address with PIN" value="{{ old('pickup_address') }}" required>
                        </div>
                    </div>

                    <!-- 4. Bank Account for Settlements -->
                    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Bank Account for Settlements</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank Account Beneficiary Name *</label>
                            <input type="text" name="bank_account_name" class="form-control" placeholder="Name as in Bank Passbook" value="{{ old('bank_account_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank Account Number *</label>
                            <input type="text" name="bank_account_number" class="form-control font-monospace" value="{{ old('bank_account_number') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank IFSC Code *</label>
                            <input type="text" name="bank_ifsc" class="form-control text-uppercase font-monospace" placeholder="e.g. HDFC0001234" value="{{ old('bank_ifsc') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank & Branch Name *</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank, Koramangala" value="{{ old('bank_name') }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill w-100 fw-bold shadow-sm mb-3">
                        Submit Merchant Onboarding Application <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    Already a registered seller? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign In to Seller Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

