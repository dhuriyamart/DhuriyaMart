<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'store_id',
        'order_type',
        'status',
        'payment_status',
        'payment_method',
        'currency',
        'subtotal_paise',
        'discount_paise',
        'shipping_fee_paise',
        'tax_paise',
        'total_amount_paise',
        'coupon_code',
        'customer_notes',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_paise' => 'integer',
            'discount_paise' => 'integer',
            'shipping_fee_paise' => 'integer',
            'tax_paise' => 'integer',
            'total_amount_paise' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function exitVerification(): HasOne
    {
        return $this->hasOne(ExitVerification::class);
    }

    public function posSession(): HasOne
    {
        return $this->hasOne(PosSession::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return Money::format($this->total_amount_paise);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return Money::format($this->subtotal_paise);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return Money::format($this->discount_paise);
    }

    public function getFormattedShippingAttribute(): string
    {
        return Money::format($this->shipping_fee_paise);
    }

    public function getFormattedTaxAttribute(): string
    {
        return Money::format($this->tax_paise);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeCancelled(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed', 'refunded'])) {
            return false;
        }

        return $this->sellerOrders->every(fn ($so) => in_array($so->status, ['pending', 'confirmed', 'processing']));
    }
}
