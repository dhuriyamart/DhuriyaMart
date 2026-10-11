<?php

namespace App\Support;

class Money
{
    /**
     * Format paise into Indian Rupee string (₹1,250.00).
     */
    public static function format(int|float|null $paise): string
    {
        $p = (int) ($paise ?? 0);

        return '₹'.number_format($p / 100, 2);
    }

    /**
     * Convert paise to rupee decimal.
     */
    public static function toRupees(int|float|null $paise): float
    {
        return round(((int) ($paise ?? 0)) / 100, 2);
    }

    /**
     * Convert rupees to integer paise.
     */
    public static function toPaise(float|int|string|null $rupees): int
    {
        return (int) round(((float) ($rupees ?? 0)) * 100);
    }
}
