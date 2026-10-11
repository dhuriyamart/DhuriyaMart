@extends('layouts.admin')

@section('title', 'Seller Settlements - DhuriyaMart Admin')
@section('page_title', 'Merchant Ledger Balances & Payout Settlements')

@section('content')
<div class="container-fluid px-0">
    <!-- Merchant Balance & Settlement Generation -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Merchant Ledger Balances & Pending Payouts</h5>
            <span class="small text-muted">Generate batch settlement statement for delivered orders</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Pending Delivered Orders</th>
                        <th>Current Ledger Balance</th>
                        <th class="text-end">Batch Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shops as $shop)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $shop->name }}</div>
                                <small class="text-muted">{{ $shop->legal_name ?? $shop->name }}</small>
                            </td>
                            <td class="small">{{ $shop->user->name }}</td>
                            <td>
                                <span class="badge {{ $shop->pending_orders_count > 0 ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                                    {{ $shop->pending_orders_count }} Unsettled Order(s)
                                </span>
                            </td>
                            <td class="fw-bold text-success fs-6">
                                {{ \App\Support\Money::format($shop->current_balance_paise) }}
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.settlements.generate', $shop->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" {{ $shop->pending_orders_count == 0 ? 'disabled' : '' }}>
                                        Generate Settlement Batch
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Settlement Statements History Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Settlement Statements History</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Settlement #</th>
                        <th>Merchant Shop</th>
                        <th>Gross Sales</th>
                        <th>Commission Deducted</th>
                        <th>Net Payout</th>
                        <th>Status</th>
                        <th>Bank Reference</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settlements as $settlement)
                        <tr>
                            <td class="font-monospace fw-bold text-dark">{{ $settlement->settlement_number }}</td>
                            <td>{{ $settlement->shop->name }}</td>
                            <td>{{ $settlement->formatted_total_sales }}</td>
                            <td class="text-danger small">- {{ $settlement->formatted_commission_deducted }}</td>
                            <td class="fw-bold text-success">{{ $settlement->formatted_net_payout }}</td>
                            <td><x-status-badge :status="$settlement->status" /></td>
                            <td><code>{{ $settlement->payment_reference ?? 'Pending' }}</code></td>
                            <td class="text-end">
                                @if($settlement->status !== 'paid')
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#payModal_{{ $settlement->id }}">
                                        Mark as Paid
                                    </button>

                                    <!-- Mark Paid Modal -->
                                    <div class="modal fade text-start" id="payModal_{{ $settlement->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('admin.settlements.paid', $settlement->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Record Payout for {{ $settlement->settlement_number }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Net Payout Amount</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $settlement->formatted_net_payout }}" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Bank Beneficiary</label>
                                                            <div class="p-2 bg-light rounded-2 small">
                                                                {{ $settlement->shop->profile?->bank_account_name }}<br>
                                                                A/C: {{ $settlement->shop->profile?->bank_account_number }}<br>
                                                                IFSC: {{ $settlement->shop->profile?->bank_ifsc }} ({{ $settlement->shop->profile?->bank_name }})
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Bank UTR / NEFT / IMPS Reference *</label>
                                                            <input type="text" name="payment_reference" class="form-control form-control-sm font-monospace" placeholder="e.g. UTR1234567890" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-4">Confirm Settlement Paid</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="small text-muted">Settled on {{ $settlement->paid_at?->format('d M Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No settlement statements generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-center">
            {{ $settlements->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

