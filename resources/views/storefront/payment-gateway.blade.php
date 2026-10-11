@extends('layouts.app')

@section('title', 'Complete Payment - DhuriyaMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3">
                        <i class="bi bi-credit-card-2-front-fill fs-1"></i>
                    </div>
                </div>

                <h4 class="fw-bold mb-1">Pay for Order #{{ $order->order_number }}</h4>
                <p class="text-muted small mb-4">Complete your secure transaction on DhuriyaMart</p>

                <div class="bg-light p-3 rounded-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Total Payable:</span>
                        <span class="fs-4 fw-bold text-dark">{{ $order->formatted_total }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Gateway Reference:</span>
                        <span class="small font-monospace">{{ $payment->gateway_order_id }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Currency:</span>
                        <span class="small fw-semibold">INR (₹)</span>
                    </div>
                </div>

                <div class="mb-4">
                    <form action="{{ route('payments.mock', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm py-3">
                            <i class="bi bi-shield-check me-2"></i>Pay {{ $order->formatted_total }} Now
                        </button>
                    </form>
                    <div class="small text-muted text-center mt-2 d-flex align-items-center justify-content-center gap-2">
                        <span><i class="bi bi-lock-fill text-success"></i> 256-Bit SSL Encrypted</span>
                        <span>•</span>
                        <span>UPI / Debit / NetBanking</span>
                    </div>
                </div>

                <!-- Standard Payment Callback Form -->
                <form id="razorpayForm" action="{{ route('payments.verify') }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="{{ $payment->gateway_order_id }}">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                </form>

                <div class="mt-3">
                    <a href="{{ route('account.orders.show', $order->id) }}" class="text-muted small text-decoration-none">
                        Cancel and pay later from account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

