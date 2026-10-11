@extends('layouts.seller')

@section('title', 'Ledger & Settlements - ' . $shop->name)
@section('page_title', 'Ledger Accounting & Settlements')

@section('content')
<div class="container-fluid px-0">
    <!-- Current Ledger Balance Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <span class="text-muted small text-uppercase fw-bold d-block">Current Ledger Balance</span>
                <h2 class="display-6 fw-bold text-success mb-0">{{ \App\Support\Money::format($currentBalancePaise) }}</h2>
                <small class="text-muted">Net cumulative balance ready for periodic bank settlement.</small>
            </div>
            <div class="bg-light p-3 rounded-3 border small">
                <div><strong>Bank Account:</strong> {{ $shop->profile?->bank_name ?? 'Not configured' }}</div>
                <div><strong>Account No:</strong> <code>{{ $shop->profile?->bank_account_number ?? 'N/A' }}</code> ({{ $shop->profile?->bank_ifsc ?? '' }})</div>
            </div>
        </div>
    </div>

    <!-- Settlement Statements Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Settlement Statements</h5>
        </div>

        @if($settlements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Settlement #</th>
                            <th>Total Sales</th>
                            <th>Commission</th>
                            <th>Net Payout</th>
                            <th>Status</th>
                            <th>Payment Reference</th>
                            <th>Settled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settlements as $settlement)
                            <tr>
                                <td class="font-monospace fw-bold text-dark">{{ $settlement->settlement_number }}</td>
                                <td>{{ $settlement->formatted_total_sales }}</td>
                                <td class="text-danger small">- {{ $settlement->formatted_commission_deducted }}</td>
                                <td class="fw-bold text-success">{{ $settlement->formatted_net_payout }}</td>
                                <td><x-status-badge :status="$settlement->status" /></td>
                                <td>
                                    <code class="text-dark">{{ $settlement->payment_reference ?? 'Pending' }}</code>
                                </td>
                                <td class="small text-muted">{{ $settlement->paid_at ? $settlement->paid_at->format('d M Y') : 'Processing' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                No settlement payout statements generated yet.
            </div>
        @endif
    </div>

    <!-- Live Ledger Transactions Audit Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Live Ledger Journal Entries</h5>
        </div>

        @if($ledgerEntries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledgerEntries as $entry)
                            <tr>
                                <td class="small text-muted">{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $entry->type === 'credit' ? 'bg-success' : 'bg-danger' }} text-uppercase">
                                        {{ $entry->type }}
                                    </span>
                                </td>
                                <td class="small">{{ $entry->description }}</td>
                                <td class="text-end fw-bold {{ $entry->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $entry->type === 'credit' ? '+' : '-' }}{{ $entry->formatted_amount }}
                                </td>
                                <td class="text-end fw-bold text-dark">{{ $entry->formatted_balance_after }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                No ledger journal transactions recorded yet.
            </div>
        @endif
    </div>
</div>
@endsection

