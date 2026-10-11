@extends('layouts.admin')

@section('title', 'Daily Cash Desk & Reconciliation Register')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>दैनिक नकद काउंटर एवं मिलान रजिस्टर (Cash Desk &amp; Reconciliation)</h4>
        <span class="text-muted small">
            Store: <strong>{{ $store->name }}</strong> ({{ $store->village_name }}) | Date: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
        </span>
    </div>
    <div class="d-flex gap-2">
        @if(!$activeRegister)
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#openDrawerModal">
                <i class="bi bi-box-arrow-in-down me-1"></i> सुबह का नकद दराज खोलें (Open Drawer)
            </button>
        @else
            <button class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#cashEntryModal">
                <i class="bi bi-plus-slash-minus me-1"></i> नकद आवक / व्यय (Cash In/Out)
            </button>
            <button class="btn btn-outline-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#customerDepositModal">
                <i class="bi bi-wallet-fill me-1"></i> ग्राहक नकद जमा (Customer Deposit)
            </button>
            <button class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#closeDrawerModal">
                <i class="bi bi-lock-fill me-1"></i> शाम का दराज बंद एवं मिलान (Close &amp; Reconcile)
            </button>
        @endif
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
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Daily Reconciliation Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-info">
            <span class="text-muted small fw-bold text-uppercase">शुरुआती नकद (Float)</span>
            <div class="fs-4 fw-extrabold text-info mt-1">₹{{ number_format($summary['opening_float_rupees'], 2) }}</div>
            <span class="small text-muted">Morning Drawer Float</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-success">
            <span class="text-muted small fw-bold text-uppercase">नकद बिक्री (Cash Sales)</span>
            <div class="fs-4 fw-extrabold text-success mt-1">₹{{ number_format($summary['cash_sales_rupees'], 2) }}</div>
            <span class="small text-muted">POS Cash Orders</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-primary">
            <span class="text-muted small fw-bold text-uppercase">डिजिटल बिक्री (UPI)</span>
            <div class="fs-4 fw-extrabold text-primary mt-1">₹{{ number_format($summary['digital_sales_rupees'], 2) }}</div>
            <span class="small text-muted">UPI / Online Revenue</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-danger">
            <span class="text-muted small fw-bold text-uppercase">खर्च / भुगतान (Cash Out)</span>
            <div class="fs-4 fw-extrabold text-danger mt-1">₹{{ number_format($summary['cash_out_rupees'], 2) }}</div>
            <span class="small text-muted">Petty &amp; Store Expenses</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-warning">
            <span class="text-muted small fw-bold text-uppercase">दराज में अनुमानित नकद</span>
            <div class="fs-4 fw-extrabold text-dark mt-1">₹{{ number_format($summary['expected_cash_rupees'], 2) }}</div>
            <span class="small text-muted">Expected Cash Total</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 {{ $summary['difference_rupees'] == 0 ? 'border-success' : 'border-danger' }}">
            <span class="text-muted small fw-bold text-uppercase">मिलान अंतर (Diff)</span>
            <div class="fs-4 fw-extrabold {{ $summary['difference_rupees'] == 0 ? 'text-success' : 'text-danger' }} mt-1">
                {{ $summary['difference_rupees'] > 0 ? '+' : '' }}₹{{ number_format($summary['difference_rupees'], 2) }}
            </div>
            <span class="badge {{ $summary['difference_rupees'] == 0 ? 'bg-success' : 'bg-danger' }}">
                {{ $summary['difference_rupees'] == 0 ? '100% Balanced' : ($summary['difference_rupees'] > 0 ? 'Cash Excess' : 'Cash Shortage') }}
            </span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Transaction Entries -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-text me-1 text-primary"></i> आज की नकद दराज प्रविष्टियां (Today's Drawer Transactions)
                </h6>
                <span class="badge bg-light text-muted border">{{ $entries->count() }} transactions</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Reason / Reference</th>
                            <th>Recorded By</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="small text-muted">{{ $entry->created_at->format('h:i A') }}</td>
                                <td>
                                    @if($entry->type === 'sale')
                                        <span class="badge bg-success">Sale Order</span>
                                    @elseif($entry->type === 'opening_float')
                                        <span class="badge bg-info">Opening Float</span>
                                    @elseif($entry->type === 'cash_in' || $entry->type === 'customer_deposit')
                                        <span class="badge bg-primary">Cash In / Deposit</span>
                                    @elseif($entry->type === 'cash_out')
                                        <span class="badge bg-danger">Cash Out (Expense)</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark">{{ $entry->reason ?? '—' }}</div>
                                    @if($entry->order)
                                        <span class="text-muted" style="font-size: 0.75rem;">Order: {{ $entry->order->order_number }}</span>
                                    @endif
                                </td>
                                <td class="small">{{ $entry->user->name ?? 'Staff' }}</td>
                                <td class="text-end fw-bold {{ in_array($entry->type, ['sale', 'cash_in', 'customer_deposit', 'opening_float']) ? 'text-success' : 'text-danger' }}">
                                    {{ in_array($entry->type, ['sale', 'cash_in', 'customer_deposit', 'opening_float']) ? '+' : '-' }}₹{{ number_format($entry->amount_rupees, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No cash transactions logged yet for this drawer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Daily Registers History -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-archive-fill me-1 text-secondary"></i> पिछले दैनिक रजिस्टर (Register Archive)
                </h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentRegisters as $reg)
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark">{{ $reg->register_date->format('d M Y') }}</strong>
                            <span class="badge {{ $reg->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                                {{ strtoupper($reg->status) }}
                            </span>
                        </div>
                        <div class="small text-muted d-flex justify-content-between">
                            <span>Cash Sales: ₹{{ number_format($reg->cash_sales_rupees, 2) }}</span>
                            <span>Expected: ₹{{ number_format($reg->expected_cash_rupees, 2) }}</span>
                        </div>
                        @if($reg->closing_actual_paise !== null)
                            <div class="small mt-1 d-flex justify-content-between">
                                <span>Counted: <strong>₹{{ number_format($reg->closing_actual_rupees, 2) }}</strong></span>
                                <span class="{{ $reg->difference_rupees == 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    Diff: {{ $reg->difference_rupees > 0 ? '+' : '' }}₹{{ number_format($reg->difference_rupees, 2) }}
                                </span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No past registers recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Open Cash Drawer -->
<div class="modal fade" id="openDrawerModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.cash-desk.open') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>सुबह का नकद दराज खोलें</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">शुरुआती नकद राशि / Opening Float (₹)</label>
                    <input type="number" step="0.01" name="opening_float_rupees" class="form-control form-control-lg fw-bold" value="5000" required>
                    <small class="text-muted">दुकान खोलने पर गल्ले में रखी जाने वाली शुरुआती नकद राशि।</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">टिप्पणी / Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Morning float verified...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                <button type="submit" class="btn btn-success fw-bold">दराज शुरू करें (Open Drawer)</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Cash In / Out Entry -->
<div class="modal fade" id="cashEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.cash-desk.entry') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="cash_register_id" value="{{ $activeRegister?->id }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-slash-minus me-2 text-primary"></i>नकद आवक या व्यय दर्ज करें</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">प्रकार (Transaction Type)</label>
                    <select name="type" class="form-select" required>
                        <option value="cash_in">नकद आवक (Cash In / Top-up)</option>
                        <option value="cash_out">नकद व्यय (Cash Out / Petty Expense / Supplier Payment)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">राशि / Amount (₹)</label>
                    <input type="number" step="0.01" name="amount_rupees" class="form-control form-control-lg fw-bold" placeholder="₹" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">कारण / विवरण (Reason)</label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g. चाय नाश्ता, पैकेजिंग बैग, बिजली बिल..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                <button type="submit" class="btn btn-primary fw-bold">दर्ज करें (Save Entry)</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Close Drawer & Reconcile -->
<div class="modal fade" id="closeDrawerModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.cash-desk.close') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="cash_register_id" value="{{ $activeRegister?->id }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock-fill me-2 text-danger"></i>शाम का दराज बंद एवं मिलान</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small mb-3">
                    सिस्टम के अनुसार दराज में <strong>₹{{ number_format($summary['expected_cash_rupees'], 2) }}</strong> होने चाहिए। भौतिक रूप से गिने गए नोट और सिक्के दर्ज करें।
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">वास्तविक गिने गए नकद (Physical Cash Counted ₹)</label>
                    <input type="number" step="0.01" name="closing_actual_rupees" class="form-control form-control-lg fw-bold text-success" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">अंतिम टिप्पणी (Closing Notes)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Evening physical cash tally completed...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                <button type="submit" class="btn btn-danger fw-bold">मिलान करें एवं दराज बंद करें (Close &amp; Reconcile)</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Customer Cash Deposit -->
<div class="modal fade" id="customerDepositModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.cash-desk.deposit') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-wallet-fill me-2 text-warning"></i>ग्राहक के स्मार्ट बटुए में नकद जमा</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">ग्राहक पहचान (Customer ID / Mobile / QR)</label>
                    <input type="text" name="customer_identifier" class="form-control" placeholder="e.g. DM-CUST-10001 or +91 9876500005" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">जमा राशि / Deposit Amount (₹)</label>
                    <input type="number" step="0.01" name="amount_rupees" class="form-control form-control-lg fw-bold" placeholder="₹" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">टिप्पणी</label>
                    <input type="text" name="notes" class="form-control" placeholder="Cash counter advance deposit...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark">नकद जमा करें (Credit Wallet)</button>
            </div>
        </form>
    </div>
</div>
@endsection

