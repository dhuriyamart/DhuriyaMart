<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in to proceed with secure checkout.');
        }

        $cart = $this->cartService->getOrCreateCart($user);
        if ($cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('warning', 'Your shopping cart is empty.');
        }

        $outOfStock = $this->cartService->validateCartStock($cart);
        if (!empty($outOfStock)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are currently out of stock. Please adjust quantities.');
        }

        $totals = $this->cartService->calculateCartTotals($cart);
        $addresses = $user->addresses()->latest()->get();
        $groupedItems = $cart->items->groupBy(fn($item) => $item->variant?->product?->shop_id);

        $razorpayKeyId = Setting::get('razorpay_key_id', 'rzp_test_DhuriyaMartSandbox');
        $isSandbox = Setting::get('payment_sandbox_mode', '1') === '1';

        return view('storefront.checkout', compact(
            'cart',
            'totals',
            'addresses',
            'groupedItems',
            'razorpayKeyId',
            'isSandbox'
        ));
    }

    public function process(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $cart = $this->cartService->getOrCreateCart($user);
        if ($cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        $validated = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'name' => 'required_without:address_id|nullable|string|max:100',
            'phone' => 'required_without:address_id|nullable|string|max:20',
            'address_line1' => 'required_without:address_id|nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:100',
            'city' => 'required_without:address_id|nullable|string|max:100',
            'state' => 'required_without:address_id|nullable|string|max:100',
            'postal_code' => 'required_without:address_id|nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'payment_method' => 'required|in:cod,razorpay',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        // Resolve or create shipping address
        if (!empty($validated['address_id'])) {
            $shippingAddress = $user->addresses()->findOrFail($validated['address_id']);
        } else {
            $shippingAddress = Address::create([
                'user_id' => $user->id,
                'type' => 'shipping',
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'landmark' => $validated['landmark'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'] ?? 'India',
                'is_default' => $user->addresses()->count() === 0,
            ]);
        }

        try {
            $order = $this->orderService->createOrderFromCart(
                $cart,
                $user,
                $shippingAddress,
                $validated['payment_method'],
                $validated['customer_notes'] ?? null
            );

            // Handle online payment gateway initialization
            if ($validated['payment_method'] === 'razorpay') {
                $payment = $this->paymentService->createPaymentOrder($order, 'razorpay');
                $razorpayKeyId = Setting::get('razorpay_key_id', 'rzp_test_DhuriyaMartSandbox');
                $isSandbox = Setting::get('payment_sandbox_mode', '1') === '1';

                return view('storefront.payment-gateway', compact('order', 'payment', 'razorpayKeyId', 'isSandbox'));
            }

            // Cash on Delivery
            return redirect()->route('checkout.confirmation', $order->id)
                ->with('success', 'Your DhuriyaMart order has been placed successfully via Cash on Delivery!');

        } catch (RuntimeException $e) {
            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        }
    }

    public function confirmation(Request $request, int $id): View
    {
        $order = Order::with(['sellerOrders.shop', 'sellerOrders.items.variant', 'shippingAddress', 'items'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return view('storefront.order-confirmed', compact('order'));
    }
}

