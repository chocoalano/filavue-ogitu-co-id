<?php

namespace App\Repositories\Products\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get paginated products with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    /** @return \Illuminate\Support\Collection */
    public function getActiveCategories();

    /** @return \Illuminate\Support\Collection */
    public function getActiveBrands();

    /** @return array<string, mixed> */
    public function getFilterStats();

    public function getBySlugWithDetails(string $slug): Product;

    /** @return Collection<int, Product> */
    public function getRecommendations(Product $product, int $limit = 4): Collection;

    public function getApprovedReviewsPaginated(int $productId, int $perPage = 8): LengthAwarePaginator;
}
