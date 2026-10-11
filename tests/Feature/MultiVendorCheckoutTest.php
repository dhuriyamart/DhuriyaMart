<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\SellerOrder;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiVendorCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_multi_vendor_cart_splits_into_parent_and_seller_sub_orders(): void
    {
        // 1. Create Category
        $category = Category::create([
            'name' => 'General Merchandise',
            'slug' => 'general-merchandise',
            'is_active' => true,
        ]);

        // 2. Create Seller A with Product A
        $sellerA = $this->createSellerUser('Shop Alpha');
        $productA = Product::create([
            'shop_id' => $sellerA->shop->id,
            'category_id' => $category->id,
            'name' => 'Alpha Gadget',
            'slug' => 'alpha-gadget',
            'base_price_paise' => 100000, // ₹1,000.00
            'status' => 'approved',
        ]);
        $variantA = ProductVariant::create([
            'product_id' => $productA->id,
            'name' => 'Default',
            'sku' => 'SKU-ALPHA-1',
            'price_paise' => 100000,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        // 3. Create Seller B with Product B
        $sellerB = $this->createSellerUser('Shop Beta');
        $productB = Product::create([
            'shop_id' => $sellerB->shop->id,
            'category_id' => $category->id,
            'name' => 'Beta Organic Honey',
            'slug' => 'beta-organic-honey',
            'base_price_paise' => 50000, // ₹500.00
            'status' => 'approved',
        ]);
        $variantB = ProductVariant::create([
            'product_id' => $productB->id,
            'name' => '500g Jar',
            'sku' => 'SKU-BETA-1',
            'price_paise' => 50000,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        // 4. Create Customer & add both items to cart
        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);

        $cartService->addItem($cart, $variantA->id, 2); // 2 * ₹1,000 = ₹2,000
        $cartService->addItem($cart, $variantB->id, 1); // 1 * ₹500 = ₹500

        $this->assertEquals(2, $cart->items()->count());

        // 5. Checkout via Cash on Delivery
        $response = $this->actingAs($customer)->post('/checkout', [
            'address_id' => $customer->addresses->first()->id,
            'payment_method' => 'cod',
            'customer_notes' => 'Please ring the doorbell',
        ]);

        // Check redirect to confirmation
        $response->assertStatus(302);

        // 6. Assert Order Structure
        $orders = Order::where('user_id', $customer->id)->get();
        $this->assertCount(1, $orders, 'Exactly 1 parent customer order must be created');

        $parentOrder = $orders->first();
        $this->assertNotNull($parentOrder->order_number);
        $this->assertEquals('cod', $parentOrder->payment_method);
        $this->assertEquals('pending', $parentOrder->payment_status);

        // Assert Seller Orders
        $sellerOrders = $parentOrder->sellerOrders;
        $this->assertCount(2, $sellerOrders, 'Cart with 2 distinct shops must create exactly 2 seller orders');

        $sellerOrderA = $sellerOrders->firstWhere('shop_id', $sellerA->shop->id);
        $sellerOrderB = $sellerOrders->firstWhere('shop_id', $sellerB->shop->id);

        $this->assertNotNull($sellerOrderA, 'Shop Alpha must have a seller order');
        $this->assertNotNull($sellerOrderB, 'Shop Beta must have a seller order');

        // Verify Shop A details
        $this->assertEquals(200000, $sellerOrderA->subtotal_paise, 'Shop Alpha subtotal must be ₹2,000.00');
        $this->assertEquals(1, $sellerOrderA->items()->count());
        $this->assertEquals(2, $sellerOrderA->items->first()->quantity);
        $this->assertEquals($variantA->id, $sellerOrderA->items->first()->product_variant_id);

        // Verify Shop B details
        $this->assertEquals(50000, $sellerOrderB->subtotal_paise, 'Shop Beta subtotal must be ₹500.00');
        $this->assertEquals(1, $sellerOrderB->items()->count());
        $this->assertEquals(1, $sellerOrderB->items->first()->quantity);
        $this->assertEquals($variantB->id, $sellerOrderB->items->first()->product_variant_id);

        // Verify inventory was decremented
        $this->assertEquals(18, $variantA->fresh()->stock_quantity);
        $this->assertEquals(14, $variantB->fresh()->stock_quantity);

        // Verify customer cart is cleared
        $this->assertEquals(0, $cart->fresh()->items()->count());
    }
}

