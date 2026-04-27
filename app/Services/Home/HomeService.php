<?php

namespace App\Services\Home;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Promotion;
use App\Repositories\Home\Contracts\HomeRepositoryInterface;
use App\Support\Media\PublicMediaUrl;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeService
{
    public function __construct(
        protected HomeRepositoryInterface $homeRepository
    ) {}

    /**
     * @return array{
     *   heroBanners:\Closure():array<int, array<string,mixed>>,
     *   featuredProducts:\Closure():array<int, array<string,mixed>>,
     *   brands:\Closure():array<int, array<string,mixed>>,
     *   testimonials:\Closure():array<int, array<string,mixed>>
     * }
     */
    public function getIndexPageData(): array
    {
        return [
            'heroBanners' => fn (): array => $this->rememberArray(
                'hero_banners_v2',
                now()->addMinutes(30),
                fn (): array => $this->getHeroBanners()
            ),
            'featuredProducts' => fn (): array => $this->rememberArray(
                'featured_products_v2',
                now()->addMinutes(30),
                fn (): array => $this->getFeaturedProducts()
            ),
            'brands' => fn (): array => $this->rememberArray(
                'brands_showcase',
                now()->addHour(),
                fn (): array => $this->getBrands()
            ),
            'testimonials' => fn (): array => $this->rememberArray(
                'approved_testimonials_v1',
                now()->addMinutes(30),
                fn (): array => $this->getApprovedTestimonials()
            ),
        ];
    }

    /**
     * @return array<int, array{id:int,name:string,description:string|null,image:string|null,slug:string|null,type:string|null,code:string|null}>
     */
    private function getHeroBanners(): array
    {
        return $this->homeRepository
            ->getActiveHomepagePromotions()
            ->map(fn (Promotion $promotion): array => [
                'id' => (int) $promotion->id,
                'name' => (string) $promotion->name,
                'description' => $promotion->description,
                'image' => PublicMediaUrl::resolve($promotion->image),
                'slug' => $promotion->landing_slug,
                'type' => $promotion->type,
                'code' => $promotion->code,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *   id:int,
     *   slug:string|null,
     *   name:string,
     *   price:float,
     *   image:string|null,
     *   rating:float,
     *   reviewCount:int,
     *   salesCount:int,
     *   badge:string|null
     * }>
     */
    private function getFeaturedProducts(): array
    {
        return $this->homeRepository
            ->getBestSellingProducts(12)
            ->values()
            ->map(function (Product $product, int $index): array {
                $primaryMedia = $product->media->first();

                return [
                    'id' => (int) $product->id,
                    'slug' => $product->slug,
                    'name' => (string) $product->name,
                    'price' => (float) $product->base_price,
                    'image' => PublicMediaUrl::resolve($primaryMedia?->url),
                    'rating' => round((float) ($product->rating_avg ?? 0), 1),
                    'reviewCount' => (int) ($product->review_count ?? 0),
                    'salesCount' => (int) ($product->sales_count ?? 0),
                    'badge' => match (true) {
                        $index === 0 => 'Terlaris',
                        $index < 3 && ((int) ($product->sales_count ?? 0) > 0) => 'Hot',
                        default => null,
                    },
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{name:string,slug:string,productCount:int}>
     */
    private function getBrands(): array
    {
        return $this->homeRepository
            ->getActiveBrands(20)
            ->map(function (object $item): array {
                $brandName = (string) ($item->brand ?? '');

                return [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'productCount' => (int) ($item->product_count ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id:int,name:string,avatar:string|null,role:string|null,rating:int,text:string}>
     */
    private function getApprovedTestimonials(): array
    {
        return ProductReview::query()
            ->where('is_approved', true)
            ->whereNotNull('comment')
            ->with('customer:id,name', 'product:id,name')
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (ProductReview $review): array => [
                'id' => (int) $review->id,
                'name' => (string) ($review->customer?->name ?? 'Pelanggan'),
                'avatar' => null,
                'role' => $review->product?->name ?? null,
                'rating' => (int) $review->rating,
                'text' => (string) $review->comment,
            ])
            ->values()
            ->all();
    }

    /**
     * Pastikan data cache selalu berupa array,
     * termasuk jika key lama masih menyimpan Collection.
     */
    private function rememberArray(string $key, DateTimeInterface|int $ttl, \Closure $fallback): array
    {
        $cached = Cache::remember($key, $ttl, $fallback);

        if ($cached instanceof Collection) {
            $normalized = $cached->values()->all();
            Cache::put($key, $normalized, $ttl);

            return $normalized;
        }

        if (is_array($cached)) {
            return $cached;
        }

        return [];
    }
}
