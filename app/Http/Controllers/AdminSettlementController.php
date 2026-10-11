<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SellerSettlement;
use App\Models\Shop;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminSettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    public function index(): View
    {
        $shops = Shop::withCount(['sellerOrders as pending_orders_count' => fn ($q) => $q->where('status', 'delivered')->where('payout_status', 'unpaid')])
            ->get();

        $settlements = SellerSettlement::with(['shop', 'items', 'processedBy'])
            ->latest()
            ->paginate(15);

        return view('admin.settlements.index', compact('shops', 'settlements'));
    }

    public function generate(Request $request, int $shopId): RedirectResponse
    {
        $shop = Shop::findOrFail($shopId);

        try {
            $settlement = $this->settlementService->generateSettlement($shop, $request->user());

            if (! $settlement) {
                return back()->with('info', "No unpaid delivered orders found for {$shop->name}.");
            }

            AuditLog::record('settlement_generated', $settlement, null, [
                'shop_id' => $shop->id,
                'net_payout_paise' => $settlement->net_payout_paise,
            ]);

            return back()->with('success', "Settlement #{$settlement->settlement_number} generated successfully for {$shop->name}.");
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(Request $request, int $id): RedirectResponse
    {
        $settlement = SellerSettlement::findOrFail($id);

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
        ]);

        try {
            $this->settlementService->markSettlementPaid(
                $settlement,
                $validated['payment_reference'],
                $request->user()
            );

            AuditLog::record('settlement_paid', $settlement, null, [
                'reference' => $validated['payment_reference'],
            ]);

            return back()->with('success', "Settlement #{$settlement->settlement_number} marked as paid.");
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
