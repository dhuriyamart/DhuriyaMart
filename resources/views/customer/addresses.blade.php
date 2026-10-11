@extends('layouts.app')

@section('title', 'Saved Addresses - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Saved Delivery Addresses</h3>
            <p class="text-muted small mb-0">Manage your shipping and billing coordinates for fast 1-click checkout</p>
        </div>
        <button class="btn btn-dm-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addressModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Address
        </button>
    </div>

    <div class="row g-3">
        @forelse($addresses as $addr)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative">
                    @if($addr->is_default)
                        <span class="badge bg-primary position-absolute top-0 end-0 m-3">Default</span>
                    @endif
                    <h6 class="fw-bold mb-1 text-dark">{{ $addr->name }}</h6>
                    <div class="small text-muted mb-2"><i class="bi bi-telephone me-1"></i>{{ $addr->phone }}</div>
                    <div class="small text-secondary mb-3 flex-grow-1">
                        {{ $addr->formatted_address }}
                    </div>
                    <div class="border-top pt-2 d-flex justify-content-end gap-2">
                        <form action="{{ route('account.addresses.delete', $addr->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="bg-white rounded-4 p-5 text-center shadow-sm border">
                    <i class="bi bi-geo-alt fs-1 text-muted d-block mb-3"></i>
                    <h5>No addresses saved yet</h5>
                    <p class="text-muted small">Add your delivery location to streamline checkout.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('account.addresses.save') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Delivery Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Recipient Full Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Mobile Phone Number *</label>
                            <input type="text" name="phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Street Address, House/Flat No *</label>
                            <input type="text" name="address_line1" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Area, Colony (Optional)</label>
                            <input type="text" name="address_line2" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Landmark (Optional)</label>
                            <input type="text" name="landmark" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">City *</label>
                            <input type="text" name="city" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">State *</label>
                            <input type="text" name="state" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Postal PIN Code *</label>
                            <input type="text" name="postal_code" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defCheck">
                                <label class="form-check-label small" for="defCheck">
                                    Set as default shipping address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-dm-primary rounded-pill px-4">Save Address</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

