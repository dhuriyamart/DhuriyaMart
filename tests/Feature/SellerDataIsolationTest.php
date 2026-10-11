<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerOrder;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_seller_cannot_view_or_modify_another_sellers_products(): void
    {
        $category = Category::create([
            'name' => 'Isolation Cat',
            'slug' => 'isolation-cat',
            'is_active' => true,
        ]);

        $sellerA = $this->createSellerUser('Seller Alpha');
        $sellerB = $this->createSellerUser('Seller Beta');

        // Product belonging to Seller B
        $productB = Product::create([
            'shop_id' => $sellerB->shop->id,
            'category_id' => $category->id,
            'name' => 'Seller B Secret Gadget',
            'slug' => 'seller-b-secret-gadget',
            'base_price_paise' => 49900,
            'status' => 'approved',
        ]);

        // Seller A tries to edit Seller B's product
        $this->actingAs($sellerA)
            ->get("/seller/products/{$productB->id}/edit")
            ->assertStatus(404);

        // Seller A tries to update Seller B's product
        $this->actingAs($sellerA)
            ->put("/seller/products/{$productB->id}", [
                'category_id' => $category->id,
                'name' => 'Hacked Product Name',
                'short_description' => 'Hacked',
                'description' => 'Hacked description',
                'base_price' => 10,
            ])
            ->assertStatus(404);

        // Seller A tries to delete Seller B's product
        $this->actingAs($sellerA)
            ->delete("/seller/products/{$productB->id}")
            ->assertStatus(404);

        // Assert product is untouched
        $this->assertEquals('Seller B Secret Gadget', $productB->fresh()->name);
    }

    public function test_seller_cannot_view_or_dispatch_another_sellers_orders(): void
    {
        $category = Category::create([
            'name' => 'Order Cat',
            'slug' => 'order-cat',
            'is_active' => true,
        ]);

        $sellerA = $this->createSellerUser('Vendor X');
        $sellerB = $this->createSellerUser('Vendor Y');

        $productB = Product::create([
            'shop_id' => $sellerB->shop->id,
            'category_id' => $category->id,
            'name' => 'Vendor Y Item',
            'slug' => 'vendor-y-item',
            'base_price_paise' => 120000,
            'status' => 'approved',
        ]);
        $variantB = ProductVariant::create([
            'product_id' => $productB->id,
            'name' => 'Default',
            'sku' => 'SKU-VEND-Y',
            'price_paise' => 120000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variantB->id, 1);

        // Checkout creates order for Seller B
        $this->actingAs($customer)->post('/checkout', [
            'address_id' => $customer->addresses->first()->id,
            'payment_method' => 'cod',
        ]);

        $sellerOrderB = SellerOrder::where('shop_id', $sellerB->shop->id)->first();
        $this->assertNotNull($sellerOrderB);

        // Seller A tries to view Seller B's order
        $this->actingAs($sellerA)
            ->get("/seller/orders/{$sellerOrderB->id}")
            ->assertStatus(404);

        // Seller A tries to dispatch Seller B's order
        $this->actingAs($sellerA)
            ->post("/seller/orders/{$sellerOrderB->id}/dispatch", [
                'courier_name' => 'BlueDart',
                'tracking_number' => 'BLUEDART12345',
            ])
            ->assertStatus(404);

        // Order status must remain confirmed/pending
        $this->assertNotEquals('dispatched', $sellerOrderB->fresh()->status);
    }
}

