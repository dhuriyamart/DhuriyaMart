@extends('layouts.app')

@section('title', 'Staff-Assisted POS — ' . $store->name)

@section('content')
<div class="container-fluid px-3 py-3" style="background-color: #f1f5f9; min-height: 90vh;">
    <!-- POS Header Bar -->
    <div class="bg-white rounded-3 shadow-sm p-3 mb-3 border d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-calculator-fill fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">{{ $store->name }} <span class="badge bg-success ms-2">Staff POS</span></h5>
                <span class="text-muted small">
                    <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $store->village_name }}, {{ $store->district }} |
                    <i class="bi bi-person-badge me-1 text-primary"></i>Cashier: {{ auth()->user()->name ?? 'Operator' }} |
                    Session: <code class="text-dark fw-bold">{{ $session->session_code }}</code>
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($activeRegister)
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-cash-stack text-success me-1"></i> Register Active: <strong>₹{{ number_format($activeRegister->expected_cash_rupees, 2) }}</strong>
                </span>
            @else
                <a href="{{ route('admin.cash-desk.index') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Open Cash Drawer First
                </a>
            @endif

            <a href="{{ route('pos.exit-verification') }}" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="bi bi-shield-check me-1"></i> Exit Desk
            </a>
            <a href="{{ route('pos.kiosk') }}" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="bi bi-display me-1"></i> Switch to Kiosk
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- LEFT COLUMN: Barcode Scanner, Customer Association, Quick Products -->
        <div class="col-lg-7">
            <!-- Scan / Search Form -->
            <div class="bg-white rounded-3 shadow-sm p-3 mb-3 border">
                <form action="{{ route('pos.scan') }}" method="POST" id="scanForm" class="row g-2">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="bi bi-upc-scan text-primary me-1"></i> Scan Barcode or Type SKU / Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-barcode"></i></span>
                            <input type="text" name="code" id="barcodeInput" class="form-control form-control-lg fw-bold" 
                                   placeholder="Scan barcode or enter SKU (e.g. KIR-WHEAT-01)..." autofocus required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Qty</label>
                        <input type="number" name="quantity" class="form-control form-control-lg text-center fw-bold" value="1" min="1">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                </form>
            </div>

            <!-- Customer Lookup by QR Code / Phone / ID -->
            <div class="bg-white rounded-3 shadow-sm p-3 mb-3 border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark small"><i class="bi bi-person-bounding-box text-primary me-1"></i> Customer Identification (QR / Mobile / ID)</span>
                    <span id="customerFoundBadge" class="badge bg-secondary">Walk-in Customer</span>
                </div>
                <div class="input-group">
                    <input type="text" id="customerSearchInput" class="form-control" 
                           placeholder="Enter Phone, Customer ID (e.g. DM-CUST-10001) or Scan Customer QR...">
                    <button class="btn btn-outline-secondary" type="button" id="searchCustomerBtn">
                        <i class="bi bi-search me-1"></i> Lookup
                    </button>
                </div>
                <div id="customerDetailsBox" class="mt-2 p-2 bg-light rounded border d-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="custNameText" class="text-dark"></strong> 
                            <span id="custIdText" class="badge bg-primary ms-1"></span>
                            <span id="custVillageText" class="text-muted small ms-2"></span>
                        </div>
                        <div>
                            Wallet Balance: <strong id="custWalletText" class="text-success">₹0.00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fast Touch Catalog for Top Kirana & Hardware Products -->
            <div class="bg-white rounded-3 shadow-sm p-3 border">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-warning me-1"></i> Fast-Tap Product Catalog</h6>
                    <span class="badge bg-light text-muted border">{{ $quickProducts->count() }} fast moving items</span>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-2" style="max-height: 480px; overflow-y: auto;">
                    @foreach($quickProducts as $variant)
                        <div class="col">
                            <div class="card h-100 border text-center p-2 shadow-sm product-touch-tile" 
                                 style="cursor: pointer; transition: 0.2s;"
                                 onclick="addVariantToCart('{{ $variant->sku }}')">
                                <span class="badge bg-secondary mb-1 align-self-start small" style="font-size: 0.65rem;">{{ $variant->sku }}</span>
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;" title="{{ $variant->product->name }} - {{ $variant->name }}">
                                    {{ $variant->name }}
                                </div>
                                <div class="text-muted small text-truncate">{{ $variant->product->name }}</div>
                                <div class="mt-2 text-primary fw-extrabold fs-6">
                                    ₹{{ number_format($variant->price_rupees, 2) }}
                                </div>
                                <div class="small text-muted" style="font-size: 0.7rem;">
                                    Stock: <span class="fw-bold {{ $variant->stock_quantity <= 10 ? 'text-danger' : 'text-success' }}">{{ $variant->stock_quantity }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Billing Basket & Tender Settlement -->
        <div class="col-lg-5">
            <div class="bg-white rounded-3 shadow-sm p-3 border h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-receipt text-primary me-1"></i> Current Bill 
                        <span class="badge bg-primary rounded-pill ms-1">{{ $session->item_count }} items</span>
                    </h5>
                    @if(!empty($session->cart_items))
                        <form action="{{ route('pos.clear') }}" method="POST" onsubmit="return confirm('Clear entire bill?');">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ $session->id }}">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i> Clear
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Basket Items List -->
                <div class="flex-grow-1 overflow-auto mb-3" style="max-height: 380px;">
                    @if(empty($session->cart_items))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                            <p class="mb-0">Bill is currently empty.</p>
                            <small>Scan a product barcode or tap an item on the left.</small>
                        </div>
                    @else
                        <table class="table table-sm align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center" style="width: 110px;">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->cart_items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark small">{{ $item['name'] }}</div>
                                            <code class="text-muted" style="font-size: 0.7rem;">{{ $item['sku'] }}</code>
                                        </td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary px-2" type="button" 
                                                        onclick="updateQty({{ $item['variant_id'] }}, {{ $item['quantity'] - 1 }})">-</button>
                                                <input type="text" class="form-control text-center p-0 fw-bold" value="{{ $item['quantity'] }}" readonly>
                                                <button class="btn btn-outline-secondary px-2" type="button" 
                                                        onclick="updateQty({{ $item['variant_id'] }}, {{ $item['quantity'] + 1 }})">+</button>
                                            </div>
                                        </td>
                                        <td class="text-end small">₹{{ number_format($item['price_paise'] / 100, 2) }}</td>
                                        <td class="text-end fw-bold small text-dark">₹{{ number_format($item['subtotal_paise'] / 100, 2) }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-link text-danger p-0 ms-1" onclick="removeItem({{ $item['variant_id'] }})">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Subtotal & Grand Total -->
                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between mb-1 text-muted small">
                        <span>Items Subtotal:</span>
                        <span>₹{{ number_format($session->subtotal_rupees, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted small">
                        <span>GST / Taxes:</span>
                        <span class="text-success">Inclusive (₹0.00)</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fs-5 fw-bold text-dark">Total Amount Due:</span>
                        <span class="fs-4 fw-extrabold text-primary">₹{{ number_format($session->total_amount_rupees, 2) }}</span>
                    </div>
                </div>

                <!-- Checkout Settlement Box -->
                <form action="{{ route('pos.checkout') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                    <input type="hidden" name="customer_identifier" id="checkoutCustomerIdentifier" value="">

                    <!-- Payment Mode Tabs -->
                    <label class="form-label small fw-bold text-muted mb-1">Select Payment Method</label>
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked onchange="togglePayMode('cash')">
                        <label class="btn btn-outline-success fw-bold" for="payCash"><i class="bi bi-cash-stack me-1"></i> Cash</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payUpi" value="upi" onchange="togglePayMode('upi')">
                        <label class="btn btn-outline-primary fw-bold" for="payUpi"><i class="bi bi-qr-code me-1"></i> UPI QR</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payWallet" value="wallet" onchange="togglePayMode('wallet')">
                        <label class="btn btn-outline-warning text-dark fw-bold" for="payWallet"><i class="bi bi-wallet2 me-1"></i> Wallet</label>
                    </div>

                    <!-- Cash Tender Calculator -->
                    <div id="cashTenderBox" class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Cash Received (₹)</label>
                                <input type="number" step="0.01" name="cash_tendered_rupees" id="cashReceivedInput" 
                                       class="form-control form-control-lg fw-bold text-dark" 
                                       placeholder="Enter ₹" value="{{ $session->total_amount_rupees }}"
                                       oninput="calculateChange()">
                            </div>
                            <div class="col-6 text-end">
                                <label class="form-label small fw-bold text-muted mb-1">Change to Return</label>
                                <div class="fs-4 fw-extrabold text-success" id="changeReturnDisplay">₹0.00</div>
                            </div>
                        </div>
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickTender(100)">₹100</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickTender(200)">₹200</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickTender(500)">₹500</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickTender(2000)">₹2000</button>
                            <button type="button" class="btn btn-sm btn-outline-success ms-auto" onclick="setExactTender()">Exact</button>
                        </div>
                    </div>

                    <!-- UPI Dynamic Payment Box (Hidden by default) -->
                    <div id="upiBox" class="p-3 bg-light rounded-3 border mb-3 d-none text-center">
                        <div class="p-3 bg-white d-inline-block rounded shadow-sm border mb-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=dhuriyamart@sbi%26pn=DhuriyaMart%26am={{ $session->total_amount_rupees }}%26cu=INR" 
                                 alt="UPI QR Code" class="img-fluid" style="width: 140px; height: 140px;">
                        </div>
                        <div class="fw-bold text-dark small">Scan to pay ₹{{ number_format($session->total_amount_rupees, 2) }} via GPay / PhonePe / Paytm</div>
                        <span class="badge bg-info text-dark mt-1">UPI ID: dhuriyamart@sbi</span>
                    </div>

                    <!-- Wallet Pay Box (Hidden by default) -->
                    <div id="walletBox" class="p-3 bg-light rounded-3 border mb-3 d-none">
                        <div class="small text-muted mb-1">Deduct directly from customer's preloaded Village Smart Mart Wallet balance.</div>
                        <div class="fw-bold text-dark">Selected Customer Balance: <span id="walletBoxBalanceText" class="text-success">₹0.00</span></div>
                    </div>

                    <!-- Finalize and Print Bill Button -->
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow" 
                            {{ empty($session->cart_items) ? 'disabled' : '' }}>
                        <i class="bi bi-printer-fill me-2"></i> COMPLETE BILL &amp; PRINT RECEIPT
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const totalDue = {{ $session->total_amount_rupees }};

    function addVariantToCart(sku) {
        document.getElementById('barcodeInput').value = sku;
        document.getElementById('scanForm').submit();
    }

    function updateQty(variantId, newQty) {
        if (newQty < 0) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pos/items/${variantId}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="session_id" value="{{ $session->id }}">
            <input type="hidden" name="quantity" value="${newQty}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function removeItem(variantId) {
        if (!confirm('Remove item from bill?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pos/items/${variantId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="session_id" value="{{ $session->id }}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function togglePayMode(mode) {
        document.getElementById('cashTenderBox').classList.add('d-none');
        document.getElementById('upiBox').classList.add('d-none');
        document.getElementById('walletBox').classList.add('d-none');

        if (mode === 'cash') {
            document.getElementById('cashTenderBox').classList.remove('d-none');
        } else if (mode === 'upi') {
            document.getElementById('upiBox').classList.remove('d-none');
        } else if (mode === 'wallet') {
            document.getElementById('walletBox').classList.remove('d-none');
        }
    }

    function calculateChange() {
        const received = parseFloat(document.getElementById('cashReceivedInput').value) || 0;
        const change = Math.max(0, received - totalDue);
        document.getElementById('changeReturnDisplay').innerText = `₹${change.toFixed(2)}`;
    }

    function setQuickTender(amount) {
        document.getElementById('cashReceivedInput').value = amount;
        calculateChange();
    }

    function setExactTender() {
        document.getElementById('cashReceivedInput').value = totalDue;
        calculateChange();
    }

    // Customer lookup
    document.getElementById('searchCustomerBtn').addEventListener('click', function() {
        const query = document.getElementById('customerSearchInput').value.trim();
        if (!query) return;

        fetch(`/pos/customer-lookup?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.found) {
                    const c = data.customer;
                    document.getElementById('customerFoundBadge').className = 'badge bg-success';
                    document.getElementById('customerFoundBadge').innerText = 'Verified Customer';
                    document.getElementById('customerDetailsBox').classList.remove('d-none');
                    document.getElementById('custNameText').innerText = c.name;
                    document.getElementById('custIdText').innerText = c.customer_id;
                    document.getElementById('custVillageText').innerText = c.village_name || '';
                    document.getElementById('custWalletText').innerText = `₹${c.wallet_balance_rupees.toFixed(2)}`;
                    document.getElementById('walletBoxBalanceText').innerText = `₹${c.wallet_balance_rupees.toFixed(2)}`;
                    document.getElementById('checkoutCustomerIdentifier').value = c.customer_id;
                } else {
                    alert('Customer not found for identifier.');
                }
            })
            .catch(err => console.error(err));
    });
</script>
@endsection

