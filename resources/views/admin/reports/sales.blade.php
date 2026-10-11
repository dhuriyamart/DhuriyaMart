@extends('layouts.admin')

@section('title', 'Sales Reports & Analytics - DhuriyaMart Admin')
@section('page_title', 'Marketplace Financial & Sales Reports')

@section('content')
<div class="container-fluid px-0">
    <!-- Financial Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-stat p-4 bg-white h-100">
                <span class="text-muted small text-uppercase fw-bold mb-1">Gross Merchandise Value (GMV)</span>
                <h3 class="fw-bold text-dark mb-1">{{ \App\Support\Money::format($totalSalesPaise) }}</h3>
                <small class="text-muted">Total customer payments across valid orders</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-4 bg-white h-100">
                <span class="text-muted small text-uppercase fw-bold mb-1">Marketplace Platform Commission</span>
                <h3 class="fw-bold text-success mb-1">{{ \App\Support\Money::format($totalCommissionPaise) }}</h3>
                <small class="text-muted">Net revenue retained by DhuriyaMart</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-4 bg-white h-100">
                <span class="text-muted small text-uppercase fw-bold mb-1">Net Seller Payout Obligations</span>
                <h3 class="fw-bold text-primary mb-1">{{ \App\Support\Money::format($totalNetSellerPayoutsPaise) }}</h3>
                <small class="text-muted">Total earnings payable to merchants</small>
            </div>
        </div>
    </div>

    <!-- Payment Channels Distribution -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3 text-dark">Payment Method Distribution</h5>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div><i class="bi bi-credit-card text-primary me-2"></i> Online Payments (Razorpay / UPI / Cards)</div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">{{ $onlineOrdersCount }} Orders</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div><i class="bi bi-cash-stack text-success me-2"></i> Cash on Delivery (COD)</div>
                    <span class="badge bg-success rounded-pill px-3 py-2 fs-6">{{ $codOrdersCount }} Orders</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3 text-dark">Fulfilment Snapshot</h5>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div><i class="bi bi-check-circle-fill text-success me-2"></i> Delivered Seller Orders</div>
                    <span class="badge bg-success rounded-pill px-3 py-2 fs-6">{{ $totalDeliveredCount }} Packages</span>
                </div>
                <div class="small text-muted mt-3">
                    <i class="bi bi-info-circle me-1"></i> Delivered packages trigger automatic ledger credit to the seller and update payout eligibility.
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant Performance Breakdown -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Merchant Sales & Commission Breakdown</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Merchant Shop</th>
                        <th>Commission Rate</th>
                        <th>Total Orders</th>
                        <th>Gross Sales</th>
                        <th>Platform Commission Earned</th>
                        <th>Ledger Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shopPerformance as $shop)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $shop->name }}</div>
                                <small class="text-muted">{{ $shop->legal_name ?? $shop->name }}</small>
                            </td>
                            <td><span class="badge bg-light text-primary border">{{ $shop->commission_rate }}%</span></td>
                            <td class="fw-semibold">{{ $shop->seller_orders_count }}</td>
                            <td class="fw-bold">{{ \App\Support\Money::format($shop->gross_sales ?? 0) }}</td>
                            <td class="fw-bold text-success">{{ \App\Support\Money::format($shop->total_commission ?? 0) }}</td>
                            <td class="fw-bold text-primary">{{ \App\Support\Money::format($shop->current_balance_paise) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

