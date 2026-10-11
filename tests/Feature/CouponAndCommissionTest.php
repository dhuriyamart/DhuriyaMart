<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponAndCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicSettings();
    }

    public function test_coupon_discount_applied_server_side_for_fixed_amount(): void
    {
        $seller = $this->createSellerUser('Coupon Seller');
        $category = Category::create(['name' => 'Disc Cat', 'slug' => 'disc-cat', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Shirt Item',
            'slug' => 'shirt-item',
            'base_price_paise' => 100000, // ₹1,000
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'M',
            'sku' => 'SHIRT-M',
            'price_paise' => 100000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Create ₹150 flat discount coupon
        $coupon = Coupon::create([
            'code' => 'FLAT150',
            'discount_type' => 'fixed',
            'discount_value' => 15000, // ₹150.00 in paise
            'min_order_value_paise' => 50000, // min ₹500
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variant->id, 1); // subtotal ₹1,000

        $applyResult = $cartService->applyCoupon($cart, 'FLAT150');
        $this->assertTrue($applyResult['success']);

        $totals = $cartService->calculateCartTotals($cart);
        $this->assertEquals(15000, $totals['discount_paise'], 'Discount must be ₹150.00 (15000 paise)');
        $this->assertEquals(85000, $totals['subtotal_paise'] - $totals['discount_paise']);
    }

    public function test_coupon_rejected_if_below_minimum_order_value(): void
    {
        $seller = $this->createSellerUser('Coupon Seller 2');
        $category = Category::create(['name' => 'Disc Cat 2', 'slug' => 'disc-cat-2', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Accessory Item',
            'slug' => 'accessory-item',
            'base_price_paise' => 20000, // ₹200
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Small',
            'sku' => 'ACC-S',
            'price_paise' => 20000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Requires ₹500 minimum
        Coupon::create([
            'code' => 'BIGBUY',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'min_order_value_paise' => 50000, // min ₹500
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variant->id, 1); // only ₹200

        $applyResult = $cartService->applyCoupon($cart, 'BIGBUY');
        $this->assertFalse($applyResult['success']);
        $this->assertStringContainsString('Minimum order value', $applyResult['message']);
    }

    public function test_percent_coupon_respects_max_discount_cap(): void
    {
        $seller = $this->createSellerUser('Coupon Seller 3');
        $category = Category::create(['name' => 'Disc Cat 3', 'slug' => 'disc-cat-3', 'is_active' => true]);

        $product = Product::create([
            'shop_id' => $seller->shop->id,
            'category_id' => $category->id,
            'name' => 'Laptop Item',
            'slug' => 'laptop-item',
            'base_price_paise' => 5000000, // ₹50,000
            'status' => 'approved',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '16GB',
            'sku' => 'LAP-16',
            'price_paise' => 5000000,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // 20% off, but capped at ₹1,000 (100,000 paise)
        Coupon::create([
            'code' => 'TECH20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'max_discount_paise' => 100000, // ₹1,000 cap
            'min_order_value_paise' => 10000,
            'is_active' => true,
        ]);

        $customer = $this->createCustomerUser();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer);
        $cartService->addItem($cart, $variant->id, 1);

        $cartService->applyCoupon($cart, 'TECH20');
        $totals = $cartService->calculateCartTotals($cart);

        // Without cap, 20% of ₹50,000 would be ₹10,000. With cap, it must be ₹1,000.
        $this->assertEquals(100000, $totals['discount_paise'], 'Discount must be capped at ₹1,000.00');
    }

    public function test_marketplace_commission_calculation(): void
    {
        $commissionService = app(CommissionService::class);

        // Shop with default 10% commission
        $sellerDefault = $this->createSellerUser('Standard Shop');
        $calc = $commissionService->calculateCommission($sellerDefault->shop, 100000); // ₹1,000.00
        $this->assertEquals(10.00, $calc['rate']);
        $this->assertEquals(10000, $calc['amount_paise'], '10% of ₹1,000 is ₹100');
        $this->assertEquals(90000, $calc['net_seller_amount_paise'], 'Net seller amount is ₹900');

        // Shop with custom negotiated commission (15.5%)
        $sellerCustom = $this->createSellerUser('Premium Shop');
        $sellerCustom->shop->update(['commission_rate' => 15.50]);

        $calcCustom = $commissionService->calculateCommission($sellerCustom->shop, 200000); // ₹2,000.00
        $this->assertEquals(15.50, $calcCustom['rate']);
        $this->assertEquals(31000, $calcCustom['amount_paise'], '15.5% of ₹2,000 is ₹310');
        $this->assertEquals(169000, $calcCustom['net_seller_amount_paise'], 'Net seller amount is ₹1,690');
    }
}
