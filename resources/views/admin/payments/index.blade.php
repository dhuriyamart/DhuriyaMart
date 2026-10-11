@extends('layouts.admin')

@section('title', 'Payments & Reconciliation - DhuriyaMart Admin')
@section('page_title', 'Payment Transactions, Refunds & Gateway Webhooks')

@section('content')
<div class="container-fluid px-0">
    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Payment Transactions</h5>
            <span class="small text-muted">Gateway Records</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Date</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Amount</th>
                        <th>Gateway Txn ID</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="small text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <strong class="font-monospace text-dark">{{ $payment->order?->order_number ?? 'N/A' }}</strong>
                            </td>
                            <td class="small">{{ $payment->order?->user?->name ?? 'Guest' }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $payment->provider }}</span></td>
                            <td class="fw-bold">{{ $payment->formatted_amount }}</td>
                            <td>
                                <code class="small text-dark">{{ $payment->transaction_id ?? $payment->gateway_order_id }}</code>
                            </td>
                            <td><x-status-badge :status="$payment->status" /></td>
                            <td class="text-end">
                                @if($payment->isSuccessful())
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#refundModal_{{ $payment->id }}">
                                        Issue Refund
                                    </button>
                                @endif

                                <!-- Refund Modal -->
                                <div class="modal fade text-start" id="refundModal_{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.payments.refund', $payment->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Issue Refund for Order #{{ $payment->order->order_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Refund Amount (₹ INR) *</label>
                                                        <input type="number" step="0.01" name="amount" class="form-control form-control-sm" value="{{ $payment->amount_paise / 100 }}" max="{{ $payment->amount_paise / 100 }}" required>
                                                        <small class="text-muted">Maximum refundable: {{ $payment->formatted_amount }}</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Reason for Refund *</label>
                                                        <textarea name="reason" rows="2" class="form-control form-control-sm" required placeholder="Return accepted / damaged delivery / customer dispute"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4">Process Refund</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No payment transactions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-center">
            {{ $payments->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Webhook Events Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Gateway Webhooks & Idempotent Event Log</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Processed At</th>
                        <th>Event ID (Idempotency Key)</th>
                        <th>Event Type</th>
                        <th>Provider</th>
                        <th>Payment ID</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $ev)
                        <tr>
                            <td class="small text-muted">{{ $ev->processed_at?->format('d M Y, h:i A') }}</td>
                            <td><code>{{ $ev->event_id ?? 'N/A' }}</code></td>
                            <td><span class="badge bg-light text-primary border font-monospace">{{ $ev->event_type }}</span></td>
                            <td class="text-uppercase small">{{ $ev->provider }}</td>
                            <td>#{{ $ev->payment_id ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted small">No external webhooks received yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

