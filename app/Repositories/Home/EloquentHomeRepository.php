<?php

namespace App\Repositories\Home;

use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Repositories\Home\Contracts\HomeRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentHomeRepository implements HomeRepositoryInterface
{
    public function getActiveHomepagePromotions(): Collection
    {
        return Promotion::query()
            ->where('is_active', true)
            ->where('show_on', 'homepage')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->orderByDesc('priority')
            ->get(['id', 'name', 'description', 'image', 'landing_slug', 'type', 'code']);
    }

    public function getBestSellingProducts(int $limit = 12): Collection
    {
        $paidOrderIds = Order::query()
            ->whereIn('status', ['PAID', 'shipped', 'delivered'])
            ->select('id');

        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('commodity_code')
            ->withSum([
                'orderItems as sales_count' => fn ($query) => $query->whereIn('order_id', $paidOrderIds),
            ], 'qty')
            ->withAvg(['reviews as rating_avg' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn ($query) => $query->where('is_approved', true)])
            ->with(['media' => fn ($query) => $query->where('is_primary', true)->orderBy('sort_order')->limit(1)])
            ->orderByDesc('sales_count')
            ->limit($limit)
            ->get();
    }

    public function getActiveBrands(int $limit = 20): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('brand, COUNT(*) as product_count')
            ->groupBy('brand')
            ->orderByDesc('product_count')
            ->limit($limit)
            ->get();
    }
}

