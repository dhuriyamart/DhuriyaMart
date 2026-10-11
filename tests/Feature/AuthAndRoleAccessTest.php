<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_guest_can_view_public_storefront(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $responseCatalog = $this->get('/search');
        $responseCatalog->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        $this->get('/account')->assertRedirect('/login');
        $this->get('/seller/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin_or_seller_areas(): void
    {
        $customer = $this->createCustomerUser();

        $this->actingAs($customer)
            ->get('/admin/dashboard')
            ->assertStatus(403);

        $this->actingAs($customer)
            ->get('/seller/dashboard')
            ->assertStatus(403);
    }

    public function test_customer_can_access_account_pages(): void
    {
        $customer = $this->createCustomerUser();

        $this->actingAs($customer)
            ->get('/account/orders')
            ->assertStatus(200);

        $this->actingAs($customer)
            ->get('/account/addresses')
            ->assertStatus(200);
    }

    public function test_approved_seller_can_access_seller_portal(): void
    {
        $seller = $this->createSellerUser('Approved Kirana & Hardware Hub', 'approved');

        $this->actingAs($seller)
            ->get('/seller/dashboard')
            ->assertStatus(200);

        $this->actingAs($seller)
            ->get('/seller/products')
            ->assertStatus(200);

        // Seller cannot access admin portal
        $this->actingAs($seller)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_pending_seller_is_blocked_from_restricted_actions(): void
    {
        $pendingSeller = $this->createSellerUser('Pending Seller Store', 'pending');

        // Products creation requires approved status
        $this->actingAs($pendingSeller)
            ->get('/seller/products/create')
            ->assertSessionHas('error');
    }

    public function test_admin_can_access_admin_modules(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/sellers')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/audit-logs')
            ->assertStatus(200);
    }
}

