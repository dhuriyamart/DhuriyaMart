@extends('layouts.app')

@section('title', 'Order Placed - DhuriyaMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center bg-white mb-4">
                <div class="mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-3">
                        <i class="bi bi-check-circle-fill display-4"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-1">Thank you for your order!</h3>
                <p class="text-muted mb-4">Your order <strong>#{{ $order->order_number }}</strong> has been placed and received by our merchant partners.</p>

                <!-- Order Meta Card -->
                <div class="bg-light rounded-3 p-3 text-start mb-4 border">
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Order Reference:</span>
                            <span class="fw-bold font-monospace">{{ $order->order_number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Payment Method & Status:</span>
                            <span class="fw-semibold text-uppercase">{{ $order->payment_method }}</span> — 
                            <x-status-badge :status="$order->payment_status" />
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Order Date:</span>
                            <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Total Amount:</span>
                            <span class="fw-bold text-primary fs-6">{{ $order->formatted_total }}</span>
                        </div>
                    </div>
                </div>

                <!-- Multi-Vendor Packages Breakdown -->
                <div class="text-start mb-4">
                    <h5 class="fw-bold mb-3">Merchant Dispatch Packages</h5>
                    @foreach($order->sellerOrders as $sellerOrder)
                        <div class="border rounded-3 p-3 mb-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <i class="bi bi-shop text-primary me-2"></i>
                                    <strong>{{ $sellerOrder->shop->name }}</strong>
                                    <small class="text-muted font-monospace ms-2">({{ $sellerOrder->seller_order_number }})</small>
                                </div>
                                <x-status-badge :status="$sellerOrder->status" />
                            </div>

                            <ul class="list-unstyled mb-0 small">
                                @foreach($sellerOrder->items as $item)
                                    <li class="d-flex justify-content-between py-1">
                                        <span>{{ $item->product_name }} ({{ $item->variant_name }}) × {{ $item->quantity }}</span>
                                        <span class="fw-semibold">{{ $item->formatted_total }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <!-- Shipping Address Snapshot -->
                @if($order->shippingAddress)
                    <div class="text-start border rounded-3 p-3 mb-4 bg-light small">
                        <div class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Delivery Address:</div>
                        <div class="text-muted">
                            {{ $order->shippingAddress->name }} ({{ $order->shippingAddress->phone }})<br>
                            {{ $order->shippingAddress->formatted_address }}
                        </div>
                    </div>
                @endif

                <!-- Action CTA Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-outline-dark rounded-pill px-4">
                        <i class="bi bi-box-seam me-1"></i> Track Order in Account
                    </a>
                    <a href="{{ route('account.orders.invoice', $order->id) }}" class="btn btn-outline-secondary rounded-pill px-4" target="_blank">
                        <i class="bi bi-printer me-1"></i> View Tax Invoice
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-dm-primary rounded-pill px-4 fw-bold">
                        Continue Shopping
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

