<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Shop;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService
    ) {}

    public function category(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $filters = $request->only(['q', 'min_price', 'max_price', 'in_stock', 'sort']);
        $filters['category'] = $slug;

        $products = $this->catalogService->searchProducts($filters, 12);
        $categories = $this->catalogService->getParentCategoriesWithChildren();

        return view('storefront.catalog', compact('category', 'products', 'categories', 'filters'));
    }

    public function product(string $slug): View
    {
        $product = $this->catalogService->getProductBySlug($slug);
        if (! $product) {
            abort(404, 'Product not found or currently unavailable.');
        }

        $relatedProducts = $this->catalogService->getRelatedProducts($product, 4);

        return view('storefront.product-detail', compact('product', 'relatedProducts'));
    }

    public function search(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'shop', 'min_price', 'max_price', 'in_stock', 'sort']);
        $products = $this->catalogService->searchProducts($filters, 12);
        $categories = $this->catalogService->getParentCategoriesWithChildren();
        $query = $filters['q'] ?? '';

        return view('storefront.search', compact('products', 'categories', 'filters', 'query'));
    }

    public function shop(string $slug, Request $request): View
    {
        $shop = Shop::where('slug', $slug)->where('status', 'approved')->firstOrFail();
        $filters = $request->only(['q', 'category', 'min_price', 'max_price', 'in_stock', 'sort']);
        $filters['shop'] = $slug;

        $products = $this->catalogService->searchProducts($filters, 12);

        return view('storefront.shop', compact('shop', 'products', 'filters'));
    }

    public function page(string $slug): View
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('storefront.page', compact('page'));
    }
}
