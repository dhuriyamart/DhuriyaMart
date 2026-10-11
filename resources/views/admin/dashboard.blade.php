@extends('layouts.admin')

@section('title', 'Admin Dashboard - DhuriyaMart')
@section('page_title', 'Marketplace Operational Command Center')

@section('content')
<div class="container-fluid px-0">

    <!-- Village Smart Mart: True Financial Reconciliation (Revenue, COGS, Expenses, Net Profit) -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-start border-4 border-success">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shop text-success me-2"></i>ग्रामीण स्मार्ट मार्ट वित्तीय स्थिति (Village Smart Mart Accounting)
                </h5>
                <span class="text-muted small">
                    True profit accounting: Gross Revenue &minus; Cost of Goods Sold (COGS) &minus; Store Operating Expenses = Net Profit.
                </span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.cash-desk.index') }}" class="btn btn-outline-success btn-sm fw-bold">
                    <i class="bi bi-cash-stack me-1"></i> Cash Desk
                </a>
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="bi bi-cart-check me-1"></i> Auto POs
                </a>
                <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm fw-bold" target="_blank">
                    <i class="bi bi-calculator me-1"></i> Staff POS
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-2">
                <div class="bg-light p-3 rounded-3 text-center border">
                    <span class="text-muted small text-uppercase fw-bold">विक्रय राजस्व (Revenue)</span>
                    <h4 class="fw-bold text-dark mb-0 mt-1">{{ \App\Support\Money::format($grossRevenuePaise) }}</h4>
                    <span class="small text-muted">Paid Orders</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light p-3 rounded-3 text-center border">
                    <span class="text-muted small text-uppercase fw-bold">क्रय लागत (COGS)</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1">&minus;{{ \App\Support\Money::format($cogsPaise) }}</h4>
                    <span class="small text-muted">Wholesale Inventory Cost</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light p-3 rounded-3 text-center border">
                    <span class="text-muted small text-uppercase fw-bold">सकल लाभ (Gross Profit)</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1">{{ \App\Support\Money::format($grossProfitPaise) }}</h4>
                    <span class="small text-muted">Margin: {{ $grossRevenuePaise > 0 ? round(($grossProfitPaise / $grossRevenuePaise) * 100, 1) : 0 }}%</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-light p-3 rounded-3 text-center border">
                    <span class="text-muted small text-uppercase fw-bold">दुकान खर्च (Expenses)</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1">&minus;{{ \App\Support\Money::format($operatingExpensesPaise) }}</h4>
                    <span class="small text-muted">Rent, Power, Petty</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 text-center text-white border {{ $netProfitPaise >= 0 ? 'bg-success' : 'bg-danger' }}">
                    <span class="small text-uppercase fw-bold text-white-50">शुद्ध लाभ (Net Profit)</span>
                    <h3 class="fw-extrabold text-white mb-0 mt-1">{{ \App\Support\Money::format($netProfitPaise) }}</h3>
                    <span class="small text-white-50">Actual Retained Earnings</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketplace KPI Grid -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Gross Platform Sales</span>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                        <i class="bi bi-currency-rupee fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ \App\Support\Money::format($grossSalesPaise) }}</h3>
                <small class="text-muted">Collected: <strong>{{ \App\Support\Money::format($collectedPaymentsPaise) }}</strong></small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Marketplace Orders</span>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $totalOrdersCount }}</h3>
                <small class="text-muted">{{ $pendingOrdersCount }} Pending | {{ $completedOrdersCount }} Completed</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Merchants / Shops</span>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                        <i class="bi bi-shop-window fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $approvedSellersCount }}</h3>
                <small class="text-danger fw-semibold">{{ $pendingSellersCount }} Pending Verification</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Catalogue Items</span>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-2">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $totalProductsCount }}</h3>
                <small class="text-warning fw-semibold">{{ $pendingModerationProductsCount }} In Moderation Queue</small>
            </div>
        </div>
    </div>

    <!-- Second Row: Recent Orders & Quick Actions -->
    <div class="row g-4 mb-4">
        <!-- Recent Orders Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Recent Customer Orders</h5>
                    <a href="{{ route('admin.orders.index') }}" class="small text-primary text-decoration-none fw-semibold">View All &rarr;</a>
                </div>

                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="text-end">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark">{{ $order->order_number }}</td>
                                        <td class="small">
                                            <strong>{{ $order->user->name }}</strong><br>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $order->user->email }}</span>
                                        </td>
                                        <td class="fw-bold">{{ $order->formatted_total }}</td>
                                        <td><x-status-badge :status="$order->payment_status" /></td>
                                        <td><x-status-badge :status="$order->status" /></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Inspect
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        No orders recorded in database yet.
                    </div>
                @endif
            </div>
        </div>

        <!-- Registered Sellers Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Merchant Shops</h5>
                    <a href="{{ route('admin.sellers.index') }}" class="small text-primary text-decoration-none fw-semibold">Manage &rarr;</a>
                </div>

                <ul class="list-unstyled mb-0">
                    @foreach($recentSellers as $s)
                        <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <div class="fw-bold small text-dark">{{ $s->name }}</div>
                                <span class="small text-muted">{{ $s->user->email }}</span>
                            </div>
                            <div>
                                <x-status-badge :status="$s->status" />
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if($lowStockVariantsCount > 0)
                    <div class="alert alert-danger p-3 rounded-3 mt-4 mb-0 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Low Stock Alert:</strong> {{ $lowStockVariantsCount }} variant(s) have depleted stock across merchants.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

