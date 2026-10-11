<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        $ledgerEntries = $shop->ledgerEntries()
            ->latest()
            ->paginate(15, ['*'], 'ledger_page');

        $settlements = $shop->settlements()
            ->with('items')
            ->latest()
            ->paginate(10, ['*'], 'settlement_page');

        $currentBalancePaise = $shop->current_balance_paise;

        return view('seller.settlements.index', compact('shop', 'ledgerEntries', 'settlements', 'currentBalancePaise'));
    }
}

