<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductReview;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Support\Media\PublicMediaUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductPromotionService $promotionService,
    ) {}

    /** @return array<string, mixed> */
    public function getShopData(array $filters = []): array
    {
        $paginator = $this->productRepository->getPaginated($filters);

        // Tambahkan data promo ke setiap produk tanpa mengubah DB
        $paginator->through(function (Product $product): array {
            $promo = $this->promotionService->resolvePrice($product);

            return array_merge($product->toArray(), [
                'primary_media' => $product->primaryMedia->map(fn ($m) => ['url' => $m->url])->toArray(),
                'promo_price' => $promo['has_promo'] ? $promo['unit_price'] : null,
                'promo_label' => $promo['promo_label'],
            ]);
        });

        return [
            'products' => $paginator,
            'categories' => $this->productRepository->getActiveCategories(),
            'brands' => $this->productRepository->getActiveBrands(),
            'filterStats' => $this->productRepository->getFilterStats(),
            'filters' => $filters,
        ];
    }

    /**
     * @return array{
     *   product: array<string, mixed>,
     *   reviews: array<int, array<string, mixed>>,
     *   recommendations: array<int, array<string, mixed>>
     * }
     *
     * @throws ModelNotFoundException
     */
    public function getProductShowData(string $slug, bool $includeReviews = true): array
    {
        $product = $this->productRepository->getBySlugWithDetails($slug);
        $recommendations = $this->productRepository->getRecommendations($product);
        $reviews = $includeReviews
            ? $this->getApprovedReviewsForInfiniteScroll((int) $product->id, 10)->items()
            : [];

        $promo = $this->promotionService->resolvePrice($product);

        $formattedProduct = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand,
            'shortDescription' => $product->short_desc,
            'description' => $product->long_desc,
            'priceFrom' => $promo['has_promo'] ? $promo['unit_price'] : $product->base_price,
            'rating' => $product->avg_rating ?? 0,
            'reviewsCount' => $product->reviews_count ?? 0,
            'highlights' => [],
            'specs' => [
                ['label' => 'Merek', 'value' => $product->brand ?? '-'],
                ['label' => 'Berat', 'value' => $product->weight_gram ? $product->weight_gram.'g' : '-'],
                ['label' => 'Garansi', 'value' => $product->warranty_months ? $product->warranty_months.' Bulan' : 'Tidak ada'],
                ['label' => 'Dimensi', 'value' => $product->length_mm && $product->width_mm && $product->height_mm ? "{$product->length_mm}x{$product->width_mm}x{$product->height_mm} mm" : '-'],
            ],
            'media' => $product->media->map(fn ($m) => [
                'url' => PublicMediaUrl::resolve($m->url),
                'alt' => $m->alt_text ?? $product->name,
            ])->toArray(),
            'variants' => [
                [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => 'Default',
                    'price' => $promo['has_promo'] ? $promo['unit_price'] : $product->base_price,
                    'compareAtPrice' => $promo['has_promo'] ? $product->base_price : null,
                    'promoLabel' => $promo['promo_label'],
                    'inStock' => $product->stock > 0,
                    'stock' => $product->stock,
                    'options' => [],
                    'media' => $product->primaryMedia->map(fn ($m) => [
                        'url' => PublicMediaUrl::resolve($m->url),
                        'alt' => $m->alt_text ?? $product->name,
                    ])->toArray(),
                ],
            ],
        ];

        $formattedRecommendations = $recommendations->map(function ($r): array {
            $rPromo = $this->promotionService->resolvePrice($r);

            return [
                'id' => $r->id,
                'slug' => $r->slug,
                'name' => $r->name,
                'price' => $rPromo['has_promo'] ? $rPromo['unit_price'] : $r->base_price,
                'compareAtPrice' => $rPromo['has_promo'] ? $r->base_price : null,
                'image' => $r->primaryMedia->first()
                    ? PublicMediaUrl::resolve($r->primaryMedia->first()->url)
                    : null,
                'rating' => $r->avg_rating ?? 0,
                'reviewsCount' => $r->reviews_count ?? 0,
                'badge' => $rPromo['promo_label'],
            ];
        })->toArray();

        return [
            'product' => $formattedProduct,
            'reviews' => $reviews,
            'recommendations' => $formattedRecommendations,
        ];
    }

    public function getApprovedReviewsForInfiniteScroll(int $productId, int $perPage = 8): LengthAwarePaginator
    {
        return $this->productRepository
            ->getApprovedReviewsPaginated($productId, $perPage)
            ->through(fn (ProductReview $review): array => $this->formatReview($review));
    }

    /** @return array{id:int,name:string,rating:int,title:?string,body:string,date:string,verified:bool} */
    private function formatReview(ProductReview $review): array
    {
        return [
            'id' => (int) $review->id,
            'name' => trim((string) ($review->customer?->name ?? 'User')),
            'rating' => (int) $review->rating,
            'title' => $review->title,
            'body' => trim((string) ($review->comment ?? '')),
            'date' => $review->created_at?->toDateString() ?? '',
            'verified' => (bool) ($review->is_verified_purchase ?? false),
        ];
    }
}
