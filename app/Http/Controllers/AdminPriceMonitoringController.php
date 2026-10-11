<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPriceMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $kiranaShop = Shop::where('slug', 'dhuriya-kirana-mart')->first();
        $hardwareShop = Shop::where('slug', 'dhuriya-hardware-tools')->first();

        // Query Kirana Variants with Products & Categories
        $kiranaVariants = ProductVariant::whereHas('product', function ($q) use ($kiranaShop) {
            if ($kiranaShop) {
                $q->where('shop_id', $kiranaShop->id);
            } else {
                $q->whereHas('category', fn ($cq) => $cq->where('slug', 'like', '%kirana%')
                    ->orWhere('slug', 'grains-flour-pulses')
                    ->orWhere('slug', 'oils-sugar-salt-spices')
                    ->orWhere('slug', 'packaged-food-snacks')
                    ->orWhere('slug', 'cleaning-personal-care'));
            }
        })->with(['product.category'])->get();

        // Query Hardware Variants with Products & Categories
        $hardwareVariants = ProductVariant::whereHas('product', function ($q) use ($hardwareShop) {
            if ($hardwareShop) {
                $q->where('shop_id', $hardwareShop->id);
            } else {
                $q->whereHas('category', fn ($cq) => $cq->where('slug', 'like', '%hardware%')
                    ->orWhere('slug', 'screws-nails-fittings')
                    ->orWhere('slug', 'plumbing-sanitary')
                    ->orWhere('slug', 'electrical-goods')
                    ->orWhere('slug', 'paint-adhesives')
                    ->orWhere('slug', 'hand-tools-construction'));
            }
        })->with(['product.category'])->get();

        // Calculate Kirana Totals
        $kiranaCostPaise = (int) $kiranaVariants->sum(fn ($v) => ($v->cost_price_paise ?? 0) * $v->stock_quantity);
        $kiranaSalesPaise = (int) $kiranaVariants->sum(fn ($v) => $v->price_paise * $v->stock_quantity);
        $kiranaGrossProfitPaise = max(0, $kiranaSalesPaise - $kiranaCostPaise);
        $kiranaTargetBudgetPaise = 40000000; // ₹4,00,000.00
        $kiranaBufferPaise = max(0, $kiranaTargetBudgetPaise - $kiranaCostPaise);

        // Group Kirana by Subcategory
        $kiranaGrouped = $kiranaVariants->groupBy(fn ($v) => $v->product->category->name ?? 'General');

        // Calculate Hardware Totals
        $hardwareCostPaise = (int) $hardwareVariants->sum(fn ($v) => ($v->cost_price_paise ?? 0) * $v->stock_quantity);
        $hardwareSalesPaise = (int) $hardwareVariants->sum(fn ($v) => $v->price_paise * $v->stock_quantity);
        $hardwareGrossProfitPaise = max(0, $hardwareSalesPaise - $hardwareCostPaise);
        $hardwareTargetBudgetPaise = 40000000; // ₹4,00,000.00
        $hardwareBufferPaise = max(0, $hardwareTargetBudgetPaise - $hardwareCostPaise);

        // Group Hardware by Subcategory
        $hardwareGrouped = $hardwareVariants->groupBy(fn ($v) => $v->product->category->name ?? 'General');

        // Official October 2026 Price Monitoring Benchmarks
        $officialBenchmarks = [
            [
                'item' => 'Wheat Aata',
                'unit' => '1 kg',
                'official_avg' => 37.72,
                'our_buy' => 32.00,
                'our_retail' => 38.00,
                'our_sku' => 'KRN-AATA-1KG',
            ],
            [
                'item' => 'Rice, Economy',
                'unit' => '1 kg',
                'official_avg' => 45.81,
                'our_buy' => 38.00,
                'our_retail' => 45.00,
                'our_sku' => 'KRN-RICE-ECO-1KG',
            ],
            [
                'item' => 'Arhar Dal (Toor)',
                'unit' => '1 kg',
                'official_avg' => 127.81,
                'our_buy' => 110.00,
                'our_retail' => 130.00,
                'our_sku' => 'KRN-ARHAR-DAL-1KG',
            ],
            [
                'item' => 'Refined Sugar',
                'unit' => '1 kg',
                'official_avg' => 55.43,
                'our_buy' => 48.00,
                'our_retail' => 55.00,
                'our_sku' => 'KRN-SUGAR-1KG',
            ],
        ];

        // ₹10 Lakh Master Business Plan Allocations
        $businessPlan = [
            [
                'item' => 'Kirana Opening Stock',
                'category' => 'Inventory',
                'budget_rupees' => 400000,
                'percentage' => 40.0,
                'allocated_cost' => $kiranaCostPaise / 100,
                'description' => 'Grains, pulses, edible oils, spices, snacks & cleaning supplies',
                'icon' => 'bi-basket3',
                'color' => 'success',
            ],
            [
                'item' => 'Hardware Opening Stock',
                'category' => 'Inventory',
                'budget_rupees' => 400000,
                'percentage' => 40.0,
                'allocated_cost' => $hardwareCostPaise / 100,
                'description' => 'Fasteners, sanitary pipes, electricals, paints & hand tools',
                'icon' => 'bi-tools',
                'color' => 'primary',
            ],
            [
                'item' => 'Shop Fixtures & POS Billing Setup',
                'category' => 'Capex',
                'budget_rupees' => 80000,
                'percentage' => 8.0,
                'allocated_cost' => 80000,
                'description' => 'Racks, counters, barcode scanner, thermal receipt printer & software',
                'icon' => 'bi-display',
                'color' => 'info',
            ],
            [
                'item' => 'Rent / Shop Deposit Allowance',
                'category' => 'Premises',
                'budget_rupees' => 50000,
                'percentage' => 5.0,
                'allocated_cost' => 50000,
                'description' => 'Security deposit and initial lease advance for commercial space',
                'icon' => 'bi-building',
                'color' => 'warning',
            ],
            [
                'item' => 'Signboard & Basic Setup',
                'category' => 'Branding',
                'budget_rupees' => 20000,
                'percentage' => 2.0,
                'allocated_cost' => 20000,
                'description' => 'Glow-sign storefront board, lighting fixtures, and interior painting',
                'icon' => 'bi-badge-ad',
                'color' => 'secondary',
            ],
            [
                'item' => 'Working Capital Cash Reserve',
                'category' => 'Liquidity',
                'budget_rupees' => 50000,
                'percentage' => 5.0,
                'allocated_cost' => 50000,
                'description' => 'Buffer fund for change cash, emergency restocks & daily logistics',
                'icon' => 'bi-cash-coin',
                'color' => 'dark',
            ],
        ];

        return view('admin.price-monitoring.index', compact(
            'kiranaShop',
            'hardwareShop',
            'kiranaVariants',
            'hardwareVariants',
            'kiranaCostPaise',
            'kiranaSalesPaise',
            'kiranaGrossProfitPaise',
            'kiranaTargetBudgetPaise',
            'kiranaBufferPaise',
            'kiranaGrouped',
            'hardwareCostPaise',
            'hardwareSalesPaise',
            'hardwareGrossProfitPaise',
            'hardwareTargetBudgetPaise',
            'hardwareBufferPaise',
            'hardwareGrouped',
            'officialBenchmarks',
            'businessPlan'
        ));
    }
}
