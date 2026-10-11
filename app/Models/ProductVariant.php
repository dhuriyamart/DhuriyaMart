<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'attributes',
        'price_paise',
        'compare_at_price_paise',
        'cost_price_paise',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'barcode',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price_paise' => 'integer',
            'compare_at_price_paise' => 'integer',
            'cost_price_paise' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getPriceRupeesAttribute(): float
    {
        return round($this->price_paise / 100, 2);
    }

    public function getCostPriceRupeesAttribute(): float
    {
        return round(($this->cost_price_paise ?? 0) / 100, 2);
    }

    public function getCompareAtPriceRupeesAttribute(): ?float
    {
        return $this->compare_at_price_paise ? round($this->compare_at_price_paise / 100, 2) : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return Money::format($this->price_paise);
    }

    public function getFormattedComparePriceAttribute(): ?string
    {
        return $this->compare_at_price_paise ? Money::format($this->compare_at_price_paise) : null;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->compare_at_price_paise && $this->compare_at_price_paise > $this->price_paise) {
            $diff = $this->compare_at_price_paise - $this->price_paise;

            return (int) round(($diff / $this->compare_at_price_paise) * 100);
        }

        return 0;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
