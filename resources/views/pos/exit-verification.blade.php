@extends('layouts.app')

@section('title', 'Dual Exit Verification Gate — ' . $store->name)

@section('content')
<div class="container-fluid px-3 py-4" style="background-color: #f8fafc; min-height: 90vh;">
    <!-- Top Header -->
    <div class="bg-white rounded-3 shadow-sm p-3 mb-4 border d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-shield-check fs-3"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">निकास सत्यापन गेट (Dual Exit Verification Gate)</h4>
                <span class="text-muted small">
                    <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $store->name }} | Dual Mode: Staff Check (Mode A) + Automated Barcode Scanner Gate (Mode B)
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pos.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="bi bi-calculator me-1"></i> Staff POS
            </a>
            <a href="{{ route('pos.kiosk') }}" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="bi bi-display me-1"></i> Self Kiosk
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fs-6" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show fs-6" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show fs-6" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- LEFT: Mode Selector & Verification Workspace -->
        <div class="col-lg-7">
            <!-- Search Bill / Order Number -->
            <div class="bg-white rounded-3 shadow-sm p-4 border mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-upc-scan text-primary me-2"></i>1. ग्राहक बिल / रसीद नंबर दर्ज करें (Scan Receipt)</h5>
                <form action="{{ route('pos.exit-verification') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="order_number" class="form-control form-control-lg fw-bold" 
                           placeholder="Enter Order Number (e.g. DM-POS-10001)..." 
                           value="{{ request('order_number') }}" autofocus required>
                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold">
                        <i class="bi bi-search me-1"></i> लोड करें (Load)
                    </button>
                </form>
            </div>

            @if($selectedOrder)
                <!-- Bill Details Loaded -->
                <div class="bg-white rounded-3 shadow-sm p-4 border mb-4">
                    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                        <div>
                            <span class="badge bg-primary fs-6">{{ $selectedOrder->order_number }}</span>
                            <span class="text-muted ms-2 small">{{ $selectedOrder->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div>
                            <span class="badge bg-success fs-6">Paid: ₹{{ number_format($selectedOrder->total_amount_paise / 100, 2) }}</span>
                            <span class="text-muted small ms-2">({{ $selectedOrder->payment_method }})</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong class="text-dark">Customer:</strong> {{ $selectedOrder->user->name ?? 'Walk-in' }} 
                        @if($selectedOrder->user?->phone)
                            <span class="text-muted small">({{ $selectedOrder->user->phone }})</span>
                        @endif
                    </div>

                    <!-- Mode Selection Tabs -->
                    <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3" id="verificationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="modeA-tab" data-bs-toggle="tab" data-bs-target="#modeA" type="button" role="tab">
                                <i class="bi bi-person-check me-2"></i>Mode A: कर्मचारी भौतिक जाँच (Staff Check)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="modeB-tab" data-bs-toggle="tab" data-bs-target="#modeB" type="button" role="tab">
                                <i class="bi bi-cpu me-2"></i>Mode B: स्वचालित बारकोड गेट (Automated Scanner)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="verificationTabsContent">
                        <!-- MODE A: STAFF PHYSICAL VERIFICATION -->
                        <div class="tab-pane fade show active" id="modeA" role="tabpanel">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                कर्मचारी ग्राहक की टोकरी के सामान की गिनती बिल के सामान से करें। सही होने पर 'सत्यापन पास' पर क्लिक करें।
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>SKU / Barcode</th>
                                            <th class="text-center">Billed Qty</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedOrder->items as $item)
                                            <tr>
                                                <td class="fw-bold">{{ $item->product_name }} - {{ $item->variant_name }}</td>
                                                <td><code>{{ $item->sku }}</code></td>
                                                <td class="text-center fs-6 fw-bold text-primary">{{ $item->quantity }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-success"><i class="bi bi-check"></i> On Bill</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('pos.exit-verification.staff') }}" method="POST">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">टिप्पणी / Notes (Optional)</label>
                                    <input type="text" name="notes" class="form-control" placeholder="भौतिक जाँच सही पाई गई...">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="pass" class="btn btn-success btn-lg flex-grow-1 fw-bold">
                                        <i class="bi bi-check-circle-fill me-2"></i> 100% सही - सत्यापन पास करें (Pass Verification)
                                    </button>
                                    <button type="submit" name="action" value="discrepancy" class="btn btn-outline-danger btn-lg fw-bold" onclick="return confirm('क्या टोकरी में अंतर है?');">
                                        <i class="bi bi-exclamation-octagon-fill me-1"></i> अंतर दर्ज करें (Flag Discrepancy)
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- MODE B: AUTOMATED BARCODE SCANNING GATE -->
                        <div class="tab-pane fade" id="modeB" role="tabpanel">
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-shield-lock-fill me-1"></i>
                                गेट स्कैनर पर सामान को एक-एक करके स्कैन करें। सिस्टम स्वतः बिल से मिलान करके गेट खोलेगा।
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">गेट स्कैनर इनपुट (Scan Barcodes Here)</label>
                                <div class="input-group">
                                    <input type="text" id="gateBarcodeInput" class="form-control form-control-lg fw-bold" 
                                           placeholder="Scan barcode of basket item...">
                                    <button type="button" class="btn btn-dark" onclick="addScannedBarcode()">
                                        <i class="bi bi-plus-lg"></i> Scan
                                    </button>
                                </div>
                            </div>

                            <!-- List of Scanned Barcodes -->
                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-dark small">स्कैन किए गए सामान (Scanned Items):</strong>
                                    <span id="scannedCountBadge" class="badge bg-secondary">0 Scanned</span>
                                </div>
                                <div id="scannedListContainer" class="d-flex flex-wrap gap-2 mb-2" style="min-height: 40px;">
                                    <span class="text-muted small" id="noScanPlaceholder">कोई सामान स्कैन नहीं हुआ।</span>
                                </div>
                            </div>

                            <!-- Automated Verification Result Display -->
                            <div id="autoGateResultBox" class="p-3 rounded-3 text-center d-none mb-3">
                                <h4 id="autoResultHeading" class="fw-bold mb-1"></h4>
                                <p id="autoResultMessage" class="small mb-0"></p>
                            </div>

                            <button type="button" class="btn btn-primary btn-lg w-100 fw-bold" onclick="runAutomatedCheck()">
                                <i class="bi bi-cpu-fill me-2"></i> ऑटोमेटिक मिलान जाँचें (Verify &amp; Unlock Gate)
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3 shadow-sm p-5 border text-center text-muted">
                    <i class="bi bi-upc-scan fs-1 d-block mb-3 text-secondary"></i>
                    <h5 class="fw-bold">सत्यापन हेतु बिल लोड करें</h5>
                    <p class="small mb-0">ऊपर रसीद नंबर दर्ज करें या बारकोड स्कैनर से रसीद स्कैन करें।</p>
                </div>
            @endif
        </div>

        <!-- RIGHT: Recent Verifications Log -->
        <div class="col-lg-5">
            <div class="bg-white rounded-3 shadow-sm p-4 border h-100">
                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>हाल के सत्यापन रिकॉर्ड (Verification Log)</h5>
                    <span class="badge bg-light text-muted border">{{ $recentVerifications->count() }} records</span>
                </div>

                <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                    @forelse($recentVerifications as $log)
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <strong class="text-dark">{{ $log->order->order_number ?? 'Order' }}</strong>
                                    <span class="badge {{ $log->isPassed() ? 'bg-success' : 'bg-danger' }} ms-1">
                                        {{ $log->isPassed() ? 'PASSED' : 'DISCREPANCY' }}
                                    </span>
                                </div>
                                <span class="badge bg-light text-dark border small">{{ $log->mode === 'automated' ? 'Mode B (Auto)' : 'Mode A (Staff)' }}</span>
                            </div>
                            <div class="small text-muted mb-1">
                                <span>Billed: <strong>{{ $log->total_billed_items }}</strong> items</span> | 
                                <span>Checked: <strong>{{ $log->total_scanned_items }}</strong> items</span>
                            </div>
                            @if($log->notes)
                                <div class="small text-secondary fst-italic">{{ $log->notes }}</div>
                            @endif
                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i>{{ $log->verified_at ? $log->verified_at->diffForHumans() : $log->created_at->diffForHumans() }}
                                @if($log->verifier)
                                    | By: {{ $log->verifier->name }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No verifications recorded yet today.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let scannedBarcodes = [];

    function addScannedBarcode() {
        const input = document.getElementById('gateBarcodeInput');
        const code = input.value.trim();
        if (!code) return;

        scannedBarcodes.push(code);
        input.value = '';
        input.focus();

        updateScannedDisplay();
    }

    document.getElementById('gateBarcodeInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addScannedBarcode();
        }
    });

    function updateScannedDisplay() {
        const container = document.getElementById('scannedListContainer');
        const countBadge = document.getElementById('scannedCountBadge');
        countBadge.innerText = `${scannedBarcodes.length} Scanned`;

        if (scannedBarcodes.length === 0) {
            container.innerHTML = `<span class="text-muted small">कोई सामान स्कैन नहीं हुआ।</span>`;
            return;
        }

        container.innerHTML = scannedBarcodes.map((bc, idx) => 
            `<span class="badge bg-secondary p-2">${bc} <i class="bi bi-x-circle ms-1" style="cursor:pointer;" onclick="removeScannedBarcode(${idx})"></i></span>`
        ).join('');
    }

    function removeScannedBarcode(index) {
        scannedBarcodes.splice(index, 1);
        updateScannedDisplay();
    }

    function runAutomatedCheck() {
        @if($selectedOrder)
            const orderId = {{ $selectedOrder->id }};
            if (scannedBarcodes.length === 0) {
                alert('कृपया पहले गेट स्कैनर से सामान स्कैन करें।');
                return;
            }

            fetch("{{ route('pos.exit-verification.automated') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    order_id: orderId,
                    scanned_barcodes: scannedBarcodes
                })
            })
            .then(res => res.json())
            .then(data => {
                const resultBox = document.getElementById('autoGateResultBox');
                const heading = document.getElementById('autoResultHeading');
                const msg = document.getElementById('autoResultMessage');

                resultBox.classList.remove('d-none');
                if (data.passed) {
                    resultBox.className = 'p-4 rounded-3 text-center mb-3 bg-success text-white shadow';
                    heading.innerHTML = '<i class="bi bi-unlock-fill me-2"></i> गेट अनलॉक - सत्यापन सफल!';
                    msg.innerText = 'बिल और सामान 100% मेल खाते हैं। ग्राहक जा सकते हैं।';
                } else {
                    resultBox.className = 'p-4 rounded-3 text-center mb-3 bg-danger text-white shadow';
                    heading.innerHTML = '<i class="bi bi-lock-fill me-2"></i> गेट लॉक - अंतर पाया गया!';
                    msg.innerText = data.notes || 'बिल के सामान और स्कैन किए गए सामान में अंतर है। कृपया कर्मचारी से संपर्क करें।';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Verification request failed.');
            });
        @endif
    }
</script>
@endsection

