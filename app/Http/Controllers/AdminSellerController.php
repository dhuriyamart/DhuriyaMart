<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSellerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shop::with(['user', 'profile', 'products']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('legal_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $shops = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sellers.index', compact('shops'));
    }

    public function show(int $id): View
    {
        $shop = Shop::with(['user', 'profile', 'documents', 'products.variants', 'sellerOrders'])->findOrFail($id);

        return view('admin.sellers.show', compact('shop'));
    }

    public function verify(Request $request, int $id): RedirectResponse
    {
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,suspended,rejected,pending',
            'commission_rate' => 'required|numeric|min:0|max:50',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $shop->status;
        $shop->update([
            'status' => $validated['status'],
            'commission_rate' => $validated['commission_rate'],
        ]);

        if ($shop->profile) {
            $shop->profile->update([
                'verified_at' => $validated['status'] === 'approved' ? now() : null,
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);
        }

        AuditLog::record(
            "seller_verification_{$validated['status']}",
            $shop,
            ['status' => $oldStatus],
            ['status' => $validated['status'], 'commission_rate' => $validated['commission_rate']]
        );

        return back()->with('success', "Shop '{$shop->name}' status updated to {$validated['status']}.");
    }
}

