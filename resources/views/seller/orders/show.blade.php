@extends('layouts.seller')

@section('title', 'Order #' . $sellerOrder->seller_order_number . ' - ' . $shop->name)
@section('page_title', 'Order #' . $sellerOrder->seller_order_number)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Sub-Order #{{ $sellerOrder->seller_order_number }}</h4>
            <span class="text-muted small">Parent Order: <code>{{ $sellerOrder->order->order_number }}</code> | Placed on {{ $sellerOrder->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="d-flex gap-2">
            @if($sellerOrder->canBeDispatched())
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#dispatchModal">
                    <i class="bi bi-truck me-1"></i> Dispatch Order
                </button>
            @endif
            @if($sellerOrder->canBeCancelled())
                <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    Cancel Order
                </button>
            @endif
            <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                &larr; Back to Orders
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Line Items Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Items to Pack & Dispatch</h6>
                    <x-status-badge :status="$sellerOrder->status" />
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Product Item</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sellerOrder->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                        <small class="text-muted">Variant: {{ $item->variant_name }} | SKU: <code>{{ $item->sku }}</code></small>
                                    </td>
                                    <td class="text-center fw-bold fs-6">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $item->formatted_price }}</td>
                                    <td class="text-end fw-bold">{{ $item->formatted_total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shipment Card if Dispatched -->
            @if($sellerOrder->shipment)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-truck text-primary me-2"></i> Courier Dispatch Information</h6>
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Courier Partner:</span>
                            <strong class="fs-6">{{ $sellerOrder->shipment->courier_name }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Tracking / AWB Number:</span>
                            <code class="fs-6 fw-bold text-dark">{{ $sellerOrder->shipment->tracking_number ?? 'N/A' }}</code>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Dispatched On:</span>
                            <span>{{ $sellerOrder->shipment->dispatched_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Shipment Status:</span>
                            <span class="badge bg-info text-dark text-capitalize">{{ $sellerOrder->shipment->status }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status History -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold mb-3 text-dark">Sub-Order History Log</h6>
                <ul class="list-unstyled mb-0 small">
                    @foreach($sellerOrder->statusHistories as $hist)
                        <li class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <i class="bi bi-clock-history text-muted mt-1"></i>
                            <div>
                                <span class="fw-bold text-capitalize">{{ str_replace('_', ' ', $hist->to_status) }}</span>
                                <div class="text-muted">{{ $hist->notes }}</div>
                                <small class="text-muted">{{ $hist->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Sidebar Summary Column -->
        <div class="col-lg-4">
            <!-- Financial Breakdown Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Financial Accounting</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Gross Order Subtotal:</span>
                    <span class="fw-semibold">{{ $sellerOrder->formatted_subtotal }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-danger">
                    <span>Platform Commission ({{ $sellerOrder->commission_rate }}%):</span>
                    <span class="fw-bold">- {{ $sellerOrder->formatted_commission }}</span>
                </div>
                <div class="border-top pt-2 d-flex justify-content-between align-items-baseline">
                    <span class="fw-bold text-dark">Your Net Earnings:</span>
                    <span class="fs-5 fw-bold text-success">{{ $sellerOrder->formatted_net_seller }}</span>
                </div>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Net earnings are credited to your seller ledger upon confirmed delivery handover.
                </div>
            </div>

            <!-- Customer Shipping Address Card -->
            @if($sellerOrder->order->shippingAddress)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="fw-bold mb-2 border-bottom pb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Customer Shipping Address</h6>
                    <div class="small text-secondary">
                        <strong>{{ $sellerOrder->order->shippingAddress->name }}</strong><br>
                        Phone: {{ $sellerOrder->order->shippingAddress->phone }}<br>
                        {{ $sellerOrder->order->shippingAddress->formatted_address }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Dispatch Courier Modal -->
<div class="modal fade" id="dispatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('seller.orders.dispatch', $sellerOrder->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Dispatch Order #{{ $sellerOrder->seller_order_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Courier / Logistics Partner *</label>
                        <select name="courier_name" class="form-select form-select-sm" required>
                            <option value="Delhivery">Delhivery</option>
                            <option value="Blue Dart Express">Blue Dart Express</option>
                            <option value="Ekart Logistics">Ekart Logistics</option>
                            <option value="DTDC Express">DTDC Express</option>
                            <option value="India Post Speed Post">India Post Speed Post</option>
                            <option value="Shadowfax">Shadowfax</option>
                            <option value="Self Dispatch / Local Delivery">Self Dispatch / Local Delivery</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tracking AWB / Consignment Number *</label>
                        <input type="text" name="tracking_number" class="form-control form-control-sm font-monospace" placeholder="e.g. DEL-123456789IN" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Courier Tracking Web Link (Optional)</label>
                        <input type="url" name="tracking_url" class="form-control form-control-sm" placeholder="https://track.courier.com/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Dispatch Remarks (Optional)</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Packaged in tamper-proof bubble mailer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">Confirm Dispatch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('seller.orders.cancel', $sellerOrder->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Cancel Sub-Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Cancellation *</label>
                        <textarea name="reason" rows="3" class="form-control form-control-sm" placeholder="Item damaged prior to dispatch / unexpected stock depletion" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">Confirm Cancellation</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

