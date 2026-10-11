@extends('layouts.app')

@section('title', 'आपका डिजिटल स्मार्ट मार्ट कार्ड — Dhuriya Mart')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>डिजिटल ग्राहक पहचान कार्ड (Digital Mart Card)
                </h3>
                <p class="text-muted small">
                    धुरिया मार्ट की किसी भी ग्रामीण शाखा में खरीदारी करते समय इस क्यूआर कोड को काउंटर या कियोस्क पर स्कैन कराएं।
                </p>
            </div>

            <!-- DIGITAL SMART MART CARD -->
            <div class="card border-0 shadow-lg text-white mb-4 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #065f46 100%); border-radius: 20px; min-height: 260px;">
                <!-- Decorative background elements -->
                <div class="position-absolute end-0 top-0 p-3 opacity-25">
                    <i class="bi bi-bag-check-fill" style="font-size: 10rem; margin-right: -40px; margin-top: -40px;"></i>
                </div>

                <div class="card-body p-4 position-relative d-flex flex-column justify-content-between">
                    <!-- Card Top Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-shop fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-extrabold mb-0 tracking-tight text-white">DHURIYA <span class="text-warning">MART</span></h5>
                                <span class="small text-white-50 text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Smart Village Member Card</span>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-patch-check-fill me-1"></i> VERIFIED
                        </span>
                    </div>

                    <!-- Middle Content: Customer Info & QR Code -->
                    <div class="row align-items-center my-2">
                        <div class="col-7">
                            <div class="small text-white-50 text-uppercase" style="font-size: 0.7rem;">Customer Name</div>
                            <h4 class="fw-bold mb-1 text-white text-truncate">{{ $user->name }}</h4>

                            <div class="small text-white-50 text-uppercase mt-2" style="font-size: 0.7rem;">Village / Location</div>
                            <div class="fw-bold text-white small">{{ $user->village_name ?? 'Ranipur Village, Mau' }}</div>

                            <div class="small text-white-50 text-uppercase mt-2" style="font-size: 0.7rem;">Mobile</div>
                            <div class="small text-white">{{ $user->phone ?? '+91 9876500005' }}</div>
                        </div>

                        <div class="col-5 text-end">
                            <div class="p-2 bg-white d-inline-block rounded-3 shadow">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $user->qr_token }}" 
                                     alt="Member QR" class="img-fluid" style="width: 105px; height: 105px;">
                            </div>
                            <div class="small mt-1 text-white-50" style="font-size: 0.65rem;">Scan at Billing Desk</div>
                        </div>
                    </div>

                    <!-- Bottom Bar: Customer ID & Wallet Balance -->
                    <div class="d-flex justify-content-between align-items-end pt-3 border-top border-white border-opacity-25">
                        <div>
                            <span class="text-white-50 d-block" style="font-size: 0.65rem;">CUSTOMER ID</span>
                            <span class="fw-bold fs-6 font-monospace text-warning">{{ $user->customer_id }}</span>
                        </div>
                        <div class="text-end">
                            <span class="text-white-50 d-block" style="font-size: 0.65rem;">MART WALLET BALANCE</span>
                            <span class="fw-extrabold fs-5 text-white">₹{{ number_format($user->wallet_balance_rupees, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                <button onclick="window.print()" class="btn btn-outline-dark w-100 fw-bold py-2">
                    <i class="bi bi-printer me-1"></i> कार्ड प्रिंट करें (Print Card)
                </button>
                <a href="{{ route('pos.kiosk') }}" class="btn btn-success w-100 fw-bold py-2">
                    <i class="bi bi-display me-1"></i> कियोस्क पर जाएं (Kiosk)
                </a>
            </div>

            <!-- Recent In-Store Purchases with Exit Verification Badge -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-clock-history text-primary me-2"></i>आपकी पिछली इन-स्टोर खरीदारी (In-Store Purchases)
                </h6>
                <div class="list-group list-group-flush">
                    @forelse($recentInStoreOrders as $ord)
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">{{ $ord->order_number }}</strong>
                                <div class="small text-muted">{{ $ord->created_at->format('d M Y, h:i A') }} | {{ $ord->items->count() }} items</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">₹{{ number_format($ord->total_amount_paise / 100, 2) }}</div>
                                @if($ord->exitVerification && $ord->exitVerification->isPassed())
                                    <span class="badge bg-success" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>Exit Passed</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">In-Store POS</span>
                                @endif
                                <a href="{{ route('pos.receipt', $ord->order_number) }}" class="btn btn-sm btn-link p-0 d-block small" target="_blank">View Receipt</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted small">No past in-store visits recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

