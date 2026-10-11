<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_number',
        'shop_id',
        'total_sales_paise',
        'commission_deducted_paise',
        'adjustments_paise',
        'net_payout_paise',
        'status',
        'payment_reference',
        'notes',
        'processed_by_user_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_sales_paise' => 'integer',
            'commission_deducted_paise' => 'integer',
            'adjustments_paise' => 'integer',
            'net_payout_paise' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SellerSettlementItem::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function getFormattedTotalSalesAttribute(): string
    {
        return Money::format($this->total_sales_paise);
    }

    public function getFormattedCommissionDeductedAttribute(): string
    {
        return Money::format($this->commission_deducted_paise);
    }

    public function getFormattedNetPayoutAttribute(): string
    {
        return Money::format($this->net_payout_paise);
    }
}

