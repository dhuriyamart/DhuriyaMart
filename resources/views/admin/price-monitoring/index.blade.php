@extends('layouts.admin')

@section('title', 'Kirana & Hardware Opening Stock & Price Monitoring - DhuriyaMart Admin')
@section('page_title', 'Opening Stock, Price Monitoring & ₹10 Lakh Business Plan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Opening Stock &amp; Price Monitoring System</h4>
            <p class="text-muted small mb-0">Procurement valuation, retail pricing benchmarks, and capital budget tracking for Kirana &amp; Hardware divisions</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill font-monospace small">
                <i class="bi bi-clock-history me-1"></i> Benchmark Date: 10 October 2026
            </span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill font-monospace small">
                <i class="bi bi-cash-stack me-1"></i> Target Plan: ₹10,00,000
            </span>
        </div>
    </div>

    @include('components.alerts')

    <!-- Executive Stat Cards -->
    <div class="row g-3 mb-4">
        <!-- Kirana Procurement Card -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Kirana Opening Cost</span>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                        <i class="bi bi-basket3 fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ \App\Support\Money::format($kiranaCostPaise) }}</h4>
                <div class="small text-muted mb-2">
                    Budget Ceiling: <strong class="text-dark">₹4,00,000.00</strong>
                </div>
                <div class="progress" style="height: 6px;">
                    @php $kiranaPct = min(100, round(($kiranaCostPaise / $kiranaTargetBudgetPaise) * 100, 1)); @endphp
                    <div class="progress-bar bg-success" style="width: {{ $kiranaPct }}%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                    <span>{{ $kiranaPct }}% allocated</span>
                    <span>Buffer: {{ \App\Support\Money::format($kiranaBufferPaise) }}</span>
                </div>
            </div>
        </div>

        <!-- Hardware Procurement Card -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Hardware Opening Cost</span>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                        <i class="bi bi-tools fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ \App\Support\Money::format($hardwareCostPaise) }}</h4>
                <div class="small text-muted mb-2">
                    Budget Ceiling: <strong class="text-dark">₹4,00,000.00</strong>
                </div>
                <div class="progress" style="height: 6px;">
                    @php $hwdPct = min(100, round(($hardwareCostPaise / $hardwareTargetBudgetPaise) * 100, 1)); @endphp
                    <div class="progress-bar bg-primary" style="width: {{ $hwdPct }}%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                    <span>{{ $hwdPct }}% allocated</span>
                    <span>Buffer: {{ \App\Support\Money::format($hardwareBufferPaise) }}</span>
                </div>
            </div>
        </div>

        <!-- Combined Retail Sales Potential -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Potential Sales Value</span>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                        <i class="bi bi-graph-up-arrow fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ \App\Support\Money::format($kiranaSalesPaise + $hardwareSalesPaise) }}</h4>
                <div class="small text-muted">
                    Kirana: <span class="fw-semibold text-dark">{{ \App\Support\Money::format($kiranaSalesPaise) }}</span>
                </div>
                <div class="small text-muted">
                    Hardware: <span class="fw-semibold text-dark">{{ \App\Support\Money::format($hardwareSalesPaise) }}</span>
                </div>
            </div>
        </div>

        <!-- Combined Gross Profit Potential -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Projected Gross Profit</span>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-2">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                </div>
                @php 
                    $totalProfitPaise = $kiranaGrossProfitPaise + $hardwareGrossProfitPaise;
                    $totalSalesPaise = $kiranaSalesPaise + $hardwareSalesPaise;
                    $avgMargin = $totalSalesPaise > 0 ? round(($totalProfitPaise / $totalSalesPaise) * 100, 1) : 0;
                @endphp
                <h4 class="fw-bold mb-1 text-success">{{ \App\Support\Money::format($totalProfitPaise) }}</h4>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-success rounded-pill px-2 py-1">{{ $avgMargin }}% Avg Margin</span>
                    <span class="small text-muted">across all 114 items</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Official October 2026 Benchmark Price Monitoring System -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-3">
                    <i class="bi bi-speedometer fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Official Price Monitoring System — Benchmark Tracker</h6>
                    <small class="text-muted">Official All-India Average Retail Prices on 10 October 2026 vs DhuriyaMart Wholesale &amp; Retail Rates</small>
                </div>
            </div>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                Data Source: Ministry of Consumer Affairs (10 Oct 2026)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Essential Commodity</th>
                        <th>Standard Unit</th>
                        <th class="text-center">Official All-India Avg</th>
                        <th class="text-center">DhuriyaMart Wholesale Buy</th>
                        <th class="text-center">DhuriyaMart Retail Price</th>
                        <th class="text-center">Gross Margin</th>
                        <th>Benchmark Alignment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($officialBenchmarks as $b)
                        @php
                            $margin = $b['our_retail'] - $b['our_buy'];
                            $marginPct = round(($margin / $b['our_retail']) * 100, 1);
                            $diffFromGov = round($b['our_retail'] - $b['official_avg'], 2);
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                {{ $b['item'] }}
                                <div class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $b['our_sku'] }}</div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ $b['unit'] }}</span></td>
                            <td class="text-center font-monospace fw-bold text-primary fs-6">
                                ₹{{ number_format($b['official_avg'], 2) }}
                            </td>
                            <td class="text-center font-monospace fw-semibold text-danger">
                                ₹{{ number_format($b['our_buy'], 2) }}
                            </td>
                            <td class="text-center font-monospace fw-bold text-success fs-6">
                                ₹{{ number_format($b['our_retail'], 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1">
                                    +₹{{ number_format($margin, 2) }} ({{ $marginPct }}%)
                                </span>
                            </td>
                            <td>
                                @if(abs($diffFromGov) <= 1.0)
                                    <span class="badge bg-success rounded-pill">
                                        <i class="bi bi-shield-check me-1"></i> Matches Official ({{ $diffFromGov >= 0 ? '+' : '' }}₹{{ $diffFromGov }})
                                    </span>
                                @elseif($diffFromGov < 0)
                                    <span class="badge bg-info text-dark rounded-pill">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Below Benchmark (-₹{{ abs($diffFromGov) }})
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Premium Grade (+₹{{ $diffFromGov }})
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light border-0 py-2 px-4 small text-muted">
            <i class="bi bi-info-circle text-primary me-1"></i>
            <em>The official all-India average retail price on 10 October 2026 was ₹37.72/kg for atta, ₹45.81/kg for rice, ₹127.81/kg for arhar dal and ₹55.43/kg for sugar; local buying rates and grades differ. For branded packaged goods, actual selling prices must follow the applicable printed MRP rules.</em>
        </div>
    </div>

    <!-- Final ₹10 Lakh Master Business Plan & Capital Allocation -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                    <i class="bi bi-pie-chart fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Final ₹10 Lakh Comprehensive Business Plan</h6>
                    <small class="text-muted">Master capital expenditure and working capital allocation breakdown</small>
                </div>
            </div>
            <span class="badge bg-success text-white rounded-pill px-3 py-2 fs-6">
                Total Budget: ₹10,00,000
            </span>
        </div>
        <div class="card-body p-4 pt-1">
            <div class="row g-3 mb-4">
                @foreach($businessPlan as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-4 p-3 bg-light bg-opacity-50 h-100 position-relative">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-{{ $plan['color'] }} bg-opacity-10 text-{{ $plan['color'] }} rounded-3 p-2 me-2">
                                        <i class="bi {{ $plan['icon'] }} fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">{{ $plan['item'] }}</h6>
                                        <span class="badge bg-white border text-secondary" style="font-size: 0.7rem;">{{ $plan['category'] }}</span>
                                    </div>
                                </div>
                                <span class="badge bg-{{ $plan['color'] }} rounded-pill font-monospace">
                                    {{ $plan['percentage'] }}%
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark font-monospace mb-1">
                                ₹{{ number_format($plan['budget_rupees']) }}
                            </div>
                            <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                                {{ $plan['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Formulas Box -->
            <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calculator-fill fs-3 text-primary me-3"></i>
                    <div>
                        <div class="fw-bold text-dark small">Inventory Valuation &amp; Profit Formulas:</div>
                        <div class="font-monospace text-primary small">
                            Stock purchase cost = Quantity &times; Buy price &nbsp;|&nbsp; Gross profit = Sales value - Stock cost
                        </div>
                    </div>
                </div>
                <div class="bg-white px-3 py-2 rounded-3 border small shadow-sm">
                    <strong>Worked Example:</strong> 100 LED bulbs @ ₹55 buy = <span class="text-danger fw-bold">₹5,500</span> cost. Sold @ ₹80 retail = <span class="text-primary fw-bold">₹8,000</span> sales. Gross profit = <span class="text-success fw-bold">₹2,500</span>.
                </div>
            </div>
        </div>
    </div>

    <!-- Category Budget Ceilings -->
    <div class="row g-4 mb-4">
        <!-- Kirana Budget Ceiling -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-basket3 text-success fs-4 me-2"></i>
                            <h6 class="fw-bold mb-0 text-dark">Kirana Category Target Budget (₹4,00,000)</h6>
                        </div>
                        <span class="badge bg-success rounded-pill">Total: ₹4,00,000</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Subcategory</th>
                                <th class="text-end">Target Budget</th>
                                <th class="text-end">Procurement Cost</th>
                                <th class="text-end">Buffer / Extras</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $kiranaTargets = [
                                    'Grains, Flour & Pulses' => 125000,
                                    'Oils, Sugar, Salt & Spices' => 100000,
                                    'Packaged Food & Snacks' => 65000,
                                    'Cleaning & Personal Care' => 85000,
                                ];
                            @endphp
                            @foreach($kiranaTargets as $catName => $targetRupees)
                                @php
                                    $variantsInCat = $kiranaVariants->filter(fn($v) => ($v->product->category->name ?? '') === $catName);
                                    $actualCost = $variantsInCat->sum(fn($v) => ($v->cost_price_paise ?? 0) * $v->stock_quantity) / 100;
                                    $buffer = max(0, $targetRupees - $actualCost);
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $catName }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($targetRupees) }}</td>
                                    <td class="text-end font-monospace text-primary fw-bold">₹{{ number_format($actualCost) }}</td>
                                    <td class="text-end font-monospace text-success">+₹{{ number_format($buffer) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-light fw-bold">
                                <td>Additional fast-moving &amp; replenishment stock</td>
                                <td class="text-end font-monospace">₹25,000</td>
                                <td class="text-end font-monospace text-muted">-</td>
                                <td class="text-end font-monospace text-success">+₹25,000</td>
                            </tr>
                            <tr class="table-success fw-bold fs-6">
                                <td>Kirana Category Total</td>
                                <td class="text-end font-monospace">₹4,00,000</td>
                                <td class="text-end font-monospace text-primary">₹{{ number_format($kiranaCostPaise / 100) }}</td>
                                <td class="text-end font-monospace text-success">₹{{ number_format($kiranaBufferPaise / 100) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Hardware Budget Ceiling -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-tools text-primary fs-4 me-2"></i>
                            <h6 class="fw-bold mb-0 text-dark">Hardware Category Target Budget (₹4,00,000)</h6>
                        </div>
                        <span class="badge bg-primary rounded-pill">Total: ₹4,00,000</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Subcategory</th>
                                <th class="text-end">Target Budget</th>
                                <th class="text-end">Procurement Cost</th>
                                <th class="text-end">Buffer / Extras</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $hardwareTargets = [
                                    'Screws, Nails, Locks & Fittings' => 75000,
                                    'Plumbing & Sanitary Fittings' => 90000,
                                    'Electrical Goods & Lighting' => 85000,
                                    'Paint, Adhesives & Waterproofing' => 80000,
                                    'Hand Tools & Construction Essentials' => 70000,
                                ];
                            @endphp
                            @foreach($hardwareTargets as $catName => $targetRupees)
                                @php
                                    $variantsInCat = $hardwareVariants->filter(fn($v) => ($v->product->category->name ?? '') === $catName);
                                    $actualCost = $variantsInCat->sum(fn($v) => ($v->cost_price_paise ?? 0) * $v->stock_quantity) / 100;
                                    $buffer = max(0, $targetRupees - $actualCost);
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $catName }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($targetRupees) }}</td>
                                    <td class="text-end font-monospace text-primary fw-bold">₹{{ number_format($actualCost) }}</td>
                                    <td class="text-end font-monospace text-success">+₹{{ number_format($buffer) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-primary fw-bold fs-6">
                                <td>Hardware Category Total</td>
                                <td class="text-end font-monospace">₹4,00,000</td>
                                <td class="text-end font-monospace text-primary">₹{{ number_format($hardwareCostPaise / 100) }}</td>
                                <td class="text-end font-monospace text-success">₹{{ number_format($hardwareBufferPaise / 100) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Opening Stock Lists with Interactive Tabs -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4">
            <ul class="nav nav-pills card-header-pills" id="stockTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="kirana-tab" data-bs-toggle="pill" data-bs-target="#kiranaStockPane" type="button" role="tab">
                        <i class="bi bi-basket3 me-1"></i> Kirana Opening Stock List ({{ $kiranaVariants->count() }} items)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="hardware-tab" data-bs-toggle="pill" data-bs-target="#hardwareStockPane" type="button" role="tab">
                        <i class="bi bi-tools me-1"></i> Hardware Opening Stock List ({{ $hardwareVariants->count() }} items)
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="stockTabsContent">
            <!-- Kirana Tab -->
            <div class="tab-pane fade show active" id="kiranaStockPane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-center">Opening Qty</th>
                                <th class="text-end">Buy / Unit</th>
                                <th class="text-end">Retail / Unit</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Sales Value</th>
                                <th class="text-end">Gross Profit</th>
                                <th class="text-center">Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kiranaVariants as $v)
                                @php
                                    $cost = ($v->cost_price_paise ?? 0) * $v->stock_quantity;
                                    $sales = $v->price_paise * $v->stock_quantity;
                                    $profit = max(0, $sales - $cost);
                                    $margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $v->product->name }}</div>
                                        <div class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $v->sku }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill">{{ $v->product->category->name ?? 'Kirana' }}</span>
                                    </td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $v->name }}</span></td>
                                    <td class="text-center font-monospace fw-bold">{{ $v->stock_quantity }}</td>
                                    <td class="text-end font-monospace text-danger">₹{{ number_format(($v->cost_price_paise ?? 0) / 100, 2) }}</td>
                                    <td class="text-end font-monospace text-success fw-bold">₹{{ number_format($v->price_paise / 100, 2) }}</td>
                                    <td class="text-end font-monospace fw-semibold">{{ \App\Support\Money::format($cost) }}</td>
                                    <td class="text-end font-monospace fw-bold text-primary">{{ \App\Support\Money::format($sales) }}</td>
                                    <td class="text-end font-monospace fw-bold text-success">{{ \App\Support\Money::format($profit) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">
                                            {{ $margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-success fw-bold font-monospace fs-6">
                            <tr>
                                <td colspan="3">Kirana Opening Stock Totals ({{ $kiranaVariants->count() }} items)</td>
                                <td class="text-center">{{ $kiranaVariants->sum('stock_quantity') }} units</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end">{{ \App\Support\Money::format($kiranaCostPaise) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($kiranaSalesPaise) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($kiranaGrossProfitPaise) }}</td>
                                <td class="text-center">{{ round(($kiranaGrossProfitPaise / max(1, $kiranaSalesPaise)) * 100, 1) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Hardware Tab -->
            <div class="tab-pane fade" id="hardwareStockPane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-center">Opening Qty</th>
                                <th class="text-end">Buy / Unit</th>
                                <th class="text-end">Retail / Unit</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Sales Value</th>
                                <th class="text-end">Gross Profit</th>
                                <th class="text-center">Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hardwareVariants as $v)
                                @php
                                    $cost = ($v->cost_price_paise ?? 0) * $v->stock_quantity;
                                    $sales = $v->price_paise * $v->stock_quantity;
                                    $profit = max(0, $sales - $cost);
                                    $margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $v->product->name }}</div>
                                        <div class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $v->sku }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill">{{ $v->product->category->name ?? 'Hardware' }}</span>
                                    </td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $v->name }}</span></td>
                                    <td class="text-center font-monospace fw-bold">{{ $v->stock_quantity }}</td>
                                    <td class="text-end font-monospace text-danger">₹{{ number_format(($v->cost_price_paise ?? 0) / 100, 2) }}</td>
                                    <td class="text-end font-monospace text-success fw-bold">₹{{ number_format($v->price_paise / 100, 2) }}</td>
                                    <td class="text-end font-monospace fw-semibold">{{ \App\Support\Money::format($cost) }}</td>
                                    <td class="text-end font-monospace fw-bold text-primary">{{ \App\Support\Money::format($sales) }}</td>
                                    <td class="text-end font-monospace fw-bold text-success">{{ \App\Support\Money::format($profit) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">
                                            {{ $margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary fw-bold font-monospace fs-6">
                            <tr>
                                <td colspan="3">Hardware Opening Stock Totals ({{ $hardwareVariants->count() }} items)</td>
                                <td class="text-center">{{ $hardwareVariants->sum('stock_quantity') }} units</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end">{{ \App\Support\Money::format($hardwareCostPaise) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($hardwareSalesPaise) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($hardwareGrossProfitPaise) }}</td>
                                <td class="text-center">{{ round(($hardwareGrossProfitPaise / max(1, $hardwareSalesPaise)) * 100, 1) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

