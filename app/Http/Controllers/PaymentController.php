<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function createOrder(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);
        $order = Order::findOrFail($request->input('order_id'));

        $payment = $this->paymentService->createPaymentOrder($order, 'razorpay');

        return response()->json([
            'success' => true,
            'gateway_order_id' => $payment->gateway_order_id,
            'amount_paise' => $payment->amount_paise,
            'currency' => $payment->currency,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $result = $this->paymentService->verifyPayment($order, $validated);

        if ($result['success']) {
            return redirect()->route('checkout.confirmation', $order->id)
                ->with('success', 'Online payment verified and captured successfully!');
        }

        return redirect()->route('checkout.index')
            ->with('error', $result['message'] ?? 'Payment verification failed.');
    }

    public function webhook(Request $request, string $provider): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature') ?? $request->header('x-signature');
        $payload = $request->all();

        $result = $this->paymentService->handleWebhook($provider, $payload, $signature);

        return response()->json($result);
    }

    /**
     * Sandbox Simulator: Instantly simulate a successful gateway payment in test mode.
     */
    public function mockSuccess(Request $request, int $orderId): RedirectResponse
    {
        $order = Order::findOrFail($orderId);
        $mockPaymentId = 'pay_mock_'.Str::random(12);

        $payment = $order->payments()->latest('id')->first();
        $gatewayOrderId = $payment ? $payment->gateway_order_id : ('order_mock_'.Str::random(10));

        $result = $this->paymentService->verifyPayment($order, [
            'razorpay_order_id' => $gatewayOrderId,
            'razorpay_payment_id' => $mockPaymentId,
            'razorpay_signature' => 'sandbox_simulator_authorized',
        ]);

        if ($result['success']) {
            return redirect()->route('checkout.confirmation', $order->id)
                ->with('success', 'Sandbox payment successfully simulated and verified!');
        }

        return redirect()->route('checkout.index')->with('error', 'Mock payment simulation failed.');
    }
}
