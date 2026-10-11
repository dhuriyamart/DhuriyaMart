<?php

namespace App\Services;

use App\Models\CashRegisterEntry;
use App\Models\ExitVerification;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PosSession;
use App\Models\ProductVariant;
use App\Models\SellerOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartMartService
{
    /**
     * Locate or create a customer via Customer ID, QR Token, or Phone.
     */
    public function findCustomerByIdentifier(string $identifier): ?User
    {
        $clean = trim($identifier);

        return User::where('customer_id', $clean)
            ->orWhere('qr_token', $clean)
            ->orWhere('phone', $clean)
            ->orWhere('email', $clean)
            ->first();
    }

    /**
     * Start or fetch active POS session for a store.
     */
    public function getOrCreateSession(Store $store, ?User $customer = null, ?User $cashier = null, string $mode = 'staff_assisted'): PosSession
    {
        $query = PosSession::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('mode', $mode);

        if ($customer) {
            $query->where('customer_id', $customer->id);
        } elseif ($cashier && $mode === 'staff_assisted') {
            $query->where('cashier_id', $cashier->id);
        }

        $session = $query->latest()->first();

        if (! $session) {
            $session = PosSession::create([
                'session_code' => 'POS-'.strtoupper(Str::random(8)),
                'store_id' => $store->id,
                'cashier_id' => $cashier?->id,
                'customer_id' => $customer?->id,
                'mode' => $mode,
                'status' => 'active',
                'cart_items' => [],
                'subtotal_paise' => 0,
                'discount_paise' => 0,
                'tax_paise' => 0,
                'total_amount_paise' => 0,
            ]);
        }

        return $session;
    }

    /**
     * Add or update an item in a POS session by barcode or SKU or variant ID.
     */
    public function addItemToSession(PosSession $session, string $barcodeOrSku, int $quantity = 1): PosSession
    {
        $variant = ProductVariant::with('product')
            ->where('barcode', $barcodeOrSku)
            ->orWhere('sku', $barcodeOrSku)
            ->orWhere('id', is_numeric($barcodeOrSku) ? (int) $barcodeOrSku : 0)
            ->first();

        if (! $variant) {
            throw new \InvalidArgumentException("Product not found for code: {$barcodeOrSku}");
        }

        $items = $session->cart_items ?? [];
        $foundIndex = null;

        foreach ($items as $idx => $item) {
            if ($item['variant_id'] == $variant->id) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex !== null) {
            $items[$foundIndex]['quantity'] += $quantity;
            $items[$foundIndex]['subtotal_paise'] = $items[$foundIndex]['quantity'] * $items[$foundIndex]['price_paise'];
        } else {
            $items[] = [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'name' => $variant->product->name.' - '.$variant->name,
                'price_paise' => $variant->price_paise,
                'cost_price_paise' => $variant->cost_price_paise ?? (int) ($variant->price_paise * 0.8),
                'quantity' => $quantity,
                'subtotal_paise' => $variant->price_paise * $quantity,
                'tax_paise' => 0,
            ];
        }

        return $this->recalculateSessionTotals($session, $items);
    }

    /**
     * Update quantity of an item in the session.
     */
    public function updateItemQuantity(PosSession $session, int $variantId, int $quantity): PosSession
    {
        $items = $session->cart_items ?? [];
        $updated = [];

        foreach ($items as $item) {
            if ($item['variant_id'] == $variantId) {
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                    $item['subtotal_paise'] = $quantity * $item['price_paise'];
                    $updated[] = $item;
                }
            } else {
                $updated[] = $item;
            }
        }

        return $this->recalculateSessionTotals($session, $updated);
    }

    /**
     * Remove an item from the session.
     */
    public function removeItemFromSession(PosSession $session, int $variantId): PosSession
    {
        return $this->updateItemQuantity($session, $variantId, 0);
    }

    /**
     * Clear all items in the session.
     */
    public function clearSession(PosSession $session): PosSession
    {
        return $this->recalculateSessionTotals($session, []);
    }

    protected function recalculateSessionTotals(PosSession $session, array $items): PosSession
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['subtotal_paise'] ?? 0);
        }

        $session->cart_items = $items;
        $session->subtotal_paise = $subtotal;
        $session->tax_paise = 0; // Prices are inclusive of GST in Indian retail standard
        $session->total_amount_paise = max(0, $subtotal - ($session->discount_paise ?? 0));
        $session->save();

        return $session;
    }

    /**
     * Checkout POS session and convert to finalized Order.
     */
    public function checkoutSession(
        PosSession $session,
        string $paymentMethod,
        ?int $cashTenderedPaise = null,
        ?string $customerIdentifier = null
    ): Order {
        if (empty($session->cart_items)) {
            throw new \RuntimeException('Cannot checkout an empty shopping session.');
        }

        return DB::transaction(function () use ($session, $paymentMethod, $cashTenderedPaise, $customerIdentifier) {
            // Associate customer if identifier provided
            if ($customerIdentifier && ! $session->customer_id) {
                $found = $this->findCustomerByIdentifier($customerIdentifier);
                if ($found) {
                    $session->customer_id = $found->id;
                }
            }

            // Fallback user if walk-in guest
            $userId = $session->customer_id;
            if (! $userId) {
                $guestUser = User::firstOrCreate(
                    ['email' => 'walkin.guest@dhuriyamart.com'],
                    [
                        'name' => 'Walk-in Store Customer',
                        'customer_id' => 'DM-CUST-WALKIN',
                        'qr_token' => 'DMQR-WALKIN-000',
                        'phone' => '+91 0000000000',
                        'village_name' => $session->store->village_name,
                        'password' => bcrypt(Str::random(16)),
                        'status' => 'active',
                    ]
                );
                $userId = $guestUser->id;
            }

            $orderNumber = 'DM-POS-'.strtoupper(Str::random(8));
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'store_id' => $session->store_id,
                'order_type' => $session->mode === 'self_service' ? 'pos_self_service' : 'pos_staff_assisted',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'currency' => 'INR',
                'subtotal_paise' => $session->subtotal_paise,
                'discount_paise' => $session->discount_paise,
                'shipping_fee_paise' => 0,
                'tax_paise' => $session->tax_paise,
                'total_amount_paise' => $session->total_amount_paise,
                'customer_notes' => "In-Store POS Sale via {$session->mode} at {$session->store->name}",
            ]);

            // Create OrderItems and decrement inventory
            foreach ($session->cart_items as $item) {
                $variant = ProductVariant::with('product.shop')->find($item['variant_id']);
                $shopId = $variant?->product?->shop_id ?? 1;

                // Create or find SellerOrder
                $sellerOrder = SellerOrder::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'shop_id' => $shopId,
                    ],
                    [
                        'seller_order_number' => 'SO-'.$order->order_number.'-'.$shopId,
                        'status' => 'delivered',
                        'subtotal_paise' => 0,
                        'total_amount_paise' => 0,
                        'commission_rate' => 0,
                        'commission_amount_paise' => 0,
                        'net_seller_amount_paise' => 0,
                        'payout_status' => 'settled',
                    ]
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'seller_order_id' => $sellerOrder->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'shop_id' => $shopId,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'price_paise' => $item['price_paise'],
                    'quantity' => $item['quantity'],
                    'subtotal_paise' => $item['subtotal_paise'],
                    'tax_paise' => 0,
                    'total_paise' => $item['subtotal_paise'],
                    'commission_rate' => 0,
                    'commission_amount_paise' => 0,
                ]);

                // Update seller order totals
                $sellerOrder->subtotal_paise += $item['subtotal_paise'];
                $sellerOrder->total_amount_paise += $item['subtotal_paise'];
                $sellerOrder->net_seller_amount_paise += $item['subtotal_paise'];
                $sellerOrder->save();

                // Deduct stock
                if ($variant) {
                    $variant->decrement('stock_quantity', $item['quantity']);
                    $variant->refresh();
                    InventoryMovement::create([
                        'product_variant_id' => $variant->id,
                        'user_id' => $session->cashier_id ?? $userId,
                        'type' => 'sale',
                        'quantity_change' => -$item['quantity'],
                        'stock_after' => $variant->stock_quantity,
                        'reference_type' => 'pos_order',
                        'reference_id' => (string) $order->id,
                        'notes' => "POS sale deduction {$order->order_number}",
                    ]);
                }
            }

            // Create Payment record
            Payment::create([
                'order_id' => $order->id,
                'gateway' => $paymentMethod,
                'transaction_id' => 'TXN-'.strtoupper(Str::random(10)),
                'amount_paise' => $order->total_amount_paise,
                'status' => 'captured',
                'payload' => [
                    'pos_session' => $session->session_code,
                    'payment_method' => $paymentMethod,
                    'cash_tendered_paise' => $cashTenderedPaise,
                ],
            ]);

            // If cash payment, log into store cash register
            if ($paymentMethod === 'cash') {
                $changePaise = $cashTenderedPaise ? max(0, $cashTenderedPaise - $session->total_amount_paise) : 0;
                $session->cash_tendered_paise = $cashTenderedPaise ?? $session->total_amount_paise;
                $session->change_returned_paise = $changePaise;

                $register = $session->store->getActiveRegister();
                if ($register) {
                    $register->increment('cash_sales_paise', $session->total_amount_paise);
                    $register->recalculateExpected();
                    $register->save();

                    CashRegisterEntry::create([
                        'cash_register_id' => $register->id,
                        'user_id' => $session->cashier_id ?? $userId,
                        'type' => 'sale',
                        'amount_paise' => $session->total_amount_paise,
                        'order_id' => $order->id,
                        'reason' => "Cash sale for Order {$order->order_number}",
                    ]);
                }
            }

            // Mark session completed
            $session->status = 'completed';
            $session->order_id = $order->id;
            $session->payment_method = $paymentMethod;
            $session->completed_at = now();
            $session->save();

            return $order;
        });
    }

    /**
     * Mode A: Staff-Assisted Physical Exit Verification.
     * Staff physically checks items against the bill.
     */
    public function verifyExitStaff(Order $order, int $verifierId, array $scannedItems, ?string $notes = null): ExitVerification
    {
        $billedItemsCount = $order->items()->sum('quantity');
        $scannedItemsCount = array_sum(array_column($scannedItems, 'quantity'));

        $isPassed = ($billedItemsCount === $scannedItemsCount);

        return ExitVerification::create([
            'verification_code' => 'EXIT-'.strtoupper(Str::random(8)),
            'store_id' => $order->store_id ?? 1,
            'order_id' => $order->id,
            'pos_session_id' => $order->posSession?->id,
            'verifier_id' => $verifierId,
            'mode' => 'staff_assisted',
            'status' => $isPassed ? 'passed' : 'discrepancy_flagged',
            'total_billed_items' => $billedItemsCount,
            'total_scanned_items' => $scannedItemsCount,
            'scanned_items' => $scannedItems,
            'discrepancy_details' => $isPassed ? null : [
                'expected_count' => $billedItemsCount,
                'scanned_count' => $scannedItemsCount,
                'difference' => $scannedItemsCount - $billedItemsCount,
            ],
            'notes' => $notes ?? ($isPassed ? 'Staff manual physical verification passed.' : 'Discrepancy: Item count mismatch.'),
            'verified_at' => now(),
        ]);
    }

    /**
     * Mode B: Automated Barcode Verification Gate.
     * Scans item barcodes and automatically matches against billed items.
     */
    public function verifyExitAutomated(Order $order, array $scannedBarcodes): ExitVerification
    {
        $orderItems = $order->items()->with('variant')->get();
        $expectedMap = [];
        $totalBilled = 0;

        foreach ($orderItems as $item) {
            $key = $item->variant->barcode ?: $item->sku;
            $expectedMap[$key] = ($expectedMap[$key] ?? 0) + $item->quantity;
            $totalBilled += $item->quantity;
        }

        // Count scanned barcodes
        $scannedMap = [];
        $totalScanned = count($scannedBarcodes);

        foreach ($scannedBarcodes as $bc) {
            $bc = trim($bc);
            if ($bc) {
                $scannedMap[$bc] = ($scannedMap[$bc] ?? 0) + 1;
            }
        }

        // Check for discrepancies
        $discrepancies = [];
        foreach ($scannedMap as $bc => $count) {
            $expected = $expectedMap[$bc] ?? 0;
            if ($count > $expected) {
                $discrepancies[] = [
                    'code' => $bc,
                    'type' => 'extra_item',
                    'message' => "Scanned {$count} units, but bill contains {$expected}.",
                ];
            }
        }

        foreach ($expectedMap as $bc => $count) {
            $scanned = $scannedMap[$bc] ?? 0;
            if ($scanned < $count) {
                $discrepancies[] = [
                    'code' => $bc,
                    'type' => 'missing_item',
                    'message' => "Bill contains {$count} units, but only {$scanned} scanned at gate.",
                ];
            }
        }

        $isPassed = empty($discrepancies);

        return ExitVerification::create([
            'verification_code' => 'AUTO-GATE-'.strtoupper(Str::random(8)),
            'store_id' => $order->store_id ?? 1,
            'order_id' => $order->id,
            'pos_session_id' => $order->posSession?->id,
            'verifier_id' => null, // Automated system
            'mode' => 'automated',
            'status' => $isPassed ? 'passed' : 'discrepancy_flagged',
            'total_billed_items' => $totalBilled,
            'total_scanned_items' => $totalScanned,
            'scanned_items' => $scannedMap,
            'discrepancy_details' => $isPassed ? null : $discrepancies,
            'notes' => $isPassed
                ? 'Automated barcode scanner verified 100% match. Gate unlocked.'
                : 'Automated scan detected discrepancy. Gate locked. Cashier intervention requested.',
            'verified_at' => now(),
        ]);
    }
}
