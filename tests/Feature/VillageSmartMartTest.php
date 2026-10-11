<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\ExitVerification;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AutoPurchaseOrderService;
use App\Services\CashDeskService;
use App\Services\SmartMartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VillageSmartMartTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $cashier;
    protected Store $store;
    protected ProductVariant $variant;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@dhuriyamart.com')->first();
        $this->customer = User::where('email', 'customer@dhuriyamart.com')->first();
        $this->cashier = User::where('email', 'cashier.mau@dhuriyamart.com')->first();
        $this->store = Store::first();
        $this->variant = ProductVariant::first();
        $this->supplier = Supplier::first();
    }

    public function test_customer_has_unique_id_and_qr_token_and_can_view_mart_card(): void
    {
        $this->customer->ensureCustomerIdentification();

        $this->assertNotNull($this->customer->customer_id);
        $this->assertNotNull($this->customer->qr_token);
        $this->assertStringStartsWith('DM-CUST-', $this->customer->customer_id);
        $this->assertStringStartsWith('DMQR-', $this->customer->qr_token);

        $response = $this->actingAs($this->customer)->get(route('account.qr-card'));
        $response->assertStatus(200);
        $response->assertSee($this->customer->customer_id);
        $response->assertSee($this->customer->name);
    }

    public function test_staff_pos_and_kiosk_pages_load(): void
    {
        // Kiosk is accessible
        $kioskRes = $this->get(route('pos.kiosk'));
        $kioskRes->assertStatus(200);
        $kioskRes->assertSee('Self-Service Kiosk');

        // Staff POS requires cashier / staff role
        $posRes = $this->actingAs($this->cashier)->get(route('pos.index'));
        $posRes->assertStatus(200);
        $posRes->assertSee($this->store->name);
    }

    public function test_pos_can_scan_item_and_checkout_with_cash_tender(): void
    {
        $smartMartService = app(SmartMartService::class);
        $session = $smartMartService->getOrCreateSession($this->store, $this->customer, $this->cashier, 'staff_assisted');

        // Scan item
        $response = $this->actingAs($this->cashier)->post(route('pos.scan'), [
            'session_id' => $session->id,
            'code' => $this->variant->sku,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $session->refresh();
        $this->assertCount(1, $session->cart_items);
        $this->assertEquals(2, $session->item_count);

        $initialStock = $this->variant->stock_quantity;

        // Checkout with Cash
        $checkoutResponse = $this->actingAs($this->cashier)->post(route('pos.checkout'), [
            'session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_tendered_rupees' => $session->total_amount_rupees + 100, // ₹100 change
            'customer_identifier' => $this->customer->customer_id,
        ]);

        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->order_id);

        $order = Order::find($session->order_id);
        $this->assertNotNull($order);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('cash', $order->payment_method);

        // Stock was decremented
        $this->variant->refresh();
        $this->assertEquals($initialStock - 2, $this->variant->stock_quantity);

        // Printable Receipt loads
        $receiptRes = $this->get(route('pos.receipt', $order->order_number));
        $receiptRes->assertStatus(200);
        $receiptRes->assertSee($order->order_number);
    }

    public function test_exit_verification_mode_a_staff_verification(): void
    {
        $smartMartService = app(SmartMartService::class);
        $session = $smartMartService->getOrCreateSession($this->store, $this->customer, $this->cashier, 'staff_assisted');
        $smartMartService->addItemToSession($session, $this->variant->sku, 1);
        $order = $smartMartService->checkoutSession($session, 'cash', $session->total_amount_paise);

        // Mode A: Staff passes physical check
        $res = $this->actingAs($this->cashier)->post(route('pos.exit-verification.staff'), [
            'order_id' => $order->id,
            'action' => 'pass',
            'notes' => 'All items physically matched.',
        ]);

        $res->assertRedirect();
        $verification = ExitVerification::where('order_id', $order->id)->first();
        $this->assertNotNull($verification);
        $this->assertEquals('passed', $verification->status);
        $this->assertEquals('staff_assisted', $verification->mode);
    }

    public function test_exit_verification_mode_b_automated_scan_and_discrepancy(): void
    {
        $smartMartService = app(SmartMartService::class);
        $session = $smartMartService->getOrCreateSession($this->store, $this->customer, $this->cashier, 'staff_assisted');
        $smartMartService->addItemToSession($session, $this->variant->sku, 2);
        $order = $smartMartService->checkoutSession($session, 'cash', $session->total_amount_paise);

        $barcode = $this->variant->barcode ?: $this->variant->sku;

        // Exact match scenario (2 barcodes scanned)
        $passRes = $this->actingAs($this->cashier)->postJson(route('pos.exit-verification.automated'), [
            'order_id' => $order->id,
            'scanned_barcodes' => [$barcode, $barcode],
        ]);
        $passRes->assertStatus(200);
        $passRes->assertJson(['passed' => true, 'status' => 'passed']);

        // Discrepancy scenario (3 barcodes scanned instead of 2)
        $failRes = $this->actingAs($this->cashier)->postJson(route('pos.exit-verification.automated'), [
            'order_id' => $order->id,
            'scanned_barcodes' => [$barcode, $barcode, 'UNBILLED-EXTRA-ITEM'],
        ]);
        $failRes->assertStatus(200);
        $failRes->assertJson(['passed' => false, 'status' => 'discrepancy_flagged']);
    }

    public function test_daily_cash_register_lifecycle_and_reconciliation(): void
    {
        $cashDeskService = app(CashDeskService::class);
        CashRegister::where('store_id', $this->store->id)->delete();

        // Open register with ₹5,000 float
        $register = $cashDeskService->openRegister($this->store, $this->cashier, 500000, 'Morning shift');
        $this->assertEquals(500000, $register->opening_float_paise);
        $this->assertEquals('open', $register->status);

        // Add petty cash out ₹250
        $cashDeskService->recordCashEntry($register, $this->cashier, 'cash_out', 25000, null, 'Tea & refreshments');
        $register->refresh();
        $this->assertEquals(25000, $register->cash_out_paise);
        $this->assertEquals(475000, $register->expected_cash_paise);

        // Close register with actual physical count of ₹4,750 (100% balanced)
        $register = $cashDeskService->closeRegister($register, $this->cashier, 475000, 'Evening tally perfect');
        $this->assertEquals('closed', $register->status);
        $this->assertEquals(0, $register->difference_paise);

        // Reconciliation report
        $report = $cashDeskService->getDailyReconciliation($this->store);
        $this->assertTrue($report['is_balanced']);
        $this->assertEquals(0.00, $report['difference_rupees']);
    }

    public function test_customer_cash_deposit_credits_wallet(): void
    {
        $cashDeskService = app(CashDeskService::class);
        $initialBalance = $this->customer->wallet_balance_paise;

        $res = $cashDeskService->customerDeposit($this->customer, $this->cashier, 50000, $this->store, 'Counter cash deposit');

        $this->customer->refresh();
        $this->assertEquals($initialBalance + 50000, $this->customer->wallet_balance_paise);
        $this->assertEquals($initialBalance + 50000, $res['new_balance_paise']);
    }

    public function test_automated_purchase_order_generation_and_grn_stock_intake(): void
    {
        // Set variant stock low to trigger auto-PO
        $this->variant->stock_quantity = 2;
        $this->variant->low_stock_threshold = 5;
        $this->variant->save();

        $poService = app(AutoPurchaseOrderService::class);
        $generated = $poService->generateAutoDraftPOs($this->store, $this->admin, 20);

        $this->assertNotEmpty($generated);
        $po = $generated->first();
        $this->assertEquals('draft', $po->status);
        $this->assertGreaterThan(0, $po->total_cost_paise);

        // Approve PO
        $po = $poService->approvePO($po, $this->admin);
        $this->assertEquals('approved', $po->status);

        // Process GRN receiving
        $initialStock = $this->variant->stock_quantity;
        $poItem = $po->items->firstWhere('product_variant_id', $this->variant->id);

        if ($poItem) {
            $receivedQty = $poItem->quantity_ordered;
            $poService->receiveGoods($po, [$poItem->id => $receivedQty], [$poItem->id => 0]);

            $this->variant->refresh();
            $this->assertEquals($initialStock + $receivedQty, $this->variant->stock_quantity);

            $po->refresh();
            $this->assertEquals('received', $po->status);
        }
    }

    public function test_village_store_branch_creation_and_toggle(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.village-stores.store'), [
            'code' => 'STORE-GKP-03',
            'name' => 'Dhuriya Mart - Gorakhpur Dehat Branch',
            'village_name' => 'Sahjanwa Rural',
            'district' => 'Gorakhpur',
            'state' => 'Uttar Pradesh',
            'pincode' => '273209',
            'phone' => '+91 9876500033',
        ]);

        $response->assertRedirect();
        $newStore = Store::where('code', 'STORE-GKP-03')->first();
        $this->assertNotNull($newStore);
        $this->assertTrue($newStore->is_active);

        // Toggle
        $toggleRes = $this->actingAs($this->admin)->post(route('admin.village-stores.toggle', $newStore->id));
        $toggleRes->assertRedirect();
        $newStore->refresh();
        $this->assertFalse($newStore->is_active);
    }
}
