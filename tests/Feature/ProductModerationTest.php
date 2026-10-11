<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_seller_can_create_product_which_enters_admin_moderation_queue(): void
    {
        $category = Category::create([
            'name' => 'Moderation Category',
            'slug' => 'moderation-cat',
            'is_active' => true,
        ]);

        $seller = $this->createSellerUser('Creator Store', 'approved');

        // Seller posts new product
        $response = $this->actingAs($seller)->post('/seller/products', [
            'category_id' => $category->id,
            'name' => 'Ultra Wireless Earbuds',
            'short_description' => 'Noise cancellation earbuds with 40h playtime',
            'description' => 'Detailed product specs and high quality audio drivers.',
            'base_price' => 1999.00,
            'compare_at_price' => 2999.00,
            'sku' => 'SKU-EARBUDS-PRO',
            'variant_name' => 'Matte Black',
            'stock_quantity' => 50,
            'image_url' => 'https://example.com/earbuds.jpg',
        ]);

        $response->assertRedirect('/seller/products');

        // Assert product was created in pending state
        $product = Product::where('slug', 'ultra-wireless-earbuds')->first();
        $this->assertNotNull($product);
        $this->assertEquals('pending', $product->status);
        $this->assertEquals(199900, $product->base_price_paise);
        $this->assertEquals(1, $product->variants()->count());
        $this->assertEquals('SKU-EARBUDS-PRO', $product->variants->first()->sku);
        $this->assertEquals(50, $product->variants->first()->stock_quantity);

        // Assert pending product is NOT visible in public catalog
        $catalogResponse = $this->get('/search?q=Earbuds');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertDontSee('Ultra Wireless Earbuds');

        // Admin approves the product
        $admin = $this->createAdminUser();
        $modResponse = $this->actingAs($admin)->post("/admin/products/{$product->id}/moderate", [
            'status' => 'approved',
        ]);
        $modResponse->assertSessionHas('success');

        // Assert product is now approved
        $this->assertEquals('approved', $product->fresh()->status);

        // Assert audit log was recorded
        $log = AuditLog::where('action', 'product_moderation_approved')->first();
        $this->assertNotNull($log);
        $this->assertEquals($admin->id, $log->user_id);

        // Product is now visible in public catalog search
        $publicResponse = $this->get('/search?q=Earbuds');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Ultra Wireless Earbuds');
    }

    public function test_admin_can_reject_product_with_reason(): void
    {
        $category = Category::create([
            'name' => 'Review Category',
            'slug' => 'review-cat',
            'is_active' => true,
        ]);

        $seller = $this->createSellerUser('Another Store');
        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Duplicate Low Quality Item',
            'slug' => 'duplicate-low-quality-item',
            'base_price_paise' => 19900,
            'status' => 'pending',
        ]);

        $admin = $this->createAdminUser();
        $this->actingAs($admin)->post("/admin/products/{$product->id}/moderate", [
            'status' => 'rejected',
            'rejection_reason' => 'Incomplete specifications and trademark conflict',
        ]);

        $this->assertEquals('rejected', $product->fresh()->status);
        $this->assertEquals('Incomplete specifications and trademark conflict', $product->fresh()->rejection_reason);
    }
}

