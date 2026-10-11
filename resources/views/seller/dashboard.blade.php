@extends('layouts.seller')

@section('title', 'Seller Dashboard - ' . $shop->name)
@section('page_title', 'Seller Overview - ' . $shop->name)

@section('content')
<div class="container-fluid px-0">

    @if($shop->status === 'pending')
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center">
            <i class="bi bi-clock-history fs-3 me-3 text-warning"></i>
            <div>
                <strong class="d-block">Your Merchant Profile is Pending Verification</strong>
                <span class="small">Our moderation team is reviewing your shop documents. You can still review your profile and prepare product data.</span>
            </div>
        </div>
    @endif

    <!-- Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Gross Sales</span>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                        <i class="bi bi-currency-rupee fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ \App\Support\Money::format($grossSalesPaise) }}</h3>
                <small class="text-muted">{{ $totalOrdersCount }} orders placed</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Net Earnings</span>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                        <i class="bi bi-wallet-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-success mb-1">{{ \App\Support\Money::format($netEarningsPaise) }}</h3>
                <small class="text-muted">Ledger Balance: <strong>{{ \App\Support\Money::format($currentBalancePaise) }}</strong></small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Pending Dispatch</span>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $pendingDispatchesCount }}</h3>
                <small class="text-muted">Awaiting courier pickup</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Low Stock Warning</span>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-danger mb-1">{{ $lowStockCount }}</h3>
                <small class="text-muted">SKUs below threshold</small>
            </div>
        </div>
    </div>

    <!-- Recent Assigned Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">Recent Assigned Orders</h5>
            <a href="{{ route('seller.orders.index') }}" class="small text-primary text-decoration-none fw-semibold">View All Orders &rarr;</a>
        </div>

        @if($recentOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Sub-Order #</th>
                            <th>Parent Order</th>
                            <th>Date</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="font-monospace fw-bold text-dark">{{ $order->seller_order_number }}</td>
                                <td class="small font-monospace">{{ $order->order->order_number }}</td>
                                <td class="small text-muted">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="fw-bold text-success">{{ $order->formatted_net_seller }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td>
                                    <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View & Dispatch
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                No customer orders assigned to your shop yet.
            </div>
        @endif
    </div>

</div>
@endsection

