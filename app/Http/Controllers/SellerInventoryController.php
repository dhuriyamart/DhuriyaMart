<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SellerInventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        $variants = ProductVariant::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
            ->with(['product'])
            ->orderBy('stock_quantity')
            ->paginate(20);

        $recentMovements = InventoryMovement::whereHas('variant.product', fn($q) => $q->where('shop_id', $shop->id))
            ->with('variant.product')
            ->latest()
            ->take(10)
            ->get();

        return view('seller.inventory.index', compact('shop', 'variants', 'recentMovements'));
    }

    public function update(Request $request, int $variantId): RedirectResponse
    {
        $shop = $request->user()->shop;

        $variant = ProductVariant::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
            ->findOrFail($variantId);

        $validated = $request->validate([
            'new_stock' => 'required|integer|min:0|max:100000',
            'reason' => 'required|string|max:200',
        ]);

        try {
            $this->inventoryService->adjustStock(
                $variant,
                $validated['new_stock'],
                $validated['reason'],
                $request->user()
            );

            return back()->with('success', "Stock for SKU {$variant->sku} updated to {$validated['new_stock']} units.");
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

