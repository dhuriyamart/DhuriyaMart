<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'opened_by_id',
        'closed_by_id',
        'register_date',
        'opening_float_paise',
        'cash_sales_paise',
        'cash_in_paise',
        'cash_out_paise',
        'expected_cash_paise',
        'closing_actual_paise',
        'difference_paise',
        'status',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'register_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CashRegisterEntry::class);
    }

    public function getOpeningFloatRupeesAttribute(): float
    {
        return round($this->opening_float_paise / 100, 2);
    }

    public function getCashSalesRupeesAttribute(): float
    {
        return round($this->cash_sales_paise / 100, 2);
    }

    public function getCashInRupeesAttribute(): float
    {
        return round($this->cash_in_paise / 100, 2);
    }

    public function getCashOutRupeesAttribute(): float
    {
        return round($this->cash_out_paise / 100, 2);
    }

    public function getExpectedCashRupeesAttribute(): float
    {
        return round($this->expected_cash_paise / 100, 2);
    }

    public function getClosingActualRupeesAttribute(): ?float
    {
        return $this->closing_actual_paise !== null ? round($this->closing_actual_paise / 100, 2) : null;
    }

    public function getDifferenceRupeesAttribute(): float
    {
        return round($this->difference_paise / 100, 2);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function recalculateExpected(): void
    {
        $this->expected_cash_paise = $this->opening_float_paise + $this->cash_sales_paise + $this->cash_in_paise - $this->cash_out_paise;
        if ($this->closing_actual_paise !== null) {
            $this->difference_paise = (int)$this->closing_actual_paise - (int)$this->expected_cash_paise;
        }
    }
}

