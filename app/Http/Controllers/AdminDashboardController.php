<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashRegisterEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $totalOrdersCount = Order::count();
        $grossSalesPaise = (int) Order::where('status', '!=', 'cancelled')->sum('total_amount_paise');
        $collectedPaymentsPaise = (int) Payment::where('status', 'successful')->sum('amount_paise');

        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $completedOrdersCount = Order::where('status', 'completed')->count();

        $customerRoleId = Role::where('slug', 'customer')->value('id');
        $registeredCustomersCount = User::whereHas('roles', fn ($q) => $q->where('slug', 'customer'))->count();

        $approvedSellersCount = Shop::where('status', 'approved')->count();
        $pendingSellersCount = Shop::where('status', 'pending')->count();

        $totalProductsCount = Product::count();
        $pendingModerationProductsCount = Product::where('status', 'pending')->count();

        $lowStockVariantsCount = $this->inventoryService->getLowStockVariants()->count();

        // Village Smart Mart Financial Accounting (Revenue vs COGS vs Expenses vs Net Profit)
        $paidOrderItems = OrderItem::with('variant')
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->get();

        $grossRevenuePaise = (int) Order::where('payment_status', 'paid')->sum('total_amount_paise');
        $cogsPaise = 0;
        foreach ($paidOrderItems as $item) {
            $unitCost = $item->variant?->cost_price_paise ?? (int) ($item->price_paise * 0.80);
            $cogsPaise += ($unitCost * $item->quantity);
        }

        $grossProfitPaise = max(0, $grossRevenuePaise - $cogsPaise);
        $operatingExpensesPaise = (int) CashRegisterEntry::where('type', 'cash_out')->sum('amount_paise');
        $netProfitPaise = $grossProfitPaise - $operatingExpensesPaise;

        $totalVillageStores = Store::count();
        $openRegistersCount = CashRegister::where('status', 'open')->count();
        $pendingPoCount = PurchaseOrder::where('status', 'draft')->count();

        $recentOrders = Order::with(['user', 'sellerOrders.shop', 'latestPayment'])
            ->latest()
            ->take(8)
            ->get();

        $recentSellers = Shop::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalOrdersCount',
            'grossSalesPaise',
            'collectedPaymentsPaise',
            'pendingOrdersCount',
            'completedOrdersCount',
            'registeredCustomersCount',
            'approvedSellersCount',
            'pendingSellersCount',
            'totalProductsCount',
            'pendingModerationProductsCount',
            'lowStockVariantsCount',
            'grossRevenuePaise',
            'cogsPaise',
            'grossProfitPaise',
            'operatingExpensesPaise',
            'netProfitPaise',
            'totalVillageStores',
            'openRegistersCount',
            'pendingPoCount',
            'recentOrders',
            'recentSellers'
        ));
    }
}
