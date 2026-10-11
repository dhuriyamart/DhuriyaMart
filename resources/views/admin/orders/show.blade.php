@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number . ' - DhuriyaMart Admin')
@section('page_title', 'Order Inspection: #' . $order->order_number)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Order #{{ $order->order_number }}</h4>
            <span class="text-muted small">Placed on {{ $order->created_at->format('d M Y, h:i A') }} by <strong>{{ $order->user->name }}</strong></span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            &larr; Back to Orders
        </a>
    </div>

    <div class="row g-4">
        <!-- Main Column -->
        <div class="col-lg-8">
            <!-- Merchant Packages Breakdown -->
            @foreach($order->sellerOrders as $sellerOrder)
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                    <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-shop text-primary me-2"></i>
                            <strong>{{ $sellerOrder->shop->name }}</strong>
                            <code class="ms-2">({{ $sellerOrder->seller_order_number }})</code>
                        </div>
                        <div>
                            <x-status-badge :status="$sellerOrder->status" />
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Item</th>
                                    <th>SKU</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Commission ({{ $sellerOrder->commission_rate }}%)</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sellerOrder->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            <small class="text-muted">{{ $item->variant_name }}</small>
                                        </td>
                                        <td><code>{{ $item->sku }}</code></td>
                                        <td class="text-center fw-semibold">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ $item->formatted_price }}</td>
                                        <td class="text-end text-danger small">- {{ \App\Support\Money::format($item->commission_amount_paise) }}</td>
                                        <td class="text-end fw-bold">{{ $item->formatted_total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($sellerOrder->shipment)
                        <div class="p-3 bg-light border-top small">
                            <i class="bi bi-truck text-primary me-2"></i>
                            <strong>Shipment:</strong> {{ $sellerOrder->shipment->courier_name }} (AWB: <code>{{ $sellerOrder->shipment->tracking_number }}</code>) &bull; Status: {{ $sellerOrder->shipment->status }}
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Order Status Audit History -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark">Order Status History & Audit Log</h5>
                <ul class="list-unstyled mb-0 small">
                    @foreach($order->statusHistories as $hist)
                        <li class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <i class="bi bi-clock-history text-muted mt-1"></i>
                            <div>
                                <span class="fw-bold text-capitalize">{{ str_replace('_', ' ', $hist->to_status) }}</span>
                                <div class="text-muted">{{ $hist->notes }}</div>
                                <small class="text-muted">{{ $hist->created_at->format('d M Y, h:i A') }} &bull; By: {{ $hist->changedBy?->name ?? 'System' }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Sidebar Summary Column -->
        <div class="col-lg-4">
            <!-- Admin Status Override Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Admin Status Action</h5>

                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Update Parent Order Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Administrative Remarks</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Reason for status change..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold">
                        Apply Status Update
                    </button>
                </form>
            </div>

            <!-- Financial Summary -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Financial Accounting</h5>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal:</span>
                    <span>{{ $order->formatted_subtotal }}</span>
                </div>
                @if($order->discount_paise > 0)
                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Discount:</span>
                        <span>- {{ $order->formatted_discount }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Shipping:</span>
                    <span>{{ $order->formatted_shipping }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">GST:</span>
                    <span>{{ $order->formatted_tax }}</span>
                </div>
                <div class="border-top pt-2 d-flex justify-content-between align-items-baseline">
                    <span class="fw-bold">Total Amount:</span>
                    <span class="fs-5 fw-bold text-primary">{{ $order->formatted_total }}</span>
                </div>
            </div>

            <!-- Customer Shipping Address -->
            @if($order->shippingAddress)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-2 border-bottom pb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Delivery Address</h6>
                    <div class="small text-secondary">
                        <strong>{{ $order->shippingAddress->name }}</strong><br>
                        Phone: {{ $order->shippingAddress->phone }}<br>
                        {{ $order->shippingAddress->formatted_address }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

