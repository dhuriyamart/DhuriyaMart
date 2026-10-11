@extends('layouts.app')

@section('title', 'My Account - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3 p-2 mb-3 border-bottom pb-3">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                        <div class="small text-muted">{{ $user->email }}</div>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column gap-1">
                    <li class="nav-item">
                        <a href="{{ route('account.dashboard') }}" class="nav-link active rounded-pill fw-semibold">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.orders') }}" class="nav-link text-dark rounded-pill">
                            <i class="bi bi-box-seam me-2 text-primary"></i> Order History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.wishlist') }}" class="nav-link text-dark rounded-pill">
                            <i class="bi bi-heart me-2 text-danger"></i> Wishlist ({{ $wishlistCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.addresses') }}" class="nav-link text-dark rounded-pill">
                            <i class="bi bi-geo-alt me-2 text-info"></i> Saved Addresses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.qr_card') }}" class="nav-link text-dark rounded-pill">
                            <i class="bi bi-qr-code-scan me-2 text-success"></i> Digital Village QR Card
                        </a>
                    </li>
                    <li class="nav-item pt-2 border-top mt-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link text-danger w-100 text-start rounded-pill border-0 bg-transparent">
                                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Dashboard Main Panel -->
        <div class="col-lg-9">
            <!-- Summary Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-bag-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-dark">{{ $totalOrdersCount }}</h3>
                                <small class="text-muted">Total Orders Placed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                <i class="bi bi-heart-fill fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-dark">{{ $wishlistCount }}</h3>
                                <small class="text-muted">Saved Wishlist Items</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark text-capitalize">{{ $user->roles->first()?->name ?? 'Customer' }}</div>
                                <small class="text-muted">Account Standing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Recent Orders</h5>
                    <a href="{{ route('account.orders') }}" class="text-primary text-decoration-none small fw-semibold">View All Orders &rarr;</a>
                </div>

                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Shops</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark">{{ $order->order_number }}</td>
                                        <td class="small text-muted">{{ $order->created_at->format('d M Y') }}</td>
                                        <td class="small">{{ $order->sellerOrders->pluck('shop.name')->implode(', ') }}</td>
                                        <td class="fw-bold text-dark">{{ $order->formatted_total }}</td>
                                        <td><x-status-badge :status="$order->status" /></td>
                                        <td>
                                            <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        <p class="mb-2">You haven't placed any orders yet.</p>
                        <a href="{{ route('home') }}" class="btn btn-sm btn-dm-primary rounded-pill px-3">Start Shopping</a>
                    </div>
                @endif
            </div>

            <!-- Default Address Card -->
            @if($defaultAddress)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Default Delivery Address</h6>
                        <a href="{{ route('account.addresses') }}" class="small text-primary text-decoration-none">Manage Addresses &rarr;</a>
                    </div>
                    <div class="small text-secondary">
                        <strong>{{ $defaultAddress->name }}</strong> ({{ $defaultAddress->phone }})<br>
                        {{ $defaultAddress->formatted_address }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

