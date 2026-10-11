<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getSubtotalPaiseAttribute(): int
    {
        return (int) $this->items->sum(fn (CartItem $item) => $item->quantity * $item->price_paise);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return Money::format($this->subtotal_paise);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getShopsAttribute(): Collection
    {
        return $this->items->map(fn ($item) => $item->variant?->product?->shop)
            ->filter()
            ->unique('id');
    }
}
