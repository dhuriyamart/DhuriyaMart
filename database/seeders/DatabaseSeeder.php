<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['slug' => 'super-admin', 'name' => 'Super Admin', 'description' => 'Complete platform control'],
            ['slug' => 'admin', 'name' => 'Admin', 'description' => 'Platform operations and moderation'],
            ['slug' => 'seller', 'name' => 'Seller', 'description' => 'Shop and product management'],
            ['slug' => 'customer', 'name' => 'Customer', 'description' => 'Marketplace shopper'],
            ['slug' => 'support-staff', 'name' => 'Support Staff', 'description' => 'Customer and order assistance'],
            ['slug' => 'finance-staff', 'name' => 'Finance Staff', 'description' => 'Reconciliation and settlements'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }

        // 2. Default Seed Users
        $defaultPassword = Hash::make('password123');

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@dhuriyamart.com'],
            ['name' => 'Super Administrator', 'password' => $defaultPassword, 'phone' => '+91 9876500001', 'status' => 'active']
        );
        $superAdmin->assignRole('super-admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@dhuriyamart.com'],
            ['name' => 'Platform Administrator', 'password' => $defaultPassword, 'phone' => '+91 9876500002', 'status' => 'active']
        );
        $admin->assignRole('admin');

        $seller1User = User::firstOrCreate(
            ['email' => 'seller1@dhuriyamart.com'],
            ['name' => 'Ramesh Kumar (Kirana Merchant)', 'password' => $defaultPassword, 'phone' => '+91 9876500003', 'status' => 'active']
        );
        $seller1User->assignRole('seller');

        $seller2User = User::firstOrCreate(
            ['email' => 'seller2@dhuriyamart.com'],
            ['name' => 'Suresh Verma (Hardware Merchant)', 'password' => $defaultPassword, 'phone' => '+91 9876500004', 'status' => 'active']
        );
        $seller2User->assignRole('seller');

        $customerUser = User::firstOrCreate(
            ['email' => 'customer@dhuriyamart.com'],
            ['name' => 'Rahul Verma', 'password' => $defaultPassword, 'phone' => '+91 9876500005', 'status' => 'active']
        );
        $customerUser->assignRole('customer');

        $supportUser = User::firstOrCreate(
            ['email' => 'support@dhuriyamart.com'],
            ['name' => 'Pooja Nair', 'password' => $defaultPassword, 'phone' => '+91 9876500006', 'status' => 'active']
        );
        $supportUser->assignRole('support-staff');

        $financeUser = User::firstOrCreate(
            ['email' => 'finance@dhuriyamart.com'],
            ['name' => 'Sanjay Mehta', 'password' => $defaultPassword, 'phone' => '+91 9876500007', 'status' => 'active']
        );
        $financeUser->assignRole('finance-staff');

        // Customer addresses
        Address::firstOrCreate(
            ['user_id' => $customerUser->id, 'address_line1' => 'Flat 402, Lotus Residency, MG Road'],
            [
                'type' => 'shipping',
                'name' => 'Rahul Verma',
                'phone' => '+91 9876500005',
                'address_line2' => 'Near Metro Station',
                'landmark' => 'Apollo Pharmacy',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'country' => 'India',
                'is_default' => true,
            ]
        );

        // 3. Kirana & Hardware Opening Stock Catalogs (Exclusively Kirana & Hardware)
        $this->call(KiranaHardwareSeeder::class);

        // 4. Village Smart Mart Infrastructure
        $this->call(VillageSmartMartSeeder::class);

        // 5. Promotional Banners (Strictly Kirana & Hardware Exclusive)
        $banners = [
            [
                'title' => 'DhuriyaMart Kirana & Groceries Wholesale Mart',
                'subtitle' => 'Best Rates on Aata, Rice, Dals, Edible Oils, Spices & Household Essentials',
                'image_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&auto=format&fit=crop&q=80',
                'link_url' => '/categories/kirana-groceries',
                'position' => 'hero_main',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Dhuriya Hardware, Tools & Electricals',
                'subtitle' => 'Quality plumbing pipes, hand tools, paints, LED lighting & fasteners for home and construction.',
                'image_url' => 'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=1600&auto=format&fit=crop&q=80',
                'link_url' => '/categories/hardware-tools',
                'position' => 'hero_main',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Smart Village Mart Kiosk & Self-Checkout',
                'subtitle' => 'Fast digital billing, customer QR cards, cash desk and verified exit at your local store.',
                'image_url' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=800&auto=format&fit=crop&q=80',
                'link_url' => '/pos/kiosk',
                'position' => 'hero_side',
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($banners as $b) {
            Banner::firstOrCreate(['title' => $b['title']], $b);
        }

        // 6. Informational & Legal Pages
        $pages = [
            [
                'title' => 'About DhuriyaMart',
                'slug' => 'about-us',
                'meta_title' => 'About DhuriyaMart - All-in-One Automated Village Smart Mart',
                'meta_description' => 'DhuriyaMart: Kirana groceries and hardware & tools network for rural communities.',
                'content' => '<h2>Welcome to DhuriyaMart</h2><p>DhuriyaMart is an automated, all-in-one Smart Mart network designed for rural and suburban communities. We provide high-quality everyday Kirana groceries, staples, home cleaning essentials, alongside reliable hardware, plumbing fittings, electrical supplies, paints, and tools under a single trusted roof.</p><h3>Our Promise</h3><ul><li><strong>100% Authentic Quality:</strong> Fresh grains, certified pulses, pure edible oils, and durable industrial-grade hardware directly from authorized wholesale distributors.</li><li><strong>Transparent Village Pricing:</strong> Competitive wholesale and retail rates matching district market price monitoring benchmarks.</li><li><strong>Smart Hybrid Retail:</strong> Self-service Kiosk ordering, instant QR customer cards, assisted POS billing, and verified checkout.</li></ul>',
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms',
                'meta_title' => 'Terms of Service - DhuriyaMart',
                'meta_description' => 'Read our marketplace conditions and terms of service.',
                'content' => '<h2>Marketplace Terms & Conditions</h2><p>By using DhuriyaMart, you agree to comply with our platform terms. All transactions conducted on DhuriyaMart are in Indian Rupees (INR ₹) and are governed by Indian consumer laws and the Information Technology Act.</p>',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'meta_title' => 'Privacy Policy - DhuriyaMart',
                'meta_description' => 'How DhuriyaMart handles and safeguards your personal information.',
                'content' => '<h2>Privacy Policy</h2><p>DhuriyaMart values your privacy. We never sell your personal data or credit information to third parties. We strictly use your contact and delivery coordinates for order fulfillment, shipment tracking, and customer support.</p>',
            ],
            [
                'title' => 'Shipping & Delivery Policy',
                'slug' => 'shipping-policy',
                'meta_title' => 'Shipping & Delivery Information - DhuriyaMart',
                'meta_description' => 'Shipping rates, dispatch times, and courier partners across India.',
                'content' => '<h2>Shipping & Delivery</h2><p>All in-stock orders are packed and dispatched by our sellers within 24 to 48 business hours. We partner with reliable courier networks including Blue Dart, Delhivery, Ekart, and India Post. Standard delivery takes 3 to 6 business days across Tier 1, 2, and 3 Indian pincodes. Free standard shipping applies to orders over ₹499.</p>',
            ],
            [
                'title' => 'Cancellation & Returns Policy',
                'slug' => 'returns-policy',
                'meta_title' => '7-Day Return and Refund Policy - DhuriyaMart',
                'meta_description' => 'Learn how easy it is to request returns or cancellations on DhuriyaMart.',
                'content' => '<h2>7-Day Easy Returns & Cancellation</h2><p>You can cancel an order anytime before it has been dispatched by the respective seller. Eligible items can be returned within 7 days of delivery if they are defective, damaged in transit, or differ from their catalog description. Refunds are processed back to the original payment source within 3 to 5 business days after inspection.</p>',
            ],
            [
                'title' => 'Frequently Asked Questions (FAQs)',
                'slug' => 'faq',
                'meta_title' => 'Frequently Asked Questions - DhuriyaMart',
                'meta_description' => 'Quick answers to your orders, payments, seller onboarding, and tracking questions.',
                'content' => '<h2>Frequently Asked Questions</h2><h4>Can I order Kirana and Hardware together?</h4><p>Yes! DhuriyaMart lets you order grocery essentials and hardware repair supplies in a unified checkout with transparent itemized bills.</p><h4>How does the Village Mart Kiosk work?</h4><p>Visit your nearest village store, scan your customer QR card or enter your phone number, select items on the touchscreen kiosk or scan barcodes, and pay via cash or UPI.</p><h4>How can I register as a merchant?</h4><p>Click "Sell on DhuriyaMart", complete your merchant KYC and bank details, and start listing products as soon as our moderation team verifies your profile.</p>',
            ],
        ];

        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // 7. System Settings
        $settings = [
            'site_name' => 'DhuriyaMart',
            'site_tagline' => 'All-in-One Automated Village Smart Mart & Marketplace',
            'site_currency' => 'INR',
            'site_currency_symbol' => '₹',
            'default_commission_rate' => '10.00',
            'free_shipping_threshold_paise' => '49900', // ₹499.00
            'standard_shipping_fee_paise' => '4900',   // ₹49.00
            'tax_rate_percent' => '5.00',               // 5% GST
            'contact_email' => 'support@dhuriyamart.com',
            'contact_phone' => '+91 98765 43210',
            'office_address' => 'DhuriyaMart Technologies Pvt Ltd, 5th Floor, Innovate Tech Park, Bengaluru, Karnataka - 560001',
            'razorpay_key_id' => 'rzp_test_DhuriyaMartSandbox',
            'razorpay_key_secret' => 'rzp_test_DhuriyaMartSecretKey',
            'payment_sandbox_mode' => '1',
            'cod_enabled' => '1',
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val, 'group' => 'general']);
        }

        // 8. Coupons
        $coupons = [
            [
                'code' => 'WELCOME100',
                'description' => 'Flat ₹100 OFF on your first purchase above ₹499',
                'discount_type' => 'fixed',
                'discount_value' => 10000, // ₹100.00 in paise
                'min_order_value_paise' => 49900,
                'max_discount_paise' => null,
                'usage_limit' => 1000,
                'usage_count' => 5,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(6),
            ],
            [
                'code' => 'FESTIVE20',
                'description' => '20% Mega Festival Discount up to ₹500 on orders above ₹999',
                'discount_type' => 'percentage',
                'discount_value' => 20, // 20%
                'min_order_value_paise' => 99900,
                'max_discount_paise' => 50000, // ₹500.00 in paise
                'usage_limit' => 500,
                'usage_count' => 12,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::firstOrCreate(['code' => $c['code']], $c);
        }
    }
}
