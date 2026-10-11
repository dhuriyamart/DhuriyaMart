@extends('layouts.app')

@section('title', 'My Orders - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Order History</h3>
            <p class="text-muted small mb-0">Track and manage your past and active marketplace orders</p>
        </div>
        <a href="{{ route('account.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            &larr; Back to Dashboard
        </a>
    </div>

    @if($orders->count() > 0)
        <div class="row g-4">
            @foreach($orders as $order)
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-light py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="small text-muted d-block">Order #</span>
                                <strong class="font-monospace text-dark">{{ $order->order_number }}</strong>
                            </div>
                            <div>
                                <span class="small text-muted d-block">Order Placed</span>
                                <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div>
                                <span class="small text-muted d-block">Total Amount</span>
                                <strong class="text-dark">{{ $order->formatted_total }}</strong>
                            </div>
                            <div>
                                <span class="small text-muted d-block">Status</span>
                                <x-status-badge :status="$order->status" />
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    View Details
                                </a>
                                <a href="{{ route('account.orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" target="_blank">
                                    Invoice
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <h6 class="small fw-bold text-muted text-uppercase mb-2">Merchant Packages in this Order:</h6>
                            <div class="row g-2">
                                @foreach($order->sellerOrders as $so)
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded-3 bg-light d-flex justify-content-between align-items-center small">
                                            <div>
                                                <i class="bi bi-shop text-primary me-1"></i>
                                                <strong>{{ $so->shop->name }}</strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $so->items->count() }} item(s)</div>
                                            </div>
                                            <div>
                                                <x-status-badge :status="$so->status" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
            <h5>No orders found</h5>
            <p class="text-muted small">You haven't purchased any items yet.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-dm-primary rounded-pill px-4">Browse Marketplace</a>
            </div>
        </div>
    @endif
</div>
@endsection

