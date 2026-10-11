<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_order_id',
        'product_id',
        'product_variant_id',
        'shop_id',
        'product_name',
        'variant_name',
        'sku',
        'price_paise',
        'quantity',
        'subtotal_paise',
        'tax_paise',
        'total_paise',
        'commission_rate',
        'commission_amount_paise',
    ];

    protected function casts(): array
    {
        return [
            'price_paise' => 'integer',
            'quantity' => 'integer',
            'subtotal_paise' => 'integer',
            'tax_paise' => 'integer',
            'total_paise' => 'integer',
            'commission_rate' => 'decimal:2',
            'commission_amount_paise' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
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
