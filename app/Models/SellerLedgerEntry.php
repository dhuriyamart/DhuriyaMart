<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'type',
        'amount_paise',
        'balance_after_paise',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'balance_after_paise' => 'integer',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount_paise);
    }

    public function getFormattedBalanceAfterAttribute(): string
    {
        return Money::format($this->balance_after_paise);
    }
}

