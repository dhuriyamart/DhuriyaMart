<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SellerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shop_id',
        'seller_order_number',
        'status',
        'subtotal_paise',
        'shipping_fee_paise',
        'tax_paise',
        'discount_paise',
        'total_amount_paise',
        'commission_rate',
        'commission_amount_paise',
        'net_seller_amount_paise',
        'payout_status',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_paise' => 'integer',
            'shipping_fee_paise' => 'integer',
            'tax_paise' => 'integer',
            'discount_paise' => 'integer',
            'total_amount_paise' => 'integer',
            'commission_rate' => 'decimal:2',
            'commission_amount_paise' => 'integer',
            'net_seller_amount_paise' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function settlementItem(): HasOne
    {
        return $this->hasOne(SellerSettlementItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return Money::format($this->total_amount_paise);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return Money::format($this->subtotal_paise);
    }

    public function getFormattedCommissionAttribute(): string
    {
        return Money::format($this->commission_amount_paise);
    }

    public function getFormattedNetSellerAttribute(): string
    {
        return Money::format($this->net_seller_amount_paise);
    }

    public function canBeDispatched(): bool
    {
        return in_array($this->status, ['confirmed', 'processing']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }
}

