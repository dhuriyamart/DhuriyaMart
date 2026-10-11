<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'store_id',
        'supplier_id',
        'created_by_id',
        'approved_by_id',
        'status',
        'total_cost_paise',
        'notes',
        'expected_delivery_date',
        'received_at',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getTotalCostRupeesAttribute(): float
    {
        return round($this->total_cost_paise / 100, 2);
    }

    public function recalculateTotal(): void
    {
        $this->total_cost_paise = (int) $this->items()->sum('total_cost_paise');
        $this->save();
    }
}

