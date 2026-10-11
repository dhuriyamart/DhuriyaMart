<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::published()
            ->featured()
            ->with(['shop', 'category', 'images', 'variants'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getBestsellers(int $limit = 8): Collection
    {
        return Product::published()
            ->with(['shop', 'category', 'images', 'variants'])
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->take($limit)
            ->get();
    }

    public function getNewArrivals(int $limit = 8): Collection
    {
        return Product::published()
            ->with(['shop', 'category', 'images', 'variants'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function searchProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::published()
            ->with(['shop', 'category', 'images', 'variants']);

        if (!empty($filters['q'])) {
            $term = trim($filters['q']);
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('variants', function (Builder $vq) use ($term) {
                        $vq->where('sku', 'like', "%{$term}%")
                           ->orWhere('name', 'like', "%{$term}%");
                    });
            });
        }

        if (!empty($filters['category'])) {
            $catSlug = $filters['category'];
            $category = Category::where('slug', $catSlug)->first();
            if ($category) {
                $catIds = $category->children()->pluck('id')->prepend($category->id);
                $query->whereIn('category_id', $catIds);
            }
        }

        if (!empty($filters['shop'])) {
            $shopSlug = $filters['shop'];
            $query->whereHas('shop', fn($q) => $q->where('slug', $shopSlug));
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $minPaise = (int) ($filters['min_price'] * 100);
            $query->where('base_price_paise', '>=', $minPaise);
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $maxPaise = (int) ($filters['max_price'] * 100);
            $query->where('base_price_paise', '<=', $maxPaise);
        }

        if (!empty($filters['in_stock'])) {
            $query->whereHas('variants', function (Builder $q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'price_asc' => $query->orderBy('base_price_paise', 'asc'),
            'price_desc' => $query->orderBy('base_price_paise', 'desc'),
            'popularity' => $query->withCount('reviews')->orderByDesc('reviews_count'),
            'rating' => $query->withAvg('approvedReviews', 'rating')->orderByDesc('approved_reviews_avg_rating'),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return Product::published()
            ->where('slug', $slug)
            ->with(['shop.profile', 'category.parent', 'images', 'variants.inventoryMovements', 'approvedReviews.user'])
            ->first();
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::published()
            ->where('id', '!=', $product->id)
            ->where(function (Builder $q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('shop_id', $product->shop_id);
            })
            ->with(['shop', 'category', 'images', 'variants'])
            ->take($limit)
            ->get();
    }

    public function getParentCategoriesWithChildren(): Collection
    {
        return Category::active()
            ->parents()
            ->with(['children' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();
    }

    public function getActiveBanners(string $position = 'hero_main'): Collection
    {
        return Banner::active()
            ->where('position', $position)
            ->orderBy('sort_order')
            ->get();
    }
}

