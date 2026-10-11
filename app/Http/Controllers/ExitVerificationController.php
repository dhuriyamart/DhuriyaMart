<?php

namespace App\Http\Controllers;

use App\Models\ExitVerification;
use App\Models\Order;
use App\Models\Store;
use App\Services\SmartMartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExitVerificationController extends Controller
{
    public function __construct(
        protected SmartMartService $smartMartService
    ) {}

    /**
     * Dual Exit Verification Desk & Automated Gate View.
     */
    public function index(Request $request): View
    {
        $store = Store::firstOrCreate(
            ['code' => 'STORE-MAU-01'],
            [
                'name' => 'Dhuriya Mart - Flagship Village Store #01',
                'village_name' => 'Ranipur / Mau Dehat',
                'district' => 'Mau',
                'state' => 'Uttar Pradesh',
                'pincode' => '275101',
                'is_active' => true,
            ]
        );

        $recentVerifications = ExitVerification::with(['order.user', 'verifier'])
            ->where('store_id', $store->id)
            ->latest()
            ->take(15)
            ->get();

        $selectedOrder = null;
        if ($request->filled('order_number')) {
            $selectedOrder = Order::with(['items.variant', 'user', 'exitVerification'])
                ->where('order_number', trim($request->order_number))
                ->first();
        }

        return view('pos.exit-verification', [
            'store' => $store,
            'recentVerifications' => $recentVerifications,
            'selectedOrder' => $selectedOrder,
        ]);
    }

    /**
     * Mode A: Staff-Assisted Physical Check.
     */
    public function verifyStaff(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'action' => 'required|in:pass,discrepancy',
            'notes' => 'nullable|string',
        ]);

        $order = Order::with('items')->findOrFail($request->order_id);
        $verifier = $request->user();

        $items = [];
        $pass = ($request->action === 'pass');

        foreach ($order->items as $item) {
            $items[] = [
                'sku' => $item->sku,
                'name' => $item->product_name,
                'quantity' => $item->quantity,
            ];
        }

        // If user manually reported discrepancy, tweak count to register discrepancy
        if (! $pass) {
            $items[] = [
                'sku' => 'UNKNOWN_EXTRA',
                'name' => 'Unbilled Physical Item',
                'quantity' => 1,
            ];
        }

        $verification = $this->smartMartService->verifyExitStaff(
            $order,
            $verifier->id,
            $items,
            $request->notes
        );

        if ($verification->isPassed()) {
            return redirect()->route('pos.exit-verification')
                ->with('success', "Exit Passed! Order #{$order->order_number} verified successfully by staff.");
        }

        return redirect()->route('pos.exit-verification')
            ->with('warning', "Discrepancy Flagged for Order #{$order->order_number}! Staff intervention recorded.");
    }

    /**
     * Mode B: Automated Barcode Scan Matching.
     */
    public function verifyAutomated(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'scanned_barcodes' => 'required|array',
        ]);

        $order = Order::with('items.variant')->findOrFail($request->order_id);
        $verification = $this->smartMartService->verifyExitAutomated($order, $request->scanned_barcodes);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'passed' => $verification->isPassed(),
                'status' => $verification->status,
                'discrepancy' => $verification->discrepancy_details,
                'notes' => $verification->notes,
                'verification' => $verification,
            ]);
        }

        if ($verification->isPassed()) {
            return back()->with('success', 'Automated Scan Passed! 100% item match. Exit gate authorized.');
        }

        return back()->with('error', 'Discrepancy detected between scanned items and billed receipt! Gate locked.');
    }
}
