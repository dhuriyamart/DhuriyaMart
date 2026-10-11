<?php

namespace App\Services;

use App\Models\SellerLedgerEntry;
use App\Models\SellerOrder;
use App\Models\SellerSettlement;
use App\Models\SellerSettlementItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SettlementService
{
    /**
     * Credit seller earnings into their ledger when an order is delivered.
     */
    public function creditSellerOrder(SellerOrder $sellerOrder): ?SellerLedgerEntry
    {
        // Avoid duplicate credit for the same seller order
        $existing = SellerLedgerEntry::where('shop_id', $sellerOrder->shop_id)
            ->where('reference_type', 'seller_order')
            ->where('reference_id', (string) $sellerOrder->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($sellerOrder) {
            $lastEntry = SellerLedgerEntry::where('shop_id', $sellerOrder->shop_id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastEntry ? $lastEntry->balance_after_paise : 0;
            $creditAmount = $sellerOrder->net_seller_amount_paise;
            $newBalance = $currentBalance + $creditAmount;

            return SellerLedgerEntry::create([
                'shop_id' => $sellerOrder->shop_id,
                'type' => 'credit',
                'amount_paise' => $creditAmount,
                'balance_after_paise' => $newBalance,
                'reference_type' => 'seller_order',
                'reference_id' => (string) $sellerOrder->id,
                'description' => "Earnings credited for Order #{$sellerOrder->seller_order_number} (Delivered)",
            ]);
        });
    }

    /**
     * Debit seller ledger in case of refund or penalty.
     */
    public function debitSellerOrderRefund(SellerOrder $sellerOrder, int $amountPaise): SellerLedgerEntry
    {
        return DB::transaction(function () use ($sellerOrder, $amountPaise) {
            $lastEntry = SellerLedgerEntry::where('shop_id', $sellerOrder->shop_id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastEntry ? $lastEntry->balance_after_paise : 0;
            $newBalance = $currentBalance - $amountPaise;

            return SellerLedgerEntry::create([
                'shop_id' => $sellerOrder->shop_id,
                'type' => 'debit',
                'amount_paise' => $amountPaise,
                'balance_after_paise' => $newBalance,
                'reference_type' => 'refund',
                'reference_id' => (string) $sellerOrder->id,
                'description' => "Deduction for customer refund on Order #{$sellerOrder->seller_order_number}",
            ]);
        });
    }

    /**
     * Generate a settlement batch for unpaid delivered orders of a shop.
     */
    public function generateSettlement(Shop $shop, ?User $admin = null): ?SellerSettlement
    {
        return DB::transaction(function () use ($shop, $admin) {
            $unpaidOrders = SellerOrder::where('shop_id', $shop->id)
                ->where('status', 'delivered')
                ->where('payout_status', 'unpaid')
                ->get();

            if ($unpaidOrders->isEmpty()) {
                return null;
            }

            $totalSales = (int) $unpaidOrders->sum('total_amount_paise');
            $totalCommission = (int) $unpaidOrders->sum('commission_amount_paise');
            $netPayout = (int) $unpaidOrders->sum('net_seller_amount_paise');

            $settlementNumber = 'SETL-'.$shop->id.'-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $settlement = SellerSettlement::create([
                'settlement_number' => $settlementNumber,
                'shop_id' => $shop->id,
                'total_sales_paise' => $totalSales,
                'commission_deducted_paise' => $totalCommission,
                'adjustments_paise' => 0,
                'net_payout_paise' => $netPayout,
                'status' => 'draft',
                'processed_by_user_id' => $admin?->id ?? auth()->id(),
            ]);

            foreach ($unpaidOrders as $order) {
                SellerSettlementItem::create([
                    'seller_settlement_id' => $settlement->id,
                    'seller_order_id' => $order->id,
                    'gross_amount_paise' => $order->total_amount_paise,
                    'commission_amount_paise' => $order->commission_amount_paise,
                    'net_amount_paise' => $order->net_seller_amount_paise,
                ]);

                $order->update(['payout_status' => 'in_settlement']);
            }

            return $settlement->load('items');
        });
    }

    /**
     * Mark settlement statement paid with manual banking reference.
     */
    public function markSettlementPaid(SellerSettlement $settlement, string $paymentReference, ?User $admin = null): void
    {
        if ($settlement->status === 'paid') {
            throw new RuntimeException('This settlement is already paid.');
        }

        DB::transaction(function () use ($settlement, $paymentReference, $admin) {
            $settlement->update([
                'status' => 'paid',
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
                'processed_by_user_id' => $admin?->id ?? auth()->id(),
            ]);

            // Mark participating seller orders as settled
            $orderIds = $settlement->items()->pluck('seller_order_id');
            SellerOrder::whereIn('id', $orderIds)->update(['payout_status' => 'settled']);

            // Deduct from seller ledger for the payout made
            $lastEntry = SellerLedgerEntry::where('shop_id', $settlement->shop_id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastEntry ? $lastEntry->balance_after_paise : 0;
            $newBalance = $currentBalance - $settlement->net_payout_paise;

            SellerLedgerEntry::create([
                'shop_id' => $settlement->shop_id,
                'type' => 'debit',
                'amount_paise' => $settlement->net_payout_paise,
                'balance_after_paise' => $newBalance,
                'reference_type' => 'settlement',
                'reference_id' => (string) $settlement->id,
                'description' => "Settlement Payout Ref: {$paymentReference} (Settlement #{$settlement->settlement_number})",
            ]);
        });
    }
}
