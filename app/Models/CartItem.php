<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'price_paise',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_paise' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getTotalPaiseAttribute(): int
    {
        return $this->quantity * $this->price_paise;
    }

    public function getFormattedPriceAttribute(): string
    {
        return Money::format($this->price_paise);
    }

    public function getFormattedTotalAttribute(): string
    {
        return Money::format($this->total_paise);
    }
}

