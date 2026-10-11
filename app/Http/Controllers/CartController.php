<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    protected function getCart(Request $request)
    {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        return $this->cartService->getOrCreateCart($user, $sessionId);
    }

    public function index(Request $request): View
    {
        $cart = $this->getCart($request);
        $totals = $this->cartService->calculateCartTotals($cart);
        $outOfStock = $this->cartService->validateCartStock($cart);

        // Group items by shop for multi-vendor visibility
        $groupedItems = $cart->items->groupBy(fn ($item) => $item->variant?->product?->shop_id);

        return view('storefront.cart', compact('cart', 'totals', 'outOfStock', 'groupedItems'));
    }

    public function addItem(Request $request): RedirectResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:50',
        ]);

        $cart = $this->getCart($request);
        $variantId = (int) $request->input('variant_id');
        $quantity = (int) $request->input('quantity', 1);

        try {
            $this->cartService->addItem($cart, $variantId, $quantity);

            return back()->with('success', 'Item successfully added to your DhuriyaMart cart!');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateItem(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $cart = $this->getCart($request);
        $quantity = (int) $request->input('quantity');

        try {
            $this->cartService->updateItem($cart, $id, $quantity);

            return back()->with('success', 'Cart updated successfully.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeItem(Request $request, int $id): RedirectResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->removeItem($cart, $id);

        return back()->with('success', 'Item removed from your cart.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $cart = $this->getCart($request);
        $result = $this->cartService->applyCoupon($cart, $request->input('coupon_code'));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $cart = $this->getCart($request);
        $this->cartService->removeCoupon($cart);

        return back()->with('info', 'Coupon removed.');
    }
}
