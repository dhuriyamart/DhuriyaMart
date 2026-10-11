<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['shop', 'category', 'variants', 'primaryImage']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->input('shop_id'));
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where('name', 'like', "%{$term}%");
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();
        $shops = Shop::all();

        return view('admin.products.index', compact('products', 'categories', 'shops'));
    }

    public function moderate(Request $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);

        $oldStatus = $product->status;
        $product->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null,
        ]);

        AuditLog::record(
            "product_moderation_{$validated['status']}",
            $product,
            ['status' => $oldStatus],
            ['status' => $validated['status'], 'reason' => $validated['rejection_reason'] ?? null]
        );

        return back()->with('success', "Product #{$product->id} has been {$validated['status']}.");
    }

    public function toggleFeatured(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('success', "Featured status toggled for {$product->name}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        AuditLog::record('product_deleted', $product, ['name' => $product->name, 'shop_id' => $product->shop_id]);
        $product->delete();

        return back()->with('success', 'Product permanently deleted.');
    }
}
