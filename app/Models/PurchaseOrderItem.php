<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'quantity_ordered',
        'quantity_received',
        'unit_cost_paise',
        'total_cost_paise',
        'status',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getUnitCostRupeesAttribute(): float
    {
        return round($this->unit_cost_paise / 100, 2);
    }

    public function getTotalCostRupeesAttribute(): float
    {
        return round($this->total_cost_paise / 100, 2);
    }
}
