<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\CashRegisterEntry;
use App\Models\ExitVerification;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Role;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VillageSmartMartSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles: Ensure Store Cashier & Manager roles exist
        Role::firstOrCreate(['slug' => 'store-manager'], ['name' => 'Store Manager', 'description' => 'Village Store Branch Manager']);
        Role::firstOrCreate(['slug' => 'cashier'], ['name' => 'Cashier / POS Operator', 'description' => 'Store billing and cash desk operator']);

        // 2. Village Store Users
        $defaultPassword = Hash::make('password123');

        $manager = User::firstOrCreate(
            ['email' => 'manager.mau@dhuriyamart.com'],
            [
                'name' => 'Ramkripal Dhuriya',
                'customer_id' => 'DM-STAFF-001',
                'qr_token' => 'DMQR-STAFF-001-MAU',
                'phone' => '+91 9876500010',
                'village_name' => 'Ranipur Mau',
                'password' => $defaultPassword,
                'status' => 'active',
            ]
        );
        $manager->assignRole('store-manager');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier.mau@dhuriyamart.com'],
            [
                'name' => 'Dinesh Kumar',
                'customer_id' => 'DM-STAFF-002',
                'qr_token' => 'DMQR-STAFF-002-MAU',
                'phone' => '+91 9876500011',
                'village_name' => 'Ranipur Mau',
                'password' => $defaultPassword,
                'status' => 'active',
            ]
        );
        $cashier->assignRole('cashier');

        // Customer with QR Card
        $customer1 = User::firstOrCreate(
            ['email' => 'customer@dhuriyamart.com'],
            ['name' => 'Rahul Verma', 'password' => $defaultPassword, 'phone' => '+91 9876500005', 'status' => 'active']
        );
        $customer1->customer_id = 'DM-CUST-10001';
        $customer1->qr_token = 'DMQR-CUST-10001-RAHUL';
        $customer1->wallet_balance_paise = 150000; // ₹1,500 store balance
        $customer1->village_name = 'Ranipur Mau';
        $customer1->save();

        $customer2 = User::firstOrCreate(
            ['email' => 'ramkishore.yadav@gmail.com'],
            [
                'name' => 'Ramkishore Yadav',
                'customer_id' => 'DM-CUST-10002',
                'qr_token' => 'DMQR-CUST-10002-RAMK',
                'phone' => '+91 9876500012',
                'village_name' => 'Khutahan Village',
                'password' => $defaultPassword,
                'status' => 'active',
                'wallet_balance_paise' => 85000, // ₹850
            ]
        );
        $customer2->assignRole('customer');

        // 3. Stores (Village Smart Marts)
        $store1 = Store::firstOrCreate(
            ['code' => 'STORE-MAU-01'],
            [
                'name' => 'Dhuriya Mart - Flagship Village Store #01',
                'village_name' => 'Ranipur / Mau Dehat',
                'district' => 'Mau',
                'state' => 'Uttar Pradesh',
                'pincode' => '275101',
                'phone' => '+91 9876500010',
                'manager_id' => $manager->id,
                'is_active' => true,
            ]
        );

        $store2 = Store::firstOrCreate(
            ['code' => 'STORE-AZA-02'],
            [
                'name' => 'Dhuriya Mart - Mubarakpur Smart Branch',
                'village_name' => 'Mubarakpur Rural',
                'district' => 'Azamgarh',
                'state' => 'Uttar Pradesh',
                'pincode' => '276404',
                'phone' => '+91 9876500020',
                'manager_id' => $manager->id,
                'is_active' => true,
            ]
        );

        // 4. Suppliers for Automated POs
        $supplier1 = Supplier::firstOrCreate(
            ['name' => 'Kashi Annapurna Grains & Pulses Wholesale'],
            [
                'contact_person' => 'Harish Chandra Gupta',
                'phone' => '+91 9415201100',
                'email' => 'kashigrains@wholesalemart.in',
                'address' => 'Grain Mandi Yard, Rajghat Road',
                'city' => 'Varanasi',
                'gstin' => '09AABCK1234F1Z5',
                'category' => 'kirana',
                'payment_terms' => 'Net 15 Days',
                'rating' => 4.8,
                'is_active' => true,
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['name' => 'Purvanchal Oils, Spices & FMCG Depot'],
            [
                'contact_person' => 'Sunil Agrawal',
                'phone' => '+91 9415202200',
                'email' => 'fmcg.purvanchal@supplynet.in',
                'address' => 'Civil Lines Transport Nagar',
                'city' => 'Gorakhpur',
                'gstin' => '09AACGP5678G2Z1',
                'category' => 'kirana',
                'payment_terms' => 'Net 7 Days',
                'rating' => 4.6,
                'is_active' => true,
            ]
        );

        $supplier3 = Supplier::firstOrCreate(
            ['name' => 'Varanasi Tools & Hardware Corporation'],
            [
                'contact_person' => 'Mohit Vishwakarma',
                'phone' => '+91 9415203300',
                'email' => 'tools.varanasi@hardwareindia.com',
                'address' => 'Loha Mandi, Sigra',
                'city' => 'Varanasi',
                'gstin' => '09AAHTV9012H1Z8',
                'category' => 'hardware',
                'payment_terms' => 'Net 30 Days',
                'rating' => 4.9,
                'is_active' => true,
            ]
        );

        // 5. Active Cash Register for Today
        $today = now()->toDateString();
        $register = CashRegister::firstOrCreate(
            [
                'store_id' => $store1->id,
                'register_date' => $today,
                'status' => 'open',
            ],
            [
                'opened_by_id' => $cashier->id,
                'opening_float_paise' => 500000, // ₹5,000 float
                'cash_sales_paise' => 148500,    // ₹1,485 sales so far
                'cash_in_paise' => 50000,        // ₹500 customer deposit
                'cash_out_paise' => 20000,       // ₹200 petty tea/packing expense
                'expected_cash_paise' => 678500, // ₹6,785 expected
                'closing_actual_paise' => null,
                'difference_paise' => 0,
                'notes' => 'Morning shift active with ₹5,000 opening float.',
                'opened_at' => now()->startOfDay()->addHours(8),
            ]
        );

        // Sample Cash Register Entry
        CashRegisterEntry::firstOrCreate(
            [
                'cash_register_id' => $register->id,
                'type' => 'opening_float',
            ],
            [
                'user_id' => $cashier->id,
                'amount_paise' => 500000,
                'reason' => 'Morning drawer opening float',
            ]
        );

        CashRegisterEntry::firstOrCreate(
            [
                'cash_register_id' => $register->id,
                'type' => 'cash_out',
                'reason' => 'Store tea & cleaning wipes expense',
            ],
            [
                'user_id' => $cashier->id,
                'amount_paise' => 20000,
            ]
        );

        // 6. Sample Completed POS Session & Order with Exit Verification
        $aataVariant = ProductVariant::where('sku', 'KIR-WHEAT-01')->first() ?? ProductVariant::first();
        $oilVariant = ProductVariant::where('sku', 'KIR-MUST-01')->first() ?? ProductVariant::skip(1)->first();

        if ($aataVariant && $oilVariant) {
            $sessCode = 'POS-MAU-202610-001';
            $posSession = PosSession::firstOrCreate(
                ['session_code' => $sessCode],
                [
                    'store_id' => $store1->id,
                    'cashier_id' => $cashier->id,
                    'customer_id' => $customer1->id,
                    'mode' => 'staff_assisted',
                    'status' => 'completed',
                    'cart_items' => [
                        [
                            'variant_id' => $aataVariant->id,
                            'sku' => $aataVariant->sku,
                            'name' => $aataVariant->name,
                            'quantity' => 2,
                            'price_paise' => $aataVariant->price_paise,
                            'subtotal_paise' => $aataVariant->price_paise * 2,
                            'tax_paise' => 0,
                        ],
                        [
                            'variant_id' => $oilVariant->id,
                            'sku' => $oilVariant->sku,
                            'name' => $oilVariant->name,
                            'quantity' => 1,
                            'price_paise' => $oilVariant->price_paise,
                            'subtotal_paise' => $oilVariant->price_paise,
                            'tax_paise' => 0,
                        ],
                    ],
                    'subtotal_paise' => ($aataVariant->price_paise * 2) + $oilVariant->price_paise,
                    'discount_paise' => 0,
                    'tax_paise' => 0,
                    'total_amount_paise' => ($aataVariant->price_paise * 2) + $oilVariant->price_paise,
                    'payment_method' => 'cash',
                    'cash_tendered_paise' => 50000, // ₹500 paid
                    'change_returned_paise' => 50000 - (($aataVariant->price_paise * 2) + $oilVariant->price_paise),
                    'completed_at' => now()->subHours(2),
                ]
            );

            // Sample Order corresponding to POS
            $order = Order::firstOrCreate(
                ['order_number' => 'DM-POS-10001'],
                [
                    'user_id' => $customer1->id,
                    'store_id' => $store1->id,
                    'order_type' => 'pos_staff_assisted',
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'currency' => 'INR',
                    'subtotal_paise' => $posSession->subtotal_paise,
                    'discount_paise' => 0,
                    'shipping_fee_paise' => 0,
                    'tax_paise' => 0,
                    'total_amount_paise' => $posSession->total_amount_paise,
                    'customer_notes' => 'In-store village POS purchase',
                ]
            );
            $posSession->update(['order_id' => $order->id]);

            // Sample Exit Verification (Mode A - Staff Checked & Passed)
            ExitVerification::firstOrCreate(
                ['verification_code' => 'EXIT-MAU-0001'],
                [
                    'store_id' => $store1->id,
                    'order_id' => $order->id,
                    'pos_session_id' => $posSession->id,
                    'verifier_id' => $cashier->id,
                    'mode' => 'staff_assisted',
                    'status' => 'passed',
                    'total_billed_items' => 3,
                    'total_scanned_items' => 3,
                    'scanned_items' => [
                        ['sku' => $aataVariant->sku, 'quantity' => 2],
                        ['sku' => $oilVariant->sku, 'quantity' => 1],
                    ],
                    'discrepancy_details' => null,
                    'notes' => 'Mode A Physical Verification: Cart contents verified against bill DM-POS-10001. All items match.',
                    'verified_at' => now()->subHours(2)->addMinutes(3),
                ]
            );
        }

        // 7. Automated Purchase Order Draft for Low-Stock Items
        $lowStockVariants = ProductVariant::where('stock_quantity', '<=', 50)->take(4)->get();
        if ($lowStockVariants->isNotEmpty()) {
            $po = PurchaseOrder::firstOrCreate(
                ['po_number' => 'PO-202610-0001'],
                [
                    'store_id' => $store1->id,
                    'supplier_id' => $supplier1->id,
                    'created_by_id' => $manager->id,
                    'approved_by_id' => null,
                    'status' => 'draft',
                    'total_cost_paise' => 0,
                    'notes' => 'Auto-generated replenishment purchase order based on minimum stock threshold calculation.',
                    'expected_delivery_date' => now()->addDays(3)->toDateString(),
                ]
            );

            $totalPoCost = 0;
            foreach ($lowStockVariants as $variant) {
                $orderQty = 50; // standard restock batch
                $unitCost = $variant->cost_price_paise ?? (int) ($variant->price_paise * 0.82);
                $totalItemCost = $unitCost * $orderQty;
                $totalPoCost += $totalItemCost;

                PurchaseOrderItem::firstOrCreate(
                    [
                        'purchase_order_id' => $po->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'product_name' => $variant->product->name ?? $variant->name,
                        'variant_name' => $variant->name,
                        'sku' => $variant->sku,
                        'quantity_ordered' => $orderQty,
                        'quantity_received' => 0,
                        'unit_cost_paise' => $unitCost,
                        'total_cost_paise' => $totalItemCost,
                        'status' => 'pending',
                    ]
                );
            }
            $po->update(['total_cost_paise' => $totalPoCost]);
        }
    }
}
