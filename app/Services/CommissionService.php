<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Shop;

class CommissionService
{
    /**
     * Calculate marketplace commission for a shop line item or seller order.
     *
     * @return array{rate: float, amount_paise: int, net_seller_amount_paise: int}
     */
    public function calculateCommission(Shop $shop, int $subtotalPaise): array
    {
        $defaultRate = (float) Setting::get('default_commission_rate', '10.00');
        $rate = $shop->commission_rate !== null ? (float) $shop->commission_rate : $defaultRate;

        $commissionAmountPaise = (int) round(($subtotalPaise * $rate) / 100);
        $netSellerPaise = max(0, $subtotalPaise - $commissionAmountPaise);

        return [
            'rate' => $rate,
            'amount_paise' => $commissionAmountPaise,
            'net_seller_amount_paise' => $netSellerPaise,
        ];
    }
}

