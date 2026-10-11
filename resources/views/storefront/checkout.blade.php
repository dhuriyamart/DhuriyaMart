@extends('layouts.app')

@section('title', 'Secure Checkout - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-shield-lock-fill me-2 text-primary"></i>Secure Checkout</h3>

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                
                <!-- 1. Delivery Address Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                        Delivery Address
                    </h5>

                    @if($addresses->count() > 0)
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Select Saved Address:</label>
                            <div class="row g-2">
                                @foreach($addresses as $addr)
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="address_id" id="addr_{{ $addr->id }}" value="{{ $addr->id }}" {{ $loop->first ? 'checked' : '' }} onchange="toggleNewAddress(false)">
                                        <label class="btn btn-outline-secondary w-100 text-start p-3 rounded-3 h-100" for="addr_{{ $addr->id }}">
                                            <div class="fw-bold text-dark">{{ $addr->name }}</div>
                                            <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i>{{ $addr->phone }}</div>
                                            <div class="small text-secondary">{{ $addr->formatted_address }}</div>
                                            @if($addr->is_default)
                                                <span class="badge bg-light text-primary border mt-2">Default</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="address_id" value="" id="newAddrRadio" onchange="toggleNewAddress(true)">
                            <label class="form-check-label fw-semibold" for="newAddrRadio">
                                + Ship to a new address
                            </label>
                        </div>
                    @endif

                    <!-- New Address Fields -->
                    <div id="newAddressFields" style="{{ $addresses->count() > 0 ? 'display: none;' : '' }}">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Enter Delivery Coordinates</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Recipient Full Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Contact Mobile Number *</label>
                                <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('phone', auth()->user()->phone) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Flat, House No., Building, Street *</label>
                                <input type="text" name="address_line1" class="form-control" placeholder="House/Flat No, Apartment, Street" value="{{ old('address_line1') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Area, Colony, Sector (Optional)</label>
                                <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Landmark (Optional)</label>
                                <input type="text" name="landmark" class="form-control" placeholder="Near Temple / Metro / Hospital" value="{{ old('landmark') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">City / District *</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">State *</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state', 'Karnataka') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Postal PIN Code *</label>
                                <input type="text" name="postal_code" class="form-control" placeholder="6 digits" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Multi-Vendor Order Breakdown -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                        Package Breakdown by Merchant ({{ $groupedItems->count() }} Dispatch Package(s))
                    </h5>

                    @foreach($groupedItems as $shopId => $items)
                        @php $shop = $items->first()->variant?->product?->shop; @endphp
                        <div class="border rounded-3 p-3 mb-3 bg-light">
                            <div class="fw-bold text-dark mb-2">
                                <i class="bi bi-shop text-primary me-2"></i> Package from: {{ $shop ? $shop->name : 'Merchant' }}
                            </div>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($items as $i)
                                    <li class="d-flex justify-content-between py-1 border-top border-light">
                                        <span>{{ $i->variant->product->name }} ({{ $i->variant->name }}) × {{ $i->quantity }}</span>
                                        <span class="fw-bold">{{ $i->formatted_total }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <!-- 3. Payment Method Choice -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                        Payment Method
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_razorpay" value="razorpay" checked>
                            <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3 h-100" for="pay_razorpay">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold fs-6">Online Payment</span>
                                    <span class="badge bg-primary">Fast & Secure</span>
                                </div>
                                <div class="small text-muted">UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, NetBanking</div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_cod" value="cod">
                            <label class="btn btn-outline-secondary w-100 text-start p-3 rounded-3 h-100" for="pay_cod">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold fs-6">Cash on Delivery</span>
                                    <i class="bi bi-cash-stack text-success fs-5"></i>
                                </div>
                                <div class="small text-muted">Pay in cash or scan UPI upon doorstep package handover.</div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-muted">Delivery Instructions (Optional):</label>
                        <textarea name="customer_notes" rows="2" class="form-control form-control-sm" placeholder="Any specific instructions for delivery personnel..."></textarea>
                    </div>
                </div>

            </div>

            <!-- Order Summary Sticky Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Price Details</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal ({{ $totals['items_count'] }} items)</span>
                        <span class="fw-semibold">{{ \App\Support\Money::format($totals['subtotal_paise']) }}</span>
                    </div>

                    @if($totals['discount_paise'] > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Coupon Discount</span>
                            <span class="fw-bold">- {{ \App\Support\Money::format($totals['discount_paise']) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Charges</span>
                        @if($totals['shipping_fee_paise'] == 0)
                            <span class="text-success fw-bold">FREE</span>
                        @else
                            <span class="fw-semibold">{{ \App\Support\Money::format($totals['shipping_fee_paise']) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">GST Tax (5%)</span>
                        <span class="fw-semibold">{{ \App\Support\Money::format($totals['tax_paise']) }}</span>
                    </div>

                    <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-baseline">
                        <span class="fs-5 fw-bold text-dark">Amount Payable</span>
                        <span class="fs-4 fw-extrabold text-primary" style="font-weight: 800;">
                            {{ \App\Support\Money::format($totals['total_amount_paise']) }}
                        </span>
                    </div>

                    <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-sm">
                        Confirm & Place Order <i class="bi bi-lock-fill ms-1"></i>
                    </button>

                    <div class="text-center mt-3">
                        <small class="text-muted">By clicking place order, you agree to DhuriyaMart's <a href="{{ route('catalog.page', 'terms') }}" class="text-decoration-none">Terms</a>.</small>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleNewAddress(show) {
    document.getElementById('newAddressFields').style.display = show ? 'block' : 'none';
}
</script>
@endpush

