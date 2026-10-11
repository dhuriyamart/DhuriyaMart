<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function sales(Request $request): View
    {
        $ordersQuery = Order::where('status', '!=', 'cancelled');
        $sellerOrdersQuery = SellerOrder::where('status', '!=', 'cancelled');

        $totalSalesPaise = (int) $ordersQuery->sum('total_amount_paise');
        $totalCommissionPaise = (int) $sellerOrdersQuery->sum('commission_amount_paise');
        $totalNetSellerPayoutsPaise = (int) $sellerOrdersQuery->sum('net_seller_amount_paise');
        $totalDeliveredCount = SellerOrder::where('status', 'delivered')->count();

        // Shop-wise performance
        $shopPerformance = Shop::withCount(['sellerOrders' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['sellerOrders as gross_sales' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount_paise')
            ->withSum(['sellerOrders as total_commission' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'commission_amount_paise')
            ->get();

        // Payment method distribution
        $codOrdersCount = Order::where('payment_method', 'cod')->where('status', '!=', 'cancelled')->count();
        $onlineOrdersCount = Order::where('payment_method', '!=', 'cod')->where('status', '!=', 'cancelled')->count();

        return view('admin.reports.sales', compact(
            'totalSalesPaise',
            'totalCommissionPaise',
            'totalNetSellerPayoutsPaise',
            'totalDeliveredCount',
            'shopPerformance',
            'codOrdersCount',
            'onlineOrdersCount'
        ));
    }
}
