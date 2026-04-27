<?php

namespace App\Repositories\Home\Contracts;

use Illuminate\Support\Collection;

interface HomeRepositoryInterface
{
    /** @return Collection<int, \App\Models\Promotion> */
    public function getActiveHomepagePromotions(): Collection;

    /** @return Collection<int, \App\Models\Product> */
    public function getBestSellingProducts(int $limit = 12): Collection;

    /** @return Collection<int, object{brand:string,product_count:int|string}> */
    public function getActiveBrands(int $limit = 20): Collection;
}

