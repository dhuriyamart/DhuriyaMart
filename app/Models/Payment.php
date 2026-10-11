<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'provider',
        'amount_paise',
        'currency',
        'status',
        'gateway_order_id',
        'signature',
        'error_code',
        'error_message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount_paise);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}

