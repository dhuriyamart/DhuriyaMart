<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Database\Seeders\KiranaHardwareSeeder;
use Database\Seeders\VillageSmartMartSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KiranaAndHardwareExclusivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_only_kirana_and_hardware_categories_exist(): void
    {
        $allowedSlugs = [
            'kirana-groceries',
            'grains-flour-pulses',
            'oils-sugar-salt-spices',
            'packaged-food-snacks',
            'cleaning-personal-care',
            'hardware-tools',
            'screws-nails-fittings',
            'plumbing-sanitary',
            'electrical-goods',
            'paint-adhesives',
            'hand-tools-construction',
        ];

        $allCategories = Category::all();
        $this->assertCount(11, $allCategories);

        foreach ($allCategories as $category) {
            $this->assertContains(
                $category->slug,
                $allowedSlugs,
                "Unexpected non-kirana / non-hardware category slug found: {$category->slug}"
            );
        }

        // Verify prohibited categories do NOT exist
        $prohibitedSlugs = [
            'electronics',
            'audio-headphones',
            'smartwatches-wearables',
            'fashion-apparel',
            'ethnic-wear',
            'health-wellness',
            'home-kitchen',
        ];

        foreach ($prohibitedSlugs as $slug) {
            $this->assertNull(
                Category::where('slug', $slug)->first(),
                "Prohibited category found in database: {$slug}"
            );
        }
    }

    public function test_catalog_contains_only_kirana_and_hardware_products(): void
    {
        $totalProducts = Product::count();
        // 48 Kirana products + 66 Hardware products = 114 products
        $this->assertEquals(114, $totalProducts, "Product count should be exactly 114 (48 Kirana + 66 Hardware).");

        $prohibitedProductNames = [
            'Noise Pulse',
            'Boat Rockerz',
            'Chanderi',
            'Ceramic Dinner',
            'Teakwood',
            'Vedic Honey',
        ];

        foreach ($prohibitedProductNames as $name) {
            $found = Product::where('name', 'like', "%{$name}%")->first();
            $this->assertNull($found, "Found prohibited dummy product: {$name}");
        }
    }

    public function test_only_kirana_and_hardware_shops_exist(): void
    {
        $shops = Shop::all();
        $allowedShopSlugs = ['dhuriya-kirana-mart', 'dhuriya-hardware-tools'];

        foreach ($shops as $shop) {
            $this->assertContains(
                $shop->slug,
                $allowedShopSlugs,
                "Unexpected shop found: {$shop->slug}"
            );
        }

        $this->assertNull(Shop::where('slug', 'technova-electronics')->first());
        $this->assertNull(Shop::where('slug', 'vedic-roots')->first());
    }

    public function test_storefront_homepage_renders_kirana_and_hardware(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Assert Kirana and Hardware categories and labels are visible
        $response->assertSee('Kirana');
        $response->assertSee('Hardware');
        $response->assertSee('Grains, Flour');
        $response->assertSee('Dhuriya Hardware');

        // Assert obsolete category links do NOT exist on the homepage
        $response->assertDontSee('/categories/electronics');
        $response->assertDontSee('/categories/fashion-apparel');
        $response->assertDontSee('/categories/health-wellness');
        $response->assertDontSee('/categories/home-kitchen');
    }
}

