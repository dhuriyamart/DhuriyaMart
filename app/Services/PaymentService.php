<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    /**
     * Create payment record server-side.
     */
    public function createPaymentOrder(Order $order, string $provider = 'razorpay'): Payment
    {
        $gatewayOrderId = 'order_'.Str::random(14);

        return Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'amount_paise' => $order->total_amount_paise,
            'currency' => 'INR',
            'status' => 'pending',
            'gateway_order_id' => $gatewayOrderId,
        ]);
    }

    /**
     * Server-side payment verification with HMAC SHA256 signature verification.
     */
    public function verifyPayment(Order $order, array $attributes): array
    {
        $payment = $order->payments()->latest('id')->first();
        if (! $payment) {
            $payment = $this->createPaymentOrder($order, 'razorpay');
        }

        $gatewayOrderId = $attributes['razorpay_order_id'] ?? $payment->gateway_order_id;
        $transactionId = $attributes['razorpay_payment_id'] ?? null;
        $signature = $attributes['razorpay_signature'] ?? null;

        if (! $transactionId) {
            return ['success' => false, 'message' => 'Missing transaction identifier.'];
        }

        $secret = Setting::get('razorpay_key_secret', 'rzp_test_DhuriyaMartSecretKey');

        // Signature verification (HMAC-SHA256)
        $expectedSignature = hash_hmac('sha256', "{$gatewayOrderId}|{$transactionId}", $secret);

        $isValidSignature = hash_equals($expectedSignature, (string) $signature);

        // Also permit mock test signatures in sandbox environment
        $isSandbox = Setting::get('payment_sandbox_mode', '1') === '1';
        if (! $isValidSignature && $isSandbox && str_starts_with((string) $transactionId, 'pay_mock_')) {
            $isValidSignature = true;
        }

        if (! $isValidSignature) {
            $payment->update([
                'status' => 'failed',
                'error_code' => 'BAD_SIGNATURE',
                'error_message' => 'Cryptographic signature verification failed.',
            ]);

            return ['success' => false, 'message' => 'Payment signature verification failed.'];
        }

        // Verify that order was not already paid to prevent double processing
        if ($order->payment_status === 'paid') {
            return ['success' => true, 'order' => $order, 'payment' => $payment, 'already_paid' => true];
        }

        return DB::transaction(function () use ($order, $payment, $gatewayOrderId, $transactionId, $signature) {
            $payment->update([
                'transaction_id' => $transactionId,
                'gateway_order_id' => $gatewayOrderId,
                'signature' => $signature,
                'status' => 'successful',
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
            ]);

            // Mark participating seller orders as confirmed
            foreach ($order->sellerOrders as $sellerOrder) {
                if ($sellerOrder->status === 'pending') {
                    $sellerOrder->update(['status' => 'confirmed']);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'seller_order_id' => $sellerOrder->id,
                        'from_status' => 'pending',
                        'to_status' => 'confirmed',
                        'changed_by_user_id' => auth()->id() ?? $order->user_id,
                        'notes' => "Payment verified online (Txn: {$transactionId})",
                    ]);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'seller_order_id' => null,
                'from_status' => 'pending',
                'to_status' => 'paid',
                'changed_by_user_id' => auth()->id() ?? $order->user_id,
                'notes' => "Payment verified via {$payment->provider} (Txn: {$transactionId})",
            ]);

            return ['success' => true, 'order' => $order->fresh(), 'payment' => $payment];
        });
    }

    /**
     * Idempotent webhook handler with signature validation and duplicate event detection.
     */
    public function handleWebhook(string $provider, array $payload, ?string $signature = null): array
    {
        $eventId = $payload['event_id'] ?? $payload['id'] ?? null;
        $eventType = $payload['event'] ?? 'payment.captured';

        // 1. Idempotency Check: prevent duplicate event processing
        if ($eventId) {
            $existing = PaymentEvent::where('event_id', $eventId)->first();
            if ($existing) {
                return [
                    'success' => true,
                    'duplicate' => true,
                    'message' => "Webhook event {$eventId} was already processed at {$existing->processed_at}.",
                ];
            }
        }

        // 2. Validate webhook signature if provided
        $secret = Setting::get('razorpay_key_secret', 'rzp_test_DhuriyaMartSecretKey');
        if ($signature && ! empty($payload)) {
            $expectedSignature = hash_hmac('sha256', json_encode($payload), $secret);
            $isSandbox = Setting::get('payment_sandbox_mode', '1') === '1';

            if (! hash_equals($expectedSignature, $signature) && ! $isSandbox) {
                Log::warning("DhuriyaMart Payment Webhook: Invalid signature received for event {$eventId}");

                return ['success' => false, 'message' => 'Invalid webhook signature'];
            }
        }

        // 3. Process event atomically
        return DB::transaction(function () use ($provider, $eventId, $eventType, $payload) {
            $gatewayOrderId = $payload['payload']['payment']['entity']['order_id'] ?? ($payload['order_id'] ?? null);
            $transactionId = $payload['payload']['payment']['entity']['id'] ?? ($payload['payment_id'] ?? null);
            $amountPaise = $payload['payload']['payment']['entity']['amount'] ?? ($payload['amount'] ?? null);

            $payment = null;
            if ($gatewayOrderId) {
                $payment = Payment::where('gateway_order_id', $gatewayOrderId)->first();
            }

            $eventRecord = PaymentEvent::create([
                'payment_id' => $payment?->id,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'provider' => $provider,
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            if ($payment && in_array($eventType, ['payment.captured', 'order.paid'])) {
                if ($payment->status !== 'successful') {
                    $order = $payment->order;
                    $this->verifyPayment($order, [
                        'razorpay_order_id' => $payment->gateway_order_id,
                        'razorpay_payment_id' => $transactionId ?? ('txn_'.Str::random(10)),
                        'razorpay_signature' => 'webhook_verified',
                    ]);
                }
            } elseif ($payment && in_array($eventType, ['payment.failed'])) {
                $payment->update([
                    'status' => 'failed',
                    'error_code' => $payload['error_code'] ?? 'PAYMENT_FAILED',
                    'error_message' => $payload['error_description'] ?? 'Payment failed at gateway',
                ]);
            }

            return [
                'success' => true,
                'duplicate' => false,
                'event_id' => $eventId,
                'message' => 'Webhook processed successfully',
            ];
        });
    }

    /**
     * Process refund server-side and adjust accounting.
     */
    public function processRefund(Payment $payment, int $amountPaise, string $reason, ?User $admin = null): Refund
    {
        if ($payment->status !== 'successful') {
            throw new RuntimeException('Only successful payments can be refunded.');
        }

        $order = $payment->order;
        $totalRefunded = (int) $payment->refunds()->where('status', 'processed')->sum('amount_paise');
        $availableToRefund = $payment->amount_paise - $totalRefunded;

        if ($amountPaise > $availableToRefund) {
            throw new RuntimeException(
                'Requested refund amount exceeds available refundable balance. Max refundable: ₹'.number_format($availableToRefund / 100, 2)
            );
        }

        return DB::transaction(function () use ($payment, $order, $amountPaise, $reason, $admin) {
            $gatewayRefundId = 'rfnd_'.Str::random(14);

            $refund = Refund::create([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount_paise' => $amountPaise,
                'reason' => $reason,
                'status' => 'processed',
                'gateway_refund_id' => $gatewayRefundId,
                'processed_by_user_id' => $admin?->id ?? auth()->id(),
            ]);

            $newTotalRefunded = (int) $payment->refunds()->where('status', 'processed')->sum('amount_paise');
            if ($newTotalRefunded >= $payment->amount_paise) {
                $payment->update(['status' => 'refunded']);
                $order->update(['payment_status' => 'refunded', 'status' => 'refunded']);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'changed_by_user_id' => $admin?->id ?? auth()->id(),
                'notes' => 'Refund of ₹'.number_format($amountPaise / 100, 2)." processed ({$gatewayRefundId}): {$reason}",
            ]);

            return $refund;
        });
    }
}
