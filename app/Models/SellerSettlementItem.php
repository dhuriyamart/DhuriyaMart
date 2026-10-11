<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerSettlementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_settlement_id',
        'seller_order_id',
        'gross_amount_paise',
        'commission_amount_paise',
        'net_amount_paise',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount_paise' => 'integer',
            'commission_amount_paise' => 'integer',
            'net_amount_paise' => 'integer',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(SellerSettlement::class, 'seller_settlement_id');
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function getFormattedGrossAttribute(): string
    {
        return Money::format($this->gross_amount_paise);
    }

    public function getFormattedCommissionAttribute(): string
    {
        return Money::format($this->commission_amount_paise);
    }

    public function getFormattedNetAttribute(): string
    {
        return Money::format($this->net_amount_paise);
    }
}
