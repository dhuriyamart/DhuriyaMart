<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_code',
        'store_id',
        'order_id',
        'pos_session_id',
        'verifier_id',
        'mode',
        'status',
        'total_billed_items',
        'total_scanned_items',
        'scanned_items',
        'discrepancy_details',
        'notes',
        'verified_at',
    ];

    protected $casts = [
        'scanned_items' => 'array',
        'discrepancy_details' => 'array',
        'verified_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    public function hasDiscrepancy(): bool
    {
        return $this->status === 'discrepancy_flagged';
    }
}
