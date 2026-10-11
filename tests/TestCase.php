<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedBasicSettings(): void
    {
        \App\Models\Setting::set('site_name', 'DhuriyaMart');
        \App\Models\Setting::set('site_currency', 'INR');
        \App\Models\Setting::set('default_commission_rate', '10.00');
        \App\Models\Setting::set('free_shipping_threshold_paise', '49900');
        \App\Models\Setting::set('standard_shipping_fee_paise', '4900');
        \App\Models\Setting::set('tax_rate_percent', '5.00');
        \App\Models\Setting::set('payment_sandbox_mode', '1');
        \App\Models\Setting::set('cod_enabled', '1');
    }

    protected function createAdminUser(): \App\Models\User
    {
        $admin = \App\Models\User::factory()->create([
            'email' => 'admin_' . uniqid() . '@dhuriyamart.com',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');
        return $admin->fresh(['roles']);
    }

    protected function createSellerUser(string $shopName = 'Test Shop', string $status = 'approved'): \App\Models\User
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'seller_' . uniqid() . '@dhuriyamart.com',
            'status' => 'active',
        ]);
        $user->assignRole('seller');

        $slug = \Illuminate\Support\Str::slug($shopName . '-' . uniqid());
        $shop = \App\Models\Shop::create([
            'user_id' => $user->id,
            'name' => $shopName,
            'slug' => $slug,
            'email' => 'contact_' . uniqid() . '@seller.com',
            'status' => $status,
            'commission_rate' => 10.00,
        ]);

        \App\Models\SellerProfile::create([
            'shop_id' => $shop->id,
            'gstin' => '29ABCDE1234F1Z5',
            'pan_number' => 'ABCDE1234F',
            'bank_account_name' => $shopName,
            'bank_account_number' => '123456789012',
            'bank_ifsc' => 'HDFC0001234',
            'bank_name' => 'HDFC Bank',
            'pickup_address' => 'MG Road, Bengaluru, Karnataka - 560001',
            'verified_at' => $status === 'approved' ? now() : null,
        ]);

        return $user->fresh(['shop.profile', 'roles']);
    }

    protected function createCustomerUser(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'customer_' . uniqid() . '@example.com',
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        \App\Models\Address::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => '9876543210',
            'address_line1' => 'Flat 402, Lotus Apartments',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'type' => 'home',
            'is_default' => true,
        ]);

        return $user->fresh(['addresses', 'roles']);
    }
}

