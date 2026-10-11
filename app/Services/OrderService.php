<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
        protected InventoryService $inventoryService,
        protected CommissionService $commissionService,
        protected SettlementService $settlementService
    ) {}

    /**
     * Create parent Order and separate SellerOrders from Cart inside a transaction.
     */
    public function createOrderFromCart(
        Cart $cart,
        User $user,
        Address $shippingAddress,
        string $paymentMethod = 'cod',
        ?string $customerNotes = null
    ): Order {
        if ($cart->items()->count() === 0) {
            throw new RuntimeException('Your shopping cart is empty.');
        }

        // 1. Verify stock for all items
        $outOfStock = $this->cartService->validateCartStock($cart);
        if (! empty($outOfStock)) {
            $names = implode(', ', array_column($outOfStock, 'name'));
            throw new RuntimeException("The following items are out of stock: {$names}");
        }

        // 2. Calculate cart totals
        $totals = $this->cartService->calculateCartTotals($cart);

        return DB::transaction(function () use ($cart, $user, $shippingAddress, $paymentMethod, $customerNotes, $totals) {
            // Generate unique Order Number
            $orderNumber = 'ORD-'.strtoupper(Str::random(6)).'-'.time();

            // Create Parent Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'currency' => 'INR',
                'subtotal_paise' => $totals['subtotal_paise'],
                'discount_paise' => $totals['discount_paise'],
                'shipping_fee_paise' => $totals['shipping_fee_paise'],
                'tax_paise' => $totals['tax_paise'],
                'total_amount_paise' => $totals['total_amount_paise'],
                'coupon_code' => $cart->coupon_code,
                'customer_notes' => $customerNotes,
            ]);

            // Snapshot Shipping Address
            OrderAddress::create([
                'order_id' => $order->id,
                'type' => 'shipping',
                'name' => $shippingAddress->name,
                'phone' => $shippingAddress->phone,
                'email' => $user->email,
                'address_line1' => $shippingAddress->address_line1,
                'address_line2' => $shippingAddress->address_line2,
                'landmark' => $shippingAddress->landmark,
                'city' => $shippingAddress->city,
                'state' => $shippingAddress->state,
                'postal_code' => $shippingAddress->postal_code,
                'country' => $shippingAddress->country ?? 'India',
            ]);

            // Group items by shop to create isolated SellerOrders
            $itemsByShop = $cart->items->groupBy(fn ($item) => $item->variant->product->shop_id);
            $totalSubtotal = $totals['subtotal_paise'];

            foreach ($itemsByShop as $shopId => $cartItems) {
                $shop = $cartItems->first()->variant->product->shop;
                $shopSubtotal = (int) $cartItems->sum(fn ($i) => $i->quantity * $i->price_paise);

                // Pro-rate discount and shipping per shop proportion
                $shopProportion = $totalSubtotal > 0 ? ($shopSubtotal / $totalSubtotal) : 0;
                $shopDiscount = (int) round($totals['discount_paise'] * $shopProportion);
                $shopShipping = (int) round($totals['shipping_fee_paise'] * $shopProportion);
                $shopTax = (int) round($totals['tax_paise'] * $shopProportion);
                $shopTotal = max(0, $shopSubtotal - $shopDiscount) + $shopShipping + $shopTax;

                // Commission calculation
                $commissionCalc = $this->commissionService->calculateCommission($shop, $shopSubtotal);

                $sellerOrderNumber = 'SLR-'.$shop->id.'-'.strtoupper(Str::random(6));

                $sellerOrder = SellerOrder::create([
                    'order_id' => $order->id,
                    'shop_id' => $shop->id,
                    'seller_order_number' => $sellerOrderNumber,
                    'status' => 'pending',
                    'subtotal_paise' => $shopSubtotal,
                    'shipping_fee_paise' => $shopShipping,
                    'tax_paise' => $shopTax,
                    'discount_paise' => $shopDiscount,
                    'total_amount_paise' => $shopTotal,
                    'commission_rate' => $commissionCalc['rate'],
                    'commission_amount_paise' => $commissionCalc['amount_paise'],
                    'net_seller_amount_paise' => $commissionCalc['net_seller_amount_paise'],
                    'payout_status' => 'unpaid',
                ]);

                // Create OrderItem snapshots & deduct stock
                foreach ($cartItems as $cItem) {
                    $variant = $cItem->variant;
                    $product = $variant->product;
                    $lineTotal = $cItem->quantity * $cItem->price_paise;
                    $lineCommission = (int) round(($lineTotal * $commissionCalc['rate']) / 100);

                    // Atomic stock deduction
                    $this->inventoryService->deductStock(
                        $variant,
                        $cItem->quantity,
                        'sale',
                        $order->order_number,
                        $user
                    );

                    OrderItem::create([
                        'order_id' => $order->id,
                        'seller_order_id' => $sellerOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'shop_id' => $shop->id,
                        'product_name' => $product->name,
                        'variant_name' => $variant->name,
                        'sku' => $variant->sku,
                        'price_paise' => $cItem->price_paise,
                        'quantity' => $cItem->quantity,
                        'subtotal_paise' => $lineTotal,
                        'tax_paise' => 0,
                        'total_paise' => $lineTotal,
                        'commission_rate' => $commissionCalc['rate'],
                        'commission_amount_paise' => $lineCommission,
                    ]);
                }

                // Initial seller order history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'seller_order_id' => $sellerOrder->id,
                    'from_status' => null,
                    'to_status' => 'pending',
                    'changed_by_user_id' => $user->id,
                    'notes' => 'Seller order created during customer checkout',
                ]);
            }

            // Record Coupon redemption
            if ($totals['coupon']) {
                $coupon = $totals['coupon'];
                CouponRedemption::create([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'discount_amount_paise' => $totals['discount_paise'],
                ]);
                $coupon->increment('usage_count');
            }

            // Initial parent order status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'seller_order_id' => null,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by_user_id' => $user->id,
                'notes' => "Order placed via {$paymentMethod}",
            ]);

            // Clear Cart
            $this->cartService->clearCart($cart);

            return $order->load(['sellerOrders.items', 'addresses', 'items']);
        });
    }

    /**
     * Cancel an entire parent customer order.
     */
    public function cancelOrder(Order $order, string $reason, ?User $user = null): bool
    {
        if (! $order->canBeCancelled()) {
            throw new RuntimeException('This order cannot be cancelled in its current state.');
        }

        return DB::transaction(function () use ($order, $reason, $user) {
            $fromStatus = $order->status;
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Restore stock for all items
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $this->inventoryService->restoreStock(
                        $item->variant,
                        $item->quantity,
                        'order_cancellation',
                        $order->order_number,
                        $user
                    );
                }
            }

            // Cancel each seller order
            foreach ($order->sellerOrders as $sellerOrder) {
                $sellerOrder->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'seller_order_id' => $sellerOrder->id,
                    'from_status' => $sellerOrder->status,
                    'to_status' => 'cancelled',
                    'changed_by_user_id' => $user?->id ?? auth()->id(),
                    'notes' => "Cancelled by customer/admin: {$reason}",
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'seller_order_id' => null,
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'changed_by_user_id' => $user?->id ?? auth()->id(),
                'notes' => "Order cancelled: {$reason}",
            ]);

            return true;
        });
    }

    /**
     * Cancel a specific shop's sub-order.
     */
    public function cancelSellerOrder(SellerOrder $sellerOrder, string $reason, ?User $user = null): bool
    {
        if (! $sellerOrder->canBeCancelled()) {
            throw new RuntimeException('Seller order cannot be cancelled in its current state.');
        }

        return DB::transaction(function () use ($sellerOrder, $reason, $user) {
            $fromStatus = $sellerOrder->status;
            $sellerOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Restore stock for this seller's items
            foreach ($sellerOrder->items as $item) {
                if ($item->variant) {
                    $this->inventoryService->restoreStock(
                        $item->variant,
                        $item->quantity,
                        'seller_cancellation',
                        $sellerOrder->seller_order_number,
                        $user
                    );
                }
            }

            OrderStatusHistory::create([
                'order_id' => $sellerOrder->order_id,
                'seller_order_id' => $sellerOrder->id,
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'changed_by_user_id' => $user?->id ?? auth()->id(),
                'notes' => "Seller order cancelled: {$reason}",
            ]);

            // If all sibling seller orders are cancelled, cancel the parent order
            $order = $sellerOrder->order;
            if ($order->sellerOrders()->where('status', '!=', 'cancelled')->count() === 0) {
                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'All participating seller orders were cancelled',
                ]);
            }

            return true;
        });
    }

    /**
     * Update seller order status and perform automatic transitions (e.g. delivery ledger credit).
     */
    public function updateSellerOrderStatus(SellerOrder $sellerOrder, string $newStatus, ?string $notes = null, ?User $user = null): void
    {
        $validStatuses = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled', 'returned'];
        if (! in_array($newStatus, $validStatuses)) {
            throw new RuntimeException("Invalid order status: {$newStatus}");
        }

        DB::transaction(function () use ($sellerOrder, $newStatus, $notes, $user) {
            $fromStatus = $sellerOrder->status;
            $sellerOrder->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $sellerOrder->order_id,
                'seller_order_id' => $sellerOrder->id,
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'changed_by_user_id' => $user?->id ?? auth()->id(),
                'notes' => $notes ?? "Status updated to {$newStatus}",
            ]);

            // If marked delivered, credit seller earnings into the vendor ledger!
            if ($newStatus === 'delivered' && $fromStatus !== 'delivered') {
                $this->settlementService->creditSellerOrder($sellerOrder);
            }

            // Sync parent order status
            $order = $sellerOrder->order;
            $allStatuses = $order->sellerOrders()->pluck('status');

            if ($allStatuses->every(fn ($s) => $s === 'delivered')) {
                $order->update(['status' => 'completed']);
            } elseif ($allStatuses->contains('dispatched') || $allStatuses->contains('processing')) {
                $order->update(['status' => 'processing']);
            }
        });
    }
}
