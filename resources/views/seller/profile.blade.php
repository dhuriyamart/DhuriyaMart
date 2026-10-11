@extends('layouts.seller')

@section('title', 'Shop Profile & KYC - ' . ($shop ? $shop->name : 'Onboarding'))
@section('page_title', 'Shop Configuration & Bank KYC')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                <form action="{{ route('seller.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- 1. Shop Info -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Shop Branding & Contact</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Shop Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $shop?->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Support Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $shop?->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Support Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $shop?->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">About the Shop / Mission</label>
                            <textarea name="description" rows="2" class="form-control">{{ old('description', $shop?->description) }}</textarea>
                        </div>
                    </div>

                    <!-- 2. KYC Coordinates -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Business KYC & Dispatch Location</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">PAN Number *</label>
                            <input type="text" name="pan_number" class="form-control text-uppercase font-monospace" value="{{ old('pan_number', $profile?->pan_number) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">GSTIN</label>
                            <input type="text" name="gstin" class="form-control text-uppercase font-monospace" value="{{ old('gstin', $profile?->gstin) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Warehouse Pickup Address for Couriers *</label>
                            <textarea name="pickup_address" rows="2" class="form-control" required>{{ old('pickup_address', $profile?->pickup_address) }}</textarea>
                        </div>
                    </div>

                    <!-- 3. Banking Settlement Coordinates -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Settlement Bank Account</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Account Holder Name *</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $profile?->bank_account_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Account Number *</label>
                            <input type="text" name="bank_account_number" class="form-control font-monospace" value="{{ old('bank_account_number', $profile?->bank_account_number) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank IFSC *</label>
                            <input type="text" name="bank_ifsc" class="form-control text-uppercase font-monospace" value="{{ old('bank_ifsc', $profile?->bank_ifsc) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank & Branch *</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile?->bank_name) }}" required>
                        </div>
                    </div>

                    <!-- 4. Upload Documents -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Upload Verification Documents</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Document Type</label>
                            <select name="document_type" class="form-select">
                                <option value="pan_card">PAN Card</option>
                                <option value="gst_certificate">GST Registration Certificate</option>
                                <option value="bank_proof">Cancelled Cheque / Bank Statement</option>
                                <option value="address_proof">Electricity Bill / Rent Agreement</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Document File (PDF, PNG, JPG)</label>
                            <input type="file" name="document_file" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                        Save Shop Profile & Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

