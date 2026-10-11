<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_code',
        'store_id',
        'cashier_id',
        'customer_id',
        'mode',
        'status',
        'cart_items',
        'subtotal_paise',
        'discount_paise',
        'tax_paise',
        'total_amount_paise',
        'order_id',
        'payment_method',
        'cash_tendered_paise',
        'change_returned_paise',
        'completed_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'completed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function exitVerification(): HasOne
    {
        return $this->hasOne(ExitVerification::class);
    }

    public function getSubtotalRupeesAttribute(): float
    {
        return round($this->subtotal_paise / 100, 2);
    }

    public function getDiscountRupeesAttribute(): float
    {
        return round($this->discount_paise / 100, 2);
    }

    public function getTaxRupeesAttribute(): float
    {
        return round($this->tax_paise / 100, 2);
    }

    public function getTotalAmountRupeesAttribute(): float
    {
        return round($this->total_amount_paise / 100, 2);
    }

    public function getCashTenderedRupeesAttribute(): float
    {
        return $this->cash_tendered_paise ? round($this->cash_tendered_paise / 100, 2) : 0;
    }

    public function getChangeReturnedRupeesAttribute(): float
    {
        return $this->change_returned_paise ? round($this->change_returned_paise / 100, 2) : 0;
    }

    public function getItemCountAttribute(): int
    {
        if (empty($this->cart_items) || !is_array($this->cart_items)) {
            return 0;
        }

        return array_sum(array_column($this->cart_items, 'quantity'));
    }
}

