<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PosSession;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\CashDeskService;
use App\Services\SmartMartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected SmartMartService $smartMartService,
        protected CashDeskService $cashDeskService
    ) {}

    /**
     * Get or create active primary store.
     */
    private function getPrimaryStore(): Store
    {
        return Store::where('is_active', true)->first()
            ?? Store::first()
            ?? Store::create([
                'name' => 'Dhuriya Mart - Flagship Village Store #01',
                'code' => 'STORE-MAU-01',
                'village_name' => 'Ranipur / Mau Dehat',
                'district' => 'Mau',
                'state' => 'Uttar Pradesh',
                'pincode' => '275101',
                'is_active' => true,
            ]);
    }

    /**
     * Staff-Assisted Billing POS Interface.
     */
    public function index(Request $request): View
    {
        $store = $this->getPrimaryStore();

        $cashier = $request->user();
        $session = $this->smartMartService->getOrCreateSession($store, null, $cashier, 'staff_assisted');
        $activeRegister = $store->getActiveRegister();

        $quickProducts = ProductVariant::with(['product.category'])
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'desc')
            ->take(30)
            ->get();

        return view('pos.index', [
            'store' => $store,
            'session' => $session,
            'activeRegister' => $activeRegister,
            'quickProducts' => $quickProducts,
            'mode' => 'staff_assisted',
        ]);
    }

    /**
     * Customer Self-Service Kiosk / Smart Mobile Interface.
     */
    public function kiosk(Request $request): View
    {
        $store = $this->getPrimaryStore();
        $customer = $request->user();
        $session = $this->smartMartService->getOrCreateSession($store, $customer, null, 'self_service');

        $quickProducts = ProductVariant::with(['product.category'])
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('id', 'asc')
            ->take(24)
            ->get();

        return view('pos.kiosk', [
            'store' => $store,
            'session' => $session,
            'quickProducts' => $quickProducts,
            'customer' => $customer,
            'mode' => 'self_service',
        ]);
    }

    /**
     * Scan item and add to current POS session.
     */
    public function scan(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'code' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $session = PosSession::findOrFail($request->session_id);
        $qty = $request->input('quantity', 1);

        try {
            $session = $this->smartMartService->addItemToSession($session, $request->code, $qty);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item added successfully',
                    'session' => $session,
                    'total_rupees' => $session->total_amount_rupees,
                    'item_count' => $session->item_count,
                ]);
            }

            return back()->with('success', 'Item scanned and added to bill.');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update quantity of an item in the session.
     */
    public function updateQuantity(Request $request, int $variantId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $session = PosSession::findOrFail($request->session_id);
        $session = $this->smartMartService->updateItemQuantity($session, $variantId, (int) $request->quantity);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'session' => $session,
                'total_rupees' => $session->total_amount_rupees,
                'item_count' => $session->item_count,
            ]);
        }

        return back()->with('success', 'Quantity updated.');
    }

    /**
     * Remove item from POS session.
     */
    public function removeItem(Request $request, int $variantId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
        ]);

        $session = PosSession::findOrFail($request->session_id);
        $session = $this->smartMartService->removeItemFromSession($session, $variantId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'session' => $session,
                'total_rupees' => $session->total_amount_rupees,
                'item_count' => $session->item_count,
            ]);
        }

        return back()->with('success', 'Item removed from bill.');
    }

    /**
     * Clear all items from POS session.
     */
    public function clear(Request $request): RedirectResponse
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
        ]);

        $session = PosSession::findOrFail($request->session_id);
        $this->smartMartService->clearSession($session);

        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Search customer by QR code, mobile, or Customer ID.
     */
    public function customerLookup(Request $request): JsonResponse
    {
        $query = $request->query('query');
        if (! $query) {
            return response()->json(['found' => false]);
        }

        $customer = $this->smartMartService->findCustomerByIdentifier($query);

        if (! $customer) {
            return response()->json(['found' => false, 'message' => 'Customer not found.']);
        }

        $customer->ensureCustomerIdentification();

        return response()->json([
            'found' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'customer_id' => $customer->customer_id,
                'qr_token' => $customer->qr_token,
                'phone' => $customer->phone,
                'village_name' => $customer->village_name,
                'wallet_balance_rupees' => $customer->wallet_balance_rupees,
            ],
        ]);
    }

    /**
     * Checkout POS session into completed order.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'payment_method' => 'required|in:cash,upi,wallet',
            'cash_tendered_rupees' => 'nullable|numeric|min:0',
            'customer_identifier' => 'nullable|string',
        ]);

        $session = PosSession::findOrFail($request->session_id);

        $cashTenderedPaise = $request->cash_tendered_rupees
            ? (int) round($request->cash_tendered_rupees * 100)
            : null;

        try {
            $order = $this->smartMartService->checkoutSession(
                $session,
                $request->payment_method,
                $cashTenderedPaise,
                $request->customer_identifier
            );

            return redirect()->route('pos.receipt', ['orderNumber' => $order->order_number])
                ->with('success', 'Order billed successfully! Instant receipt generated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display printable thermal digital receipt.
     */
    public function receipt(string $orderNumber): View
    {
        $order = Order::with(['items.variant', 'user', 'store', 'posSession', 'exitVerification'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('pos.receipt', [
            'order' => $order,
        ]);
    }
}
