<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    private function createTestOrder(): Order
    {
        $seller = $this->createSellerUser('Payment Shop');
        $category = Category::create(['name' => 'Pay Cat', 'slug' => 'pay-cat', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Payable Item',
            'slug' => 'payable-item',
            'base_price_paise' => 150000,
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'PAY-001',
            'price_paise' => 150000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variant->id, 1);

        $this->actingAs($customer)->post('/checkout', [
            'address_id' => $customer->addresses->first()->id,
            'payment_method' => 'razorpay',
        ]);

        return Order::where('user_id', $customer->id)->latest()->first();
    }

    public function test_valid_hmac_signature_verifies_payment_and_marks_order_paid(): void
    {
        $order = $this->createTestOrder();
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($order, 'razorpay');

        $secret = 'rzp_test_DhuriyaMartSecretKey';
        Setting::set('razorpay_key_secret', $secret);

        $txId = 'pay_live_test_'.uniqid();
        $gatewayOrderId = $payment->gateway_order_id;
        $signature = hash_hmac('sha256', "{$gatewayOrderId}|{$txId}", $secret);

        $response = $this->post('/payments/verify', [
            'order_id' => $order->id,
            'razorpay_order_id' => $gatewayOrderId,
            'razorpay_payment_id' => $txId,
            'razorpay_signature' => $signature,
        ]);

        $response->assertRedirect(route('checkout.confirmation', $order->id));

        // Assert Order and Payment statuses
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals('successful', $payment->fresh()->status);
        $this->assertEquals($txId, $payment->fresh()->transaction_id);

        // Seller order must transition to confirmed
        $sellerOrder = $order->sellerOrders()->first();
        $this->assertEquals('confirmed', $sellerOrder->status);
    }

    public function test_invalid_signature_is_rejected_and_leaves_order_unpaid(): void
    {
        Setting::set('payment_sandbox_mode', '0'); // strict mode
        $order = $this->createTestOrder();
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($order, 'razorpay');

        $response = $this->post('/payments/verify', [
            'order_id' => $order->id,
            'razorpay_order_id' => $payment->gateway_order_id,
            'razorpay_payment_id' => 'pay_invalid_123',
            'razorpay_signature' => 'tampered_invalid_signature',
        ]);

        $response->assertRedirect(route('checkout.index'));
        $this->assertEquals('pending', $order->fresh()->payment_status);
        $this->assertEquals('failed', $payment->fresh()->status);
    }

    public function test_mock_sandbox_gateway_simulator_marks_order_paid(): void
    {
        $order = $this->createTestOrder();

        $response = $this->post("/payments/mock/{$order->id}");
        $response->assertRedirect(route('checkout.confirmation', $order->id));

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_webhook_idempotency_prevents_duplicate_processing(): void
    {
        $order = $this->createTestOrder();
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($order, 'razorpay');

        $eventId = 'evt_test_'.uniqid();
        $webhookPayload = [
            'event_id' => $eventId,
            'event' => 'payment.captured',
            'order_id' => $payment->gateway_order_id,
            'payment_id' => 'pay_hook_'.uniqid(),
            'amount' => $order->total_amount_paise,
        ];

        // First webhook call
        $firstResult = $paymentService->handleWebhook('razorpay', $webhookPayload);
        $this->assertTrue($firstResult['success']);
        $this->assertFalse($firstResult['duplicate']);

        // Assert Payment Event was persisted
        $this->assertEquals(1, PaymentEvent::where('event_id', $eventId)->count());

        // Second duplicate webhook call with same event ID
        $secondResult = $paymentService->handleWebhook('razorpay', $webhookPayload);
        $this->assertTrue($secondResult['success']);
        $this->assertTrue($secondResult['duplicate'], 'Duplicate webhook must be detected');

        // Still only 1 record in database
        $this->assertEquals(1, PaymentEvent::where('event_id', $eventId)->count());
    }

    public function test_refund_processing_updates_payment_and_prevents_over_refunding(): void
    {
        $order = $this->createTestOrder();
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPaymentOrder($order, 'razorpay');

        // Mark paid first
        $payment->update(['status' => 'successful']);
        $order->update(['payment_status' => 'paid', 'status' => 'paid']);

        $totalPaise = $payment->amount_paise;

        // Process partial refund
        $refund = $paymentService->processRefund($payment, 50000, 'Customer goodwill partial refund');
        $this->assertEquals('processed', $refund->status);
        $this->assertEquals(50000, $refund->amount_paise);

        // Try refunding more than remaining balance
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exceeds available refundable balance');
        $paymentService->processRefund($payment, $totalPaise, 'Excess refund attempt');
    }
}
