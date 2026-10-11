<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\KiranaHardwareSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPriceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_admin_can_view_price_monitoring_and_opening_stock_budget_dashboard(): void
    {
        // Run Kirana & Hardware Seeder
        $this->seed(KiranaHardwareSeeder::class);

        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/price-monitoring');

        $response->assertStatus(200);

        // Assert Official October 2026 Price Benchmarks are present
        $response->assertSee('Price Monitoring System');
        $response->assertSee('37.72'); // Atta official avg
        $response->assertSee('45.81'); // Rice official avg
        $response->assertSee('127.81'); // Arhar dal official avg
        $response->assertSee('55.43'); // Sugar official avg

        // Assert ₹10 Lakh Business Plan allocations are present
        $response->assertSee('₹10 Lakh Comprehensive Business Plan');
        $response->assertSee('Kirana Opening Stock');
        $response->assertSee('Hardware Opening Stock');
        $response->assertSee('Shop Fixtures & POS Billing Setup');
        $response->assertSee('Working Capital Cash Reserve');

        // Assert Kirana and Hardware products are in the list
        $response->assertSee('Wheat Aata');
        $response->assertSee('Iron Nails');
        $response->assertSee('LED Bulb');
    }

    public function test_guest_and_customer_cannot_access_price_monitoring_dashboard(): void
    {
        $this->get('/admin/price-monitoring')->assertRedirect('/login');

        $customer = $this->createCustomerUser();
        $this->actingAs($customer)->get('/admin/price-monitoring')->assertStatus(403);
    }
}

