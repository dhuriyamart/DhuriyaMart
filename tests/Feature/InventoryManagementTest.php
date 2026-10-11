<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_deduct_stock_reduces_inventory_and_logs_movement(): void
    {
        $seller = $this->createSellerUser('Stock Test Seller');
        $category = Category::create(['name' => 'Stock Cat', 'slug' => 'stock-cat', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Stocked Item',
            'slug' => 'stocked-item',
            'base_price_paise' => 10000,
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'STK-001',
            'price_paise' => 10000,
            'stock_quantity' => 25,
            'is_active' => true,
        ]);

        $inventoryService = app(InventoryService::class);
        $movement = $inventoryService->deductStock($variant, 5, 'sale', 'ORD-1234');

        $this->assertEquals(20, $variant->fresh()->stock_quantity);
        $this->assertEquals(-5, $movement->quantity_change);
        $this->assertEquals(20, $movement->stock_after);
        $this->assertEquals('sale', $movement->type);
    }

    public function test_prevent_negative_inventory_on_insufficient_stock(): void
    {
        $seller = $this->createSellerUser('Stock Seller 2');
        $category = Category::create(['name' => 'Stock Cat 2', 'slug' => 'stock-cat-2', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Limited Edition Item',
            'slug' => 'limited-edition-item',
            'base_price_paise' => 50000,
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Rare Edition',
            'sku' => 'RARE-001',
            'price_paise' => 50000,
            'stock_quantity' => 3, // only 3 available
            'is_active' => true,
        ]);

        $inventoryService = app(InventoryService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');

        // Attempting to buy 5 units when only 3 are in stock
        $inventoryService->deductStock($variant, 5, 'sale');

        // Stock must remain 3
        $this->assertEquals(3, $variant->fresh()->stock_quantity);
    }

    public function test_cannot_manually_adjust_stock_to_negative(): void
    {
        $seller = $this->createSellerUser('Stock Seller 3');
        $category = Category::create(['name' => 'Stock Cat 3', 'slug' => 'stock-cat-3', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Adjustable Item',
            'slug' => 'adjustable-item',
            'base_price_paise' => 10000,
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'ADJ-001',
            'price_paise' => 10000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $inventoryService = app(InventoryService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stock quantity cannot be negative');

        $inventoryService->adjustStock($variant, -5);
    }

    public function test_cancelling_seller_order_restores_inventory(): void
    {
        $seller = $this->createSellerUser('Restorer Shop');
        $category = Category::create(['name' => 'Restorer Cat', 'slug' => 'restorer-cat', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Restorable Goods',
            'slug' => 'restorable-goods',
            'base_price_paise' => 10000,
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'RESTORE-001',
            'price_paise' => 10000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variant->id, 4);

        // Place order
        $this->actingAs($customer)->post('/checkout', [
            'address_id' => $customer->addresses->first()->id,
            'payment_method' => 'cod',
        ]);

        // Stock decreased to 6
        $this->assertEquals(6, $variant->fresh()->stock_quantity);

        $sellerOrder = $seller->shop->sellerOrders()->first();
        $this->assertNotNull($sellerOrder);

        // Seller cancels order
        $orderService = app(OrderService::class);
        $orderService->cancelSellerOrder($sellerOrder, 'Customer requested change', $seller);

        // Stock restored to 10
        $this->assertEquals(10, $variant->fresh()->stock_quantity);
        $this->assertEquals('cancelled', $sellerOrder->fresh()->status);
    }
}
