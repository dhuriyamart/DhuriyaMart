@extends('layouts.admin')

@section('title', 'Merchant KYC Review - ' . $shop->name)
@section('page_title', 'Merchant KYC Dossier: ' . $shop->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">{{ $shop->name }}</h4>
            <span class="text-muted small">Registered on {{ $shop->created_at->format('d M Y') }} &bull; Current Status: </span>
            <x-status-badge :status="$shop->status" />
        </div>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            &larr; Back to Sellers
        </a>
    </div>

    <div class="row g-4">
        <!-- Main KYC Dossier Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Business & Taxation Credentials</h5>
                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Legal Business Entity:</span>
                        <strong class="fs-6 text-dark">{{ $shop->legal_name ?? $shop->name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Merchant Account Owner:</span>
                        <strong>{{ $shop->user->name }}</strong> ({{ $shop->user->email }})
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Income Tax PAN:</span>
                        <code class="fs-6 fw-bold text-dark">{{ $shop->profile?->pan_number ?? 'Not Provided' }}</code>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">GSTIN Number:</span>
                        <code class="fs-6 fw-bold text-dark">{{ $shop->profile?->gstin ?? 'Not Provided' }}</code>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block">Warehouse Logistics Pickup Address:</span>
                        <p class="mb-0 text-secondary">{{ $shop->profile?->pickup_address ?? 'Not Provided' }}</p>
                    </div>
                </div>
            </div>

            <!-- Settlement Bank Account -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Settlement Bank Account Coordinates</h5>
                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Account Holder:</span>
                        <strong>{{ $shop->profile?->bank_account_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Account Number:</span>
                        <code class="fs-6 fw-bold text-dark">{{ $shop->profile?->bank_account_number ?? 'N/A' }}</code>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">IFSC Code:</span>
                        <code class="text-dark fw-bold">{{ $shop->profile?->bank_ifsc ?? 'N/A' }}</code>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Bank & Branch:</span>
                        <span>{{ $shop->profile?->bank_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- KYC Documents -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Attached Verification Documents</h5>
                @if($shop->documents->count() > 0)
                    <ul class="list-unstyled mb-0">
                        @foreach($shop->documents as $doc)
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <strong class="text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</strong>
                                    <div class="small text-muted">{{ $doc->original_name }}</div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $doc->status }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">No digital documents attached to this profile.</p>
                @endif
            </div>
        </div>

        <!-- Verification Action Decision Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Admin Decision</h5>

                <form action="{{ route('admin.sellers.verify', $shop->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Platform Commission Rate (%) *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="commission_rate" class="form-control" value="{{ old('commission_rate', $shop->commission_rate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Standard marketplace default is 10.00%</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Verification Decision *</label>
                        <select name="status" class="form-select" required>
                            <option value="approved" {{ $shop->status === 'approved' ? 'selected' : '' }}>Approve Merchant (Active)</option>
                            <option value="pending" {{ $shop->status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="suspended" {{ $shop->status === 'suspended' ? 'selected' : '' }}>Suspend Shop</option>
                            <option value="rejected" {{ $shop->status === 'rejected' ? 'selected' : '' }}>Reject KYC</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rejection / Suspension Reason (Optional)</label>
                        <textarea name="rejection_reason" rows="3" class="form-control form-control-sm" placeholder="Provide notes if rejecting or suspending this seller...">{{ $shop->profile?->rejection_reason }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold">
                        Save Verification Decision
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top small text-muted">
                    <div><strong>Active Products:</strong> {{ $shop->products->count() }}</div>
                    <div><strong>Assigned Orders:</strong> {{ $shop->sellerOrders->count() }}</div>
                    <div><strong>Ledger Balance:</strong> {{ \App\Support\Money::format($shop->current_balance_paise) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

