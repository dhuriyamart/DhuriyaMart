<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Coupon;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::withCount('redemptions')->latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $discountValue = $validated['discount_type'] === 'fixed'
            ? Money::toPaise($validated['discount_value'])
            : (int) $validated['discount_value'];

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $discountValue,
            'min_order_value_paise' => !empty($validated['min_order_value']) ? Money::toPaise($validated['min_order_value']) : 0,
            'max_discount_paise' => !empty($validated['max_discount']) ? Money::toPaise($validated['max_discount']) : null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        AuditLog::record('coupon_created', $coupon, null, $validated);

        return back()->with('success', "Coupon '{$coupon->code}' created successfully.");
    }

    public function toggle(int $id): RedirectResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return back()->with('success', "Coupon '{$coupon->code}' active status toggled.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $coupon = Coupon::findOrFail($id);
        AuditLog::record('coupon_deleted', $coupon, ['code' => $coupon->code]);
        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }
}

