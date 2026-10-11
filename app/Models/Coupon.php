<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_value_paise',
        'max_discount_paise',
        'usage_limit',
        'usage_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'integer',
            'min_order_value_paise' => 'integer',
            'max_discount_paise' => 'integer',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isValidForAmount(int $subtotalPaise): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        if ($subtotalPaise < $this->min_order_value_paise) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $subtotalPaise): int
    {
        if (!$this->isValidForAmount($subtotalPaise)) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = (int) round(($subtotalPaise * $this->discount_value) / 100);
            if ($this->max_discount_paise && $discount > $this->max_discount_paise) {
                $discount = $this->max_discount_paise;
            }
            return min($discount, $subtotalPaise);
        }

        // Fixed amount discount in paise
        return min($this->discount_value, $subtotalPaise);
    }

    public function getFormattedDiscountDescriptionAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            $desc = "{$this->discount_value}% OFF";
            if ($this->max_discount_paise) {
                $desc .= " up to " . Money::format($this->max_discount_paise);
            }
            return $desc;
        }

        return Money::format($this->discount_value) . " OFF";
    }
}

