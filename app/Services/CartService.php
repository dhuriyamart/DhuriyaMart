<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartService
{
    public function __construct(
        protected ShippingService $shippingService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Get or create a cart for an authenticated user or guest session.
     */
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        if ($sessionId) {
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        throw new RuntimeException('Either a user or a session ID must be provided to retrieve a cart.');
    }

    /**
     * Add an item to the cart, verifying stock availability.
     */
    public function addItem(Cart $cart, int $variantId, int $quantity = 1): CartItem
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantity must be greater than zero.');
        }

        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if (! $variant->is_active || ! $variant->product->is_active || $variant->product->status !== 'approved') {
            throw new RuntimeException('This product variant is currently unavailable for purchase.');
        }

        $cartItem = $cart->items()->where('product_variant_id', $variantId)->first();
        $targetQuantity = ($cartItem ? $cartItem->quantity : 0) + $quantity;

        if ($variant->stock_quantity < $targetQuantity) {
            throw new RuntimeException(
                "Only {$variant->stock_quantity} unit(s) available in stock for {$variant->name}."
            );
        }

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $targetQuantity,
                'price_paise' => $variant->price_paise, // Fresh price sync
            ]);

            return $cartItem;
        }

        return $cart->items()->create([
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'price_paise' => $variant->price_paise,
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateItem(Cart $cart, int $cartItemId, int $quantity): ?CartItem
    {
        $item = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $variant = $item->variant;
        if ($variant->stock_quantity < $quantity) {
            throw new RuntimeException("Only {$variant->stock_quantity} unit(s) available in stock.");
        }

        $item->update([
            'quantity' => $quantity,
            'price_paise' => $variant->price_paise,
        ]);

        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Cart $cart, int $cartItemId): void
    {
        $cart->items()->where('id', $cartItemId)->delete();
    }

    /**
     * Empty all items from the cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Apply coupon code to cart.
     */
    public function applyCoupon(Cart $cart, string $code): array
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Invalid promotional coupon code.'];
        }

        $subtotalPaise = $cart->subtotal_paise;
        if (! $coupon->isValidForAmount($subtotalPaise)) {
            return [
                'success' => false,
                'message' => "Coupon {$code} is not applicable. Minimum order value of ₹".number_format($coupon->min_order_value_paise / 100, 2).' required.',
            ];
        }

        $cart->update(['coupon_code' => $code]);

        return [
            'success' => true,
            'message' => "Coupon {$code} applied successfully! ({$coupon->formatted_discount_description})",
            'coupon' => $coupon,
        ];
    }

    /**
     * Remove coupon code.
     */
    public function removeCoupon(Cart $cart): void
    {
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Calculate comprehensive cart financials.
     */
    public function calculateCartTotals(Cart $cart): array
    {
        $cart->loadMissing(['items.variant.product.shop']);
        $subtotalPaise = $cart->subtotal_paise;

        $discountPaise = 0;
        $coupon = null;

        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon && $coupon->isValidForAmount($subtotalPaise)) {
                $discountPaise = $coupon->calculateDiscount($subtotalPaise);
            } else {
                $cart->update(['coupon_code' => null]);
                $coupon = null;
            }
        }

        $subtotalAfterDiscount = max(0, $subtotalPaise - $discountPaise);
        $shippingFeePaise = $this->shippingService->calculateShippingFee($subtotalAfterDiscount);

        // Standard 5% GST on e-commerce goods
        $taxRatePercent = (float) Setting::get('tax_rate_percent', '5.00');
        $taxPaise = (int) round(($subtotalAfterDiscount * $taxRatePercent) / 100);

        $totalAmountPaise = $subtotalAfterDiscount + $shippingFeePaise + $taxPaise;

        return [
            'subtotal_paise' => $subtotalPaise,
            'discount_paise' => $discountPaise,
            'shipping_fee_paise' => $shippingFeePaise,
            'tax_paise' => $taxPaise,
            'total_amount_paise' => $totalAmountPaise,
            'coupon' => $coupon,
            'items_count' => $cart->items_count,
        ];
    }

    /**
     * Validate that all items in the cart are still in stock.
     */
    public function validateCartStock(Cart $cart): array
    {
        $cart->loadMissing(['items.variant.product']);
        $outOfStock = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            if (! $variant || ! $variant->is_active || $variant->stock_quantity < $item->quantity) {
                $outOfStock[] = [
                    'item' => $item,
                    'available' => $variant ? $variant->stock_quantity : 0,
                    'requested' => $item->quantity,
                    'name' => $variant ? $variant->product->name.' ('.$variant->name.')' : 'Product Unavailable',
                ];
            }
        }

        return $outOfStock;
    }

    /**
     * Merge items from guest session cart into authenticated user cart upon login.
     */
    public function mergeGuestCartIntoUserCart(string $sessionId, User $user): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->first();
        if (! $guestCart || $guestCart->items()->count() === 0) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $gItem) {
                $existing = $userCart->items()->where('product_variant_id', $gItem->product_variant_id)->first();
                if ($existing) {
                    $existing->update([
                        'quantity' => $existing->quantity + $gItem->quantity,
                        'price_paise' => $gItem->price_paise,
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_variant_id' => $gItem->product_variant_id,
                        'quantity' => $gItem->quantity,
                        'price_paise' => $gItem->price_paise,
                    ]);
                }
            }
            if ($guestCart->coupon_code && ! $userCart->coupon_code) {
                $userCart->update(['coupon_code' => $guestCart->coupon_code]);
            }
            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }
}
