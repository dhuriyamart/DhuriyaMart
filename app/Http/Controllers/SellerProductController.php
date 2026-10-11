<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerProductController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        $products = $shop->products()
            ->with(['category', 'variants', 'primaryImage'])
            ->latest()
            ->paginate(15);

        return view('seller.products.index', compact('shop', 'products'));
    }

    public function create(Request $request): View
    {
        $shop = $request->user()->shop;
        $categories = Category::active()->whereNull('parent_id')->with('children')->get();

        return view('seller.products.create', compact('shop', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:1',
            'compare_at_price' => 'nullable|numeric|min:1',
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'variant_name' => 'required|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'image_url' => 'nullable|url|max:500',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $basePricePaise = Money::toPaise($validated['base_price']);
        $comparePricePaise = !empty($validated['compare_at_price']) ? Money::toPaise($validated['compare_at_price']) : null;

        $slug = Str::slug($validated['name']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        DB::transaction(function () use ($shop, $validated, $basePricePaise, $comparePricePaise, $slug, $request) {
            $product = Product::create([
                'shop_id' => $shop->id,
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'short_description' => $validated['short_description'],
                'description' => $validated['description'],
                'base_price_paise' => $basePricePaise,
                'status' => 'pending', // Requires admin moderation
                'is_active' => true,
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => strtoupper($validated['sku']),
                'name' => $validated['variant_name'],
                'price_paise' => $basePricePaise,
                'compare_at_price_paise' => $comparePricePaise,
                'stock_quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => 5,
                'is_active' => true,
            ]);

            // Handle image upload or image URL
            $imagePath = $validated['image_url'] ?? null;
            if ($request->hasFile('image_file')) {
                $imagePath = $request->file('image_file')->store('products', 'public');
            }

            if (!$imagePath) {
                $imagePath = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
            }

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imagePath,
                'alt_text' => $product->name,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            // Initial stock inventory movement
            if ($validated['stock_quantity'] > 0) {
                InventoryMovement::create([
                    'product_variant_id' => $variant->id,
                    'user_id' => $request->user()->id,
                    'type' => 'restock',
                    'quantity_change' => $validated['stock_quantity'],
                    'stock_after' => $validated['stock_quantity'],
                    'reference_type' => 'product_creation',
                    'notes' => 'Initial stock on product creation',
                ]);
            }
        });

        return redirect()->route('seller.products.index')
            ->with('success', 'Product listed successfully and submitted for admin review!');
    }

    public function edit(Request $request, int $id): View
    {
        $shop = $request->user()->shop;
        $product = $shop->products()->with(['variants', 'images'])->findOrFail($id);
        $categories = Category::active()->whereNull('parent_id')->with('children')->get();

        return view('seller.products.edit', compact('product', 'categories', 'shop'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $shop = $request->user()->shop;
        $product = $shop->products()->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $basePricePaise = Money::toPaise($validated['base_price']);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'base_price_paise' => $basePricePaise,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $shop = $request->user()->shop;
        $product = $shop->products()->findOrFail($id);
        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted.');
    }
}

