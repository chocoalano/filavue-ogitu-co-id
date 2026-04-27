<?php

namespace App\Repositories\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $now = now();

        $query = Product::query()
            ->with([
                'primaryMedia',
                'categories',
                'promotions' => fn ($q) => $q
                    ->where('promotions.is_active', true)
                    ->where('promotions.start_at', '<=', $now)
                    ->where('promotions.end_at', '>=', $now)
                    ->withPivot(['min_qty', 'discount_value', 'discount_percent', 'bundle_price']),
            ])
            ->withAvg(['reviews as avg_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->whereNotNull('commodity_code')
            ->where('is_active', true);

        // Category Filter (Slug)
        if (! empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        // Price Filter
        if (! empty($filters['min_price'])) {
            $query->where('base_price', '>=', $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        // Brand Filter
        if (! empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        // Availability Filter
        if (! empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        // Rating Filter
        if (! empty($filters['rating'])) {
            $query->having('avg_rating', '>=', $filters['rating']);
        }

        // Deep Search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%")
                    ->orWhere('short_desc', 'like', "%$search%")
                    ->orWhere('long_desc', 'like', "%$search%")
                    ->orWhereHas('categories', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%$search%")
                            ->orWhere('slug', 'like', "%$search%");
                    });
            });
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'price_low' => $query->orderBy('base_price', 'asc'),
            'price_high' => $query->orderBy('base_price', 'desc'),
            'popular' => $query->orderBy('reviews_count', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getActiveCategories()
    {
        return Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'parent_id']);
    }

    public function getFilterStats(): array
    {
        return [
            'min_price' => Product::where('is_active', true)->min('base_price') ?? 0,
            'max_price' => Product::where('is_active', true)->max('base_price') ?? 1000000,
            'ratings' => [5, 4, 3, 2, 1],
        ];
    }

    public function getActiveBrands()
    {
        return Product::where('is_active', true)
            ->whereNotNull('brand')
            ->distinct()
            ->get(['brand'])
            ->map(function ($product) {
                $count = Product::where('is_active', true)
                    ->where('brand', $product->brand)
                    ->count();

                return (object) [
                    'id' => crc32($product->brand),
                    'name' => $product->brand,
                    'slug' => str($product->brand)->slug(),
                    'products_count' => $count,
                ];
            })
            ->values();
    }

    public function getBySlugWithDetails(string $slug): Product
    {
        $now = now();

        return Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'media',
                'primaryMedia',
                'categories',
                'promotions' => fn ($q) => $q
                    ->where('promotions.is_active', true)
                    ->where('promotions.start_at', '<=', $now)
                    ->where('promotions.end_at', '>=', $now)
                    ->withPivot(['min_qty', 'discount_value', 'discount_percent', 'bundle_price']),
            ])
            ->withAvg(['reviews as avg_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->firstOrFail();
    }

    public function getRecommendations(Product $product, int $limit = 4): Collection
    {
        $categoryIds = $product->categories->pluck('id');

        return Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($categoryIds->isNotEmpty(), function ($q) use ($categoryIds) {
                $q->whereHas('categories', function ($cq) use ($categoryIds) {
                    $cq->whereIn('categories.id', $categoryIds);
                });
            })
            ->with(['primaryMedia'])
            ->withAvg(['reviews as avg_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    public function getApprovedReviewsPaginated(int $productId, int $perPage = 8): LengthAwarePaginator
    {
        return ProductReview::query()
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->with(['customer:id,name'])
            ->latest('created_at')
            ->paginate(
                $perPage,
                ['id', 'customer_id', 'product_id', 'rating', 'title', 'comment', 'is_verified_purchase', 'created_at'],
                'reviews_page'
            )
            ->withQueryString();
    }
}
