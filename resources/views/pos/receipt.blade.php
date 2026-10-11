<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }} — Dhuriya Mart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Courier New', Courier, monospace, monospace;
            color: #000;
        }
        .receipt-container {
            width: 360px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #666;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .receipt-body {
            font-size: 0.85rem;
        }
        .dashed-line {
            border-top: 1px dashed #666;
            margin: 10px 0;
        }
        @media print {
            body {
                background: none;
                margin: 0;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0 auto;
                width: 100%;
                max-width: 320px;
                padding: 5px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="text-center mt-3 no-print">
    <a href="{{ route('pos.index') }}" class="btn btn-primary fw-bold me-2">
        <i class="bi bi-cart-plus me-1"></i> New Sale
    </a>
    <button onclick="window.print()" class="btn btn-dark fw-bold me-2">
        <i class="bi bi-printer me-1"></i> Print Receipt
    </button>
    <a href="{{ route('pos.exit-verification', ['order_number' => $order->order_number]) }}" class="btn btn-warning text-dark fw-bold">
        <i class="bi bi-shield-check me-1"></i> Verify at Exit
    </a>
</div>

<div class="receipt-container">
    <!-- Header -->
    <div class="receipt-header">
        <h4 class="fw-bold mb-0 text-uppercase">DHURIYA MART</h4>
        <div class="small fw-bold">All-in-One Automated Village Smart Mart</div>
        <div class="small text-muted">{{ $order->store->name ?? 'Flagship Store' }}</div>
        <div class="small">{{ $order->store->village_name ?? 'Ranipur' }}, {{ $order->store->district ?? 'Mau' }} (UP)</div>
        <div class="small">Ph: {{ $order->store->phone ?? '+91 9876500010' }}</div>
    </div>

    <!-- Bill Details -->
    <div class="receipt-body">
        <div class="d-flex justify-content-between">
            <span>Bill No: <strong>{{ $order->order_number }}</strong></span>
            <span>Date: {{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Time: {{ $order->created_at->format('h:i A') }}</span>
            <span>Mode: {{ strtoupper($order->payment_method) }}</span>
        </div>
        @if($order->user)
            <div class="d-flex justify-content-between">
                <span>Customer: {{ $order->user->name }}</span>
                <span>ID: {{ $order->user->customer_id ?? 'GUEST' }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        <!-- Items Table -->
        <table class="w-100" style="font-size: 0.8rem;">
            <thead>
                <tr class="fw-bold">
                    <th style="width: 50%;">ITEM</th>
                    <th class="text-center" style="width: 15%;">QTY</th>
                    <th class="text-end" style="width: 15%;">RATE</th>
                    <th class="text-end" style="width: 20%;">AMT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="text-truncate" style="max-width: 140px;">
                            {{ $item->product_name }}
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $item->sku }}</div>
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->price_paise / 100, 2) }}</td>
                        <td class="text-end">₹{{ number_format($item->total_paise / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals -->
        <div class="d-flex justify-content-between">
            <span>Total Items:</span>
            <span>{{ $order->items->sum('quantity') }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Subtotal:</span>
            <span>₹{{ number_format($order->subtotal_paise / 100, 2) }}</span>
        </div>
        @if($order->discount_paise > 0)
            <div class="d-flex justify-content-between text-success">
                <span>Discount:</span>
                <span>-₹{{ number_format($order->discount_paise / 100, 2) }}</span>
            </div>
        @endif
        <div class="d-flex justify-content-between">
            <span>GST / Taxes:</span>
            <span>₹0.00 (Incl.)</span>
        </div>

        <div class="dashed-line"></div>

        <div class="d-flex justify-content-between fs-5 fw-bold">
            <span>GRAND TOTAL:</span>
            <span>₹{{ number_format($order->total_amount_paise / 100, 2) }}</span>
        </div>

        @if($order->posSession && $order->payment_method === 'cash')
            <div class="d-flex justify-content-between mt-2">
                <span>Cash Received:</span>
                <span>₹{{ number_format($order->posSession->cash_tendered_rupees, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
                <span>Change Returned:</span>
                <span>₹{{ number_format($order->posSession->change_returned_rupees, 2) }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        <!-- Exit Verification QR Code -->
        <div class="text-center my-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $order->order_number }}" 
                 alt="Verification QR" style="width: 90px; height: 90px;">
            <div class="small mt-1 fw-bold">निकास सत्यापन कोड (Exit QR)</div>
            <div class="text-muted" style="font-size: 0.65rem;">कृपया निकास द्वार पर यह रसीद दिखाएँ</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Footer greeting -->
        <div class="text-center" style="font-size: 0.75rem;">
            <p class="mb-1 fw-bold">धुरिया मार्ट पर पधारने के लिए धन्यवाद!</p>
            <p class="mb-0 text-muted">Thank you for shopping at Dhuriya Mart!</p>
            <p class="mb-0 text-muted" style="font-size: 0.65rem;">Goods once sold can be returned within 7 days with bill.</p>
        </div>
    </div>
</div>

</body>
</html>

