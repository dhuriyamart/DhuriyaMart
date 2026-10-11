<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\SellerOrder;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        if (!$shop) {
            return view('seller.onboarding');
        }

        $totalOrdersCount = $shop->sellerOrders()->count();
        $grossSalesPaise = (int) $shop->sellerOrders()->where('status', '!=', 'cancelled')->sum('total_amount_paise');
        $netEarningsPaise = (int) $shop->sellerOrders()->where('status', '!=', 'cancelled')->sum('net_seller_amount_paise');
        $pendingDispatchesCount = $shop->sellerOrders()->whereIn('status', ['confirmed', 'processing'])->count();
        $deliveredOrdersCount = $shop->sellerOrders()->where('status', 'delivered')->count();

        $currentBalancePaise = $shop->current_balance_paise;
        $lowStockCount = $this->inventoryService->getLowStockVariants($shop)->count();
        $productsCount = $shop->products()->count();

        $recentOrders = $shop->sellerOrders()
            ->with(['order.user', 'items.variant'])
            ->latest()
            ->take(6)
            ->get();

        return view('seller.dashboard', compact(
            'shop',
            'totalOrdersCount',
            'grossSalesPaise',
            'netEarningsPaise',
            'pendingDispatchesCount',
            'deliveredOrdersCount',
            'currentBalancePaise',
            'lowStockCount',
            'productsCount',
            'recentOrders'
        ));
    }
}

