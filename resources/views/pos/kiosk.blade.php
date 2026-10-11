@extends('layouts.app')

@section('title', 'Self-Service Smart Kiosk — ' . $store->name)

@section('content')
<div class="container-fluid py-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 92vh; color: #f8fafc;">
    <!-- Kiosk Top Banner -->
    <div class="container mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center bg-dark bg-opacity-75 p-3 rounded-4 border border-secondary shadow">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-3 p-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-display fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-white">स्वयं-सेवा स्मार्ट बिलिंग (Self-Service Kiosk)</h3>
                    <span class="text-warning small">
                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $store->name }} ({{ $store->village_name }})
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('locale.set', app()->getLocale() == 'hi' ? 'en' : 'hi') }}" class="btn btn-outline-warning fw-bold">
                    <i class="bi bi-translate me-1"></i> {{ app()->getLocale() == 'hi' ? 'English में बदलें' : 'हिंदी में बदलें' }}
                </a>
                <a href="{{ route('pos.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-person-badge me-1"></i> Staff POS
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success fs-5 p-3 rounded-4 mb-3 border-0 shadow">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger fs-5 p-3 rounded-4 mb-3 border-0 shadow">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="row g-4">
            <!-- Left: Barcode Scan & Visual Product Selection -->
            <div class="col-lg-7">
                <!-- Barcode Scan Box -->
                <div class="bg-dark bg-opacity-50 p-4 rounded-4 border border-secondary mb-4 shadow">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-upc-scan text-success me-2"></i>सामान का बारकोड स्कैन करें (Scan Product Barcode)
                    </h5>
                    <form action="{{ route('pos.scan') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                        <input type="text" name="code" class="form-control form-control-lg fs-4 py-3 fw-bold bg-white text-dark border-0 rounded-3" 
                               placeholder="बारकोड स्कैन करें या कोड लिखें..." autofocus required>
                        <button type="submit" class="btn btn-success btn-lg px-4 fw-bold rounded-3">
                            <i class="bi bi-plus-circle me-1"></i> जोड़ें (Add)
                        </button>
                    </form>
                </div>

                <!-- Touch Categories & Fast-Picks -->
                <div class="bg-dark bg-opacity-50 p-4 rounded-4 border border-secondary shadow">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-hand-index-thumb text-warning me-2"></i>स्क्रीन छूकर सामान चुनें (Touch to Select Items)
                    </h5>
                    <div class="row row-cols-2 row-cols-sm-3 g-3" style="max-height: 460px; overflow-y: auto;">
                        @foreach($quickProducts as $variant)
                            <div class="col">
                                <form action="{{ route('pos.scan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                                    <input type="hidden" name="code" value="{{ $variant->sku }}">
                                    <button type="submit" class="card h-100 w-100 p-3 bg-secondary bg-opacity-25 border border-secondary rounded-3 text-start text-white text-decoration-none shadow-sm hover-scale" style="transition: transform 0.15s;">
                                        <span class="badge bg-primary mb-2 align-self-start">{{ $variant->sku }}</span>
                                        <div class="fw-bold fs-6 mb-1 text-truncate w-100">{{ $variant->name }}</div>
                                        <div class="small text-white-50 text-truncate w-100 mb-2">{{ $variant->product->name }}</div>
                                        <div class="mt-auto d-flex justify-content-between align-items-center w-100">
                                            <span class="fs-5 fw-extrabold text-warning">₹{{ number_format($variant->price_rupees, 2) }}</span>
                                            <span class="btn btn-sm btn-success rounded-circle"><i class="bi bi-plus"></i></span>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right: Live Shopping Basket & One-Touch UPI Payment -->
            <div class="col-lg-5">
                <div class="bg-dark bg-opacity-75 p-4 rounded-4 border border-secondary shadow h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom border-secondary mb-3">
                        <h4 class="fw-bold text-white mb-0">
                            <i class="bi bi-basket-fill text-warning me-2"></i>आपकी टोकरी (Cart)
                        </h4>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">{{ $session->item_count }} सामान</span>
                    </div>

                    <!-- Cart Items List -->
                    <div class="flex-grow-1 overflow-auto mb-3" style="max-height: 320px;">
                        @if(empty($session->cart_items))
                            <div class="text-center py-5 text-white-50">
                                <i class="bi bi-cart3 fs-1 d-block mb-3 text-secondary"></i>
                                <h5 class="fw-bold">आपकी टोकरी खाली है</h5>
                                <p class="small">कृपया बारकोड स्कैनर के सामने सामान लाएं या बायीं ओर से चुनें।</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($session->cart_items as $item)
                                    <div class="list-group-item bg-transparent text-white border-secondary px-0 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-6">{{ $item['name'] }}</div>
                                            <span class="small text-white-50">₹{{ number_format($item['price_paise'] / 100, 2) }} × {{ $item['quantity'] }}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-warning fs-5">₹{{ number_format($item['subtotal_paise'] / 100, 2) }}</div>
                                            <form action="{{ route('pos.items.remove', $item['variant_id']) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="session_id" value="{{ $session->id }}">
                                                <button type="submit" class="btn btn-link text-danger p-0 small text-decoration-none">हटाएं (Remove)</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Cart Total Box -->
                    <div class="bg-black bg-opacity-50 p-4 rounded-3 border border-secondary mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 text-white">कुल देय राशि (Total Amount):</span>
                            <span class="fs-2 fw-extrabold text-success">₹{{ number_format($session->total_amount_rupees, 2) }}</span>
                        </div>
                    </div>

                    <!-- Payment Choices -->
                    @if(!empty($session->cart_items))
                        <div class="d-grid gap-3">
                            <!-- Instant UPI QR Modal Trigger -->
                            <button type="button" class="btn btn-primary btn-lg py-3 fw-bold fs-5 rounded-3 shadow" data-bs-toggle="modal" data-bs-target="#kioskUpiModal">
                                <i class="bi bi-qr-code-scan me-2"></i> यूपीआई से तुरंत भुगतान करें (Scan UPI QR)
                            </button>

                            <!-- Pay via Cash at Counter Token -->
                            <form action="{{ route('pos.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $session->id }}">
                                <input type="hidden" name="payment_method" value="cash">
                                <button type="submit" class="btn btn-outline-warning btn-lg py-3 fw-bold w-100 fs-5 rounded-3">
                                    <i class="bi bi-cash-coin me-2"></i> काउंटर पर नकद भुगतान करें (Cash Token)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- UPI Payment Modal -->
<div class="modal fade" id="kioskUpiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary rounded-4 p-3 text-center">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-phone me-2"></i>UPI क्यूआर कोड स्कैन करें</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="bg-white p-3 d-inline-block rounded-4 shadow-lg mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=dhuriyamart@sbi%26pn=DhuriyaMart%26am={{ $session->total_amount_rupees }}%26cu=INR" 
                         alt="UPI QR Code" class="img-fluid" style="width: 200px; height: 200px;">
                </div>
                <h3 class="fw-extrabold text-success mb-2">₹{{ number_format($session->total_amount_rupees, 2) }}</h3>
                <p class="text-white-50 small mb-4">GPay, PhonePe, Paytm या BHIM से स्कैन करके भुगतान करें</p>

                <!-- Confirm Simulated / Completed UPI Payment -->
                <form action="{{ route('pos.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                    <input type="hidden" name="payment_method" value="upi">
                    <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-pill shadow">
                        <i class="bi bi-check2-circle me-2"></i> भुगतान हो गया (Confirm Paid)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale:hover {
        transform: translateY(-3px);
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-color: #38bdf8 !important;
    }
</style>
@endsection

