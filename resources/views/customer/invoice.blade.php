<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - Order #{{ $order->order_number }} - DhuriyaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; }
        @media print {
            .no-print { display: none; }
            .invoice-box { border: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-light py-4">

    <div class="container text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4">
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-box shadow-sm rounded-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark">DhuriyaMart</h3>
                <small class="text-muted d-block">India's Trusted Multi-Vendor Marketplace</small>
                <small class="text-muted">GSTIN: 29ABCDE1234F1Z5 | PAN: ABCDE1234F</small>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-uppercase text-secondary mb-1">Tax Invoice</h5>
                <div><strong>Invoice #:</strong> INV-{{ $order->id }}-{{ date('Y') }}</div>
                <div><strong>Order #:</strong> {{ $order->order_number }}</div>
                <div><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase small text-muted">Billed & Shipped To:</h6>
                @if($order->shippingAddress)
                    <strong>{{ $order->shippingAddress->name }}</strong><br>
                    Phone: {{ $order->shippingAddress->phone }}<br>
                    {{ $order->shippingAddress->formatted_address }}
                @else
                    {{ $order->user->name }} ({{ $order->user->email }})
                @endif
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase small text-muted">Payment Information:</h6>
                <div><strong>Method:</strong> {{ strtoupper($order->payment_method) }}</div>
                <div><strong>Status:</strong> {{ strtoupper($order->payment_status) }}</div>
                <div><strong>Currency:</strong> INR (₹)</div>
            </div>
        </div>

        <!-- Multi-Vendor Packages & Items -->
        @foreach($order->sellerOrders as $sellerOrder)
            <div class="mb-4">
                <div class="bg-light p-2 border fw-bold small text-dark d-flex justify-content-between">
                    <span>Seller: {{ $sellerOrder->shop->name }} ({{ $sellerOrder->shop->legal_name ?? $sellerOrder->shop->name }})</span>
                    <span>Sub-Order: {{ $sellerOrder->seller_order_number }}</span>
                </div>
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>SKU</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sellerOrder->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product_name }}</strong><br>
                                    <small class="text-muted">Variant: {{ $item->variant_name }}</small>
                                </td>
                                <td><code>{{ $item->sku }}</code></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $item->formatted_price }}</td>
                                <td class="text-end">{{ $item->formatted_total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- Totals Summary Table -->
        <div class="row justify-content-end mb-4">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end fw-semibold">{{ $order->formatted_subtotal }}</td>
                    </tr>
                    @if($order->discount_paise > 0)
                        <tr class="text-success">
                            <td>Discount:</td>
                            <td class="text-end fw-semibold">- {{ $order->formatted_discount }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Shipping Fee:</td>
                        <td class="text-end fw-semibold">{{ $order->shipping_fee_paise == 0 ? 'FREE' : $order->formatted_shipping }}</td>
                    </tr>
                    <tr>
                        <td>GST (5%):</td>
                        <td class="text-end fw-semibold">{{ $order->formatted_tax }}</td>
                    </tr>
                    <tr class="border-top fs-5">
                        <td class="fw-bold">Total Amount:</td>
                        <td class="text-end fw-bold text-dark">{{ $order->formatted_total }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-top pt-3 text-center text-muted small">
            <p class="mb-1">This is a computer-generated tax invoice. No physical signature is required.</p>
            <p class="mb-0">DhuriyaMart Technologies Pvt Ltd &bull; support@dhuriyamart.com &bull; www.dhuriyamart.com</p>
        </div>
    </div>

</body>
</html>

