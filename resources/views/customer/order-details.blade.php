@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - DhuriyaMart')

@section('content')
<div class="container pb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Order #{{ $order->order_number }}</h3>
            <span class="text-muted small">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('account.orders.invoice', $order->id) }}" class="btn btn-outline-dark rounded-pill px-3" target="_blank">
                <i class="bi bi-printer me-1"></i> Tax Invoice
            </a>
            @if($order->canBeCancelled())
                <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i> Cancel Order
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Order Packages Column -->
        <div class="col-lg-8">
            @foreach($order->sellerOrders as $sellerOrder)
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                    <div class="card-header bg-light py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <i class="bi bi-shop text-primary me-2 fs-5"></i>
                            <strong>{{ $sellerOrder->shop->name }}</strong>
                            <span class="small text-muted font-monospace ms-2">({{ $sellerOrder->seller_order_number }})</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <x-status-badge :status="$sellerOrder->status" />
                            @if(in_array($sellerOrder->status, ['delivered', 'completed']))
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#returnModal_{{ $sellerOrder->id }}">
                                    Return Item
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <!-- Line Items -->
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th>Product Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sellerOrder->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                                <small class="text-muted">Variant: {{ $item->variant_name }} | SKU: <code>{{ $item->sku }}</code></small>
                                            </td>
                                            <td class="text-center fw-semibold">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ $item->formatted_price }}</td>
                                            <td class="text-end fw-bold">{{ $item->formatted_total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Shipment Tracking Card -->
                        @if($sellerOrder->shipment)
                            <div class="p-3 bg-light border-top small">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-truck text-primary fs-5 me-2"></i>
                                    <strong>Shipment Tracking:</strong>
                                    <span class="badge bg-info text-dark ms-2 text-capitalize">{{ $sellerOrder->shipment->status }}</span>
                                </div>
                                <div class="text-muted">
                                    Courier Partner: <strong>{{ $sellerOrder->shipment->courier_name }}</strong> | 
                                    AWB / Tracking Number: <code class="text-dark fw-bold">{{ $sellerOrder->shipment->tracking_number ?? 'In Processing' }}</code>
                                </div>
                                @if($sellerOrder->shipment->dispatched_at)
                                    <div class="text-muted" style="font-size: 0.75rem;">Dispatched on {{ $sellerOrder->shipment->dispatched_at->format('d M Y, h:i A') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Return Modal for this seller order -->
                <div class="modal fade" id="returnModal_{{ $sellerOrder->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('account.orders.return', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="seller_order_id" value="{{ $sellerOrder->id }}">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Request Return ({{ $sellerOrder->shop->name }})</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Select Items to Return:</label>
                                        @foreach($sellerOrder->items as $item)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="order_item_id[]" value="{{ $item->id }}" id="ret_item_{{ $item->id }}" checked>
                                                <label class="form-check-label small" for="ret_item_{{ $item->id }}">
                                                    {{ $item->product_name }} (Qty: {{ $item->quantity }})
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Reason for Return *</label>
                                        <select name="reason" class="form-select form-select-sm" required>
                                            <option value="Defective or damaged product">Defective or damaged product</option>
                                            <option value="Item does not match catalog specifications">Item does not match catalog specifications</option>
                                            <option value="Wrong item or size received">Wrong item or size received</option>
                                            <option value="Quality not satisfactory">Quality not satisfactory</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Additional Comments (Optional)</label>
                                        <textarea name="customer_note" rows="2" class="form-control form-control-sm" placeholder="Please provide specific details for the merchant"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">Submit Return Request</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach

            <!-- Order Timeline Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3">Order Status History</h5>
                <ul class="list-unstyled mb-0">
                    @foreach($order->statusHistories as $hist)
                        <li class="d-flex align-items-start gap-3 mb-2 pb-2 border-bottom">
                            <i class="bi bi-clock-history text-muted mt-1"></i>
                            <div>
                                <span class="fw-bold small text-capitalize">{{ str_replace('_', ' ', $hist->to_status) }}</span>
                                <div class="text-muted small">{{ $hist->notes }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $hist->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Sidebar Summary Column -->
        <div class="col-lg-4">
            <!-- Payment & Financials -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Payment Details</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Payment Status:</span>
                    <x-status-badge :status="$order->payment_status" />
                </div>
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
                    <span>{{ $order->shipping_fee_paise == 0 ? 'FREE' : $order->formatted_shipping }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-muted">GST Tax:</span>
                    <span>{{ $order->formatted_tax }}</span>
                </div>
                <div class="border-top pt-2 d-flex justify-content-between align-items-baseline">
                    <span class="fw-bold">Total Paid:</span>
                    <span class="fs-5 fw-bold text-primary">{{ $order->formatted_total }}</span>
                </div>
            </div>

            <!-- Shipping Address Card -->
            @if($order->shippingAddress)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="fw-bold mb-2 border-bottom pb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Shipping Address</h6>
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

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('account.orders.cancel', $order->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Cancel Order #{{ $order->order_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Are you sure you want to cancel this order? All allocated quantities will be returned to merchant stock.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cancellation Reason *</label>
                        <select name="reason" class="form-select form-select-sm" required>
                            <option value="Changed mind / Ordered by mistake">Changed mind / Ordered by mistake</option>
                            <option value="Found cheaper alternative elsewhere">Found cheaper alternative elsewhere</option>
                            <option value="Shipping time too long">Shipping time too long</option>
                            <option value="Incorrect shipping address provided">Incorrect shipping address provided</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">Confirm Cancellation</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

