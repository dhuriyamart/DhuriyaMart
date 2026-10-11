<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashRegisterEntry;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CashDeskService
{
    /**
     * Open a daily cash register for a store.
     */
    public function openRegister(Store $store, User $cashier, int $openingFloatPaise, ?string $notes = null): CashRegister
    {
        $today = now()->toDateString();

        $existing = CashRegister::where('store_id', $store->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return $existing;
        }

        $register = CashRegister::create([
            'store_id' => $store->id,
            'opened_by_id' => $cashier->id,
            'register_date' => $today,
            'opening_float_paise' => $openingFloatPaise,
            'cash_sales_paise' => 0,
            'cash_in_paise' => 0,
            'cash_out_paise' => 0,
            'expected_cash_paise' => $openingFloatPaise,
            'closing_actual_paise' => null,
            'difference_paise' => 0,
            'status' => 'open',
            'notes' => $notes ?? "Daily register opened by {$cashier->name}",
            'opened_at' => now(),
        ]);

        CashRegisterEntry::create([
            'cash_register_id' => $register->id,
            'user_id' => $cashier->id,
            'type' => 'opening_float',
            'amount_paise' => $openingFloatPaise,
            'reason' => 'Morning drawer opening float',
        ]);

        return $register;
    }

    /**
     * Record cash in or cash out (petty expenses, supplier payment, drawer adjustment).
     */
    public function recordCashEntry(
        CashRegister $register,
        User $user,
        string $type,
        int $amountPaise,
        ?int $orderId = null,
        ?string $reason = null
    ): CashRegisterEntry {
        return DB::transaction(function () use ($register, $user, $type, $amountPaise, $orderId, $reason) {
            $entry = CashRegisterEntry::create([
                'cash_register_id' => $register->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount_paise' => $amountPaise,
                'order_id' => $orderId,
                'reason' => $reason,
            ]);

            if ($type === 'cash_in' || $type === 'customer_deposit') {
                $register->increment('cash_in_paise', $amountPaise);
            } elseif ($type === 'cash_out') {
                $register->increment('cash_out_paise', $amountPaise);
            } elseif ($type === 'sale') {
                $register->increment('cash_sales_paise', $amountPaise);
            }

            $register->recalculateExpected();
            $register->save();

            return $entry;
        });
    }

    /**
     * Close and reconcile the daily cash drawer with physical cash count.
     */
    public function closeRegister(
        CashRegister $register,
        User $cashier,
        int $closingActualPaise,
        ?string $notes = null
    ): CashRegister {
        $register->recalculateExpected();
        $expected = $register->expected_cash_paise;
        $difference = $closingActualPaise - $expected;

        $register->closed_by_id = $cashier->id;
        $register->closing_actual_paise = $closingActualPaise;
        $register->difference_paise = $difference;
        $register->status = 'closed';
        $register->closed_at = now();
        if ($notes) {
            $register->notes = ($register->notes ? $register->notes.' | ' : '').$notes;
        }
        $register->save();

        return $register;
    }

    /**
     * Customer deposits cash at the desk to top up digital village wallet balance.
     */
    public function customerDeposit(User $customer, User $cashier, int $amountPaise, Store $store, ?string $notes = null): array
    {
        return DB::transaction(function () use ($customer, $cashier, $amountPaise, $store, $notes) {
            $customer->increment('wallet_balance_paise', $amountPaise);

            $register = $store->getActiveRegister() ?? $this->openRegister($store, $cashier, 0);

            $entry = $this->recordCashEntry(
                $register,
                $cashier,
                'customer_deposit',
                $amountPaise,
                null,
                "Wallet top-up deposit for Customer {$customer->name} ({$customer->customer_id})".($notes ? " - {$notes}" : '')
            );

            return [
                'customer' => $customer,
                'new_balance_paise' => $customer->wallet_balance_paise,
                'new_balance_rupees' => $customer->wallet_balance_rupees,
                'entry' => $entry,
            ];
        });
    }

    /**
     * Generate daily financial reconciliation summary for store owner.
     */
    public function getDailyReconciliation(Store $store, ?string $date = null): array
    {
        $targetDate = $date ?? now()->toDateString();

        $registers = CashRegister::with(['openedBy', 'closedBy', 'entries.order'])
            ->where('store_id', $store->id)
            ->where('register_date', $targetDate)
            ->get();

        $totalOpeningFloat = $registers->sum('opening_float_paise');
        $totalCashSales = $registers->sum('cash_sales_paise');
        $totalCashIn = $registers->sum('cash_in_paise');
        $totalCashOut = $registers->sum('cash_out_paise');
        $totalExpected = $registers->sum('expected_cash_paise');
        $totalActual = $registers->whereNotNull('closing_actual_paise')->sum('closing_actual_paise');
        $totalDifference = $registers->sum('difference_paise');

        // Digital orders for the day
        $digitalSalesPaise = (int) Order::where('store_id', $store->id)
            ->whereDate('created_at', $targetDate)
            ->where('payment_status', 'paid')
            ->whereIn('payment_method', ['razorpay', 'upi', 'wallet'])
            ->sum('total_amount_paise');

        $totalGrossRevenue = $totalCashSales + $digitalSalesPaise;

        return [
            'store' => $store,
            'date' => $targetDate,
            'registers' => $registers,
            'opening_float_rupees' => round($totalOpeningFloat / 100, 2),
            'cash_sales_rupees' => round($totalCashSales / 100, 2),
            'digital_sales_rupees' => round($digitalSalesPaise / 100, 2),
            'gross_revenue_rupees' => round($totalGrossRevenue / 100, 2),
            'cash_in_rupees' => round($totalCashIn / 100, 2),
            'cash_out_rupees' => round($totalCashOut / 100, 2),
            'expected_cash_rupees' => round($totalExpected / 100, 2),
            'closing_actual_rupees' => round($totalActual / 100, 2),
            'difference_rupees' => round($totalDifference / 100, 2),
            'is_balanced' => $totalDifference === 0,
        ];
    }
}
