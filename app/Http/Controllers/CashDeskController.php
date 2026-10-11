<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashRegisterEntry;
use App\Models\Store;
use App\Models\User;
use App\Services\CashDeskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashDeskController extends Controller
{
    public function __construct(
        protected CashDeskService $cashDeskService
    ) {}

    /**
     * Get or create active primary store.
     */
    private function getPrimaryStore(): Store
    {
        return Store::where('is_active', true)->first()
            ?? Store::firstOrCreate(
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
    }

    /**
     * Cash Counter & Daily Reconciliation Register.
     */
    public function index(Request $request): View
    {
        $store = $this->getPrimaryStore();

        $activeRegister = $store->getActiveRegister();
        $date = $request->input('date', now()->toDateString());
        $summary = $this->cashDeskService->getDailyReconciliation($store, $date);

        $recentRegisters = CashRegister::with(['openedBy', 'closedBy'])
            ->where('store_id', $store->id)
            ->latest('register_date')
            ->take(10)
            ->get();

        $entries = $activeRegister
            ? CashRegisterEntry::with(['user', 'order'])
                ->where('cash_register_id', $activeRegister->id)
                ->latest()
                ->get()
            : collect();

        return view('admin.cash-desk.index', [
            'store' => $store,
            'activeRegister' => $activeRegister,
            'summary' => $summary,
            'recentRegisters' => $recentRegisters,
            'entries' => $entries,
            'selectedDate' => $date,
        ]);
    }

    /**
     * Open daily cash drawer with opening float.
     */
    public function open(Request $request): RedirectResponse
    {
        $request->validate([
            'opening_float_rupees' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $store = $this->getPrimaryStore();
        $user = $request->user();

        $paise = (int) round($request->opening_float_rupees * 100);

        $this->cashDeskService->openRegister($store, $user, $paise, $request->notes);

        return back()->with('success', "Daily cash drawer opened with opening float of ₹{$request->opening_float_rupees}.");
    }

    /**
     * Record cash in or cash out (petty expenses, supplier payment, drawer adjustment).
     */
    public function addEntry(Request $request): RedirectResponse
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'type' => 'required|in:cash_in,cash_out',
            'amount_rupees' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $register = CashRegister::findOrFail($request->cash_register_id);
        $user = $request->user();
        $paise = (int) round($request->amount_rupees * 100);

        $this->cashDeskService->recordCashEntry(
            $register,
            $user,
            $request->type,
            $paise,
            null,
            $request->reason
        );

        $typeLabel = $request->type === 'cash_in' ? 'Cash In (Deposit)' : 'Cash Out (Expense)';

        return back()->with('success', "{$typeLabel} of ₹{$request->amount_rupees} recorded successfully.");
    }

    /**
     * Close cash drawer with physical count and reconcile.
     */
    public function close(Request $request): RedirectResponse
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'closing_actual_rupees' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $register = CashRegister::findOrFail($request->cash_register_id);
        $user = $request->user();
        $actualPaise = (int) round($request->closing_actual_rupees * 100);

        $register = $this->cashDeskService->closeRegister(
            $register,
            $user,
            $actualPaise,
            $request->notes
        );

        $diff = $register->difference_rupees;
        $statusMsg = $diff == 0
            ? 'Register closed & 100% balanced! Expected and actual matched perfectly.'
            : ($diff > 0
                ? "Register closed with cash surplus of ₹{$diff}."
                : 'Register closed with cash shortage of ₹'.abs($diff).'.');

        return back()->with('success', $statusMsg);
    }

    /**
     * Accept cash deposit from village customer to credit their digital wallet.
     */
    public function depositCustomer(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_identifier' => 'required|string',
            'amount_rupees' => 'required|numeric|min:10',
            'notes' => 'nullable|string',
        ]);

        $customer = User::where('customer_id', $request->customer_identifier)
            ->orWhere('phone', $request->customer_identifier)
            ->orWhere('qr_token', $request->customer_identifier)
            ->first();

        if (! $customer) {
            return back()->with('error', "No customer found for identifier '{$request->customer_identifier}'.");
        }

        $store = $this->getPrimaryStore();
        $cashier = $request->user();
        $paise = (int) round($request->amount_rupees * 100);

        $result = $this->cashDeskService->customerDeposit($customer, $cashier, $paise, $store, $request->notes);

        return back()->with('success', "Cash deposit of ₹{$request->amount_rupees} accepted for {$customer->name}. New balance: ₹{$result['new_balance_rupees']}.");
    }
}
