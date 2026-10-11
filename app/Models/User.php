<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'customer_id',
        'qr_token',
        'name',
        'email',
        'phone',
        'village_name',
        'password',
        'status',
        'avatar',
        'wallet_balance_paise',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first()
            ?? $this->addresses()->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function inWishlist(int $variantId): bool
    {
        return $this->wishlists()->where('product_variant_id', $variantId)->exists();
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasRole(string|array $role): bool
    {
        $roles = is_array($role) ? $role : [$role];

        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'admin']);
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function isSupport(): bool
    {
        return $this->hasRole(['super-admin', 'admin', 'support-staff']);
    }

    public function isFinance(): bool
    {
        return $this->hasRole(['super-admin', 'admin', 'finance-staff']);
    }

    public function assignRole(string $slug): void
    {
        $role = Role::firstOrCreate(['slug' => $slug], [
            'name' => ucwords(str_replace('-', ' ', $slug)),
        ]);
        if (! $this->roles->contains($role->id)) {
            $this->roles()->attach($role->id);
        }
    }

    public function posSessions(): HasMany
    {
        return $this->hasMany(PosSession::class, 'customer_id');
    }

    public function cashierSessions(): HasMany
    {
        return $this->hasMany(PosSession::class, 'cashier_id');
    }

    public function managedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'manager_id');
    }

    public function getWalletBalanceRupeesAttribute(): float
    {
        return round(($this->wallet_balance_paise ?? 0) / 100, 2);
    }

    public function ensureCustomerIdentification(): void
    {
        $updated = false;
        if (empty($this->customer_id)) {
            $this->customer_id = 'DM-CUST-'.str_pad($this->id, 5, '0', STR_PAD_LEFT);
            $updated = true;
        }
        if (empty($this->qr_token)) {
            $this->qr_token = 'DMQR-'.strtoupper(substr(hash('sha256', $this->id.$this->email.'dhuriyamart_token'), 0, 12));
            $updated = true;
        }
        if ($updated) {
            $this->save();
        }
    }
}
