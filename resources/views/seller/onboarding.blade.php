@extends('layouts.seller')

@section('title', 'Seller Onboarding - DhuriyaMart Seller Hub')
@section('page_title', 'Seller Onboarding')

@section('content')
<div class="container-fluid px-0 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6 text-center">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 80px; height: 80px;">
                        <i class="bi bi-shop-window fs-1"></i>
                    </span>
                </div>

                <h3 class="fw-bold text-dark mb-2">Welcome to DhuriyaMart Seller Hub!</h3>
                <p class="text-muted mb-4">
                    Your seller account has been created. To start listing products and receiving customer orders across India, please finalize your shop setup and business profile.
                </p>

                <div class="row g-3 text-start mb-4">
                    <div class="col-12 p-3 bg-light rounded-3 d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                            <span class="fw-bold small">1</span>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">Register Shop Details</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Add your shop name, description, GSTIN and pickup address.</div>
                        </div>
                    </div>

                    <div class="col-12 p-3 bg-light rounded-3 d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                            <span class="fw-bold small">2</span>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">KYC &amp; Bank Account Verification</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Admin reviews your business verification documents and sets your settlement frequency.</div>
                        </div>
                    </div>

                    <div class="col-12 p-3 bg-light rounded-3 d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                            <span class="fw-bold small">3</span>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">Publish Products &amp; Ship Orders</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Create listings, manage inventory, print invoices, and track settlements.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('seller.register') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Complete Shop Registration
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

