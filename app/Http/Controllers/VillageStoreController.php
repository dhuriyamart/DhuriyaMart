<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VillageStoreController extends Controller
{
    /**
     * Multi-Store Village Network Dashboard.
     */
    public function index(): View
    {
        $stores = Store::with(['manager', 'cashRegisters' => fn ($q) => $q->latest()->take(1)])
            ->withCount(['orders', 'posSessions'])
            ->get();

        $managers = User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['admin', 'super-admin', 'store-manager']))
            ->get();

        // Calculate network-wide metrics
        $totalSalesPaise = Order::where('payment_status', 'paid')->sum('total_amount_paise');
        $totalOrdersCount = Order::count();
        $activeRegistersCount = CashRegister::where('status', 'open')->count();

        return view('admin.village-stores.index', [
            'stores' => $stores,
            'managers' => $managers,
            'totalSalesRupees' => round($totalSalesPaise / 100, 2),
            'totalOrdersCount' => $totalOrdersCount,
            'activeRegistersCount' => $activeRegistersCount,
        ]);
    }

    /**
     * Register a new Village Branch Store.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:stores,code',
            'name' => 'required|string|max:150',
            'village_name' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:20',
            'phone' => 'nullable|string|max:30',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Store::create($request->all());

        return back()->with('success', "New Village Smart Mart branch '{$request->name}' added to the network!");
    }

    /**
     * Toggle branch active status.
     */
    public function toggle(int $id): RedirectResponse
    {
        $store = Store::findOrFail($id);
        $store->is_active = ! $store->is_active;
        $store->save();

        $status = $store->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Store '{$store->name}' has been {$status}.");
    }
}
