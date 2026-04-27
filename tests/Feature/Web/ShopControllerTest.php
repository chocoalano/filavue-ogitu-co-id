<?php

use App\Services\Products\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
    $this->withoutMiddleware();
});

it('redirects to shop index when product slug is not found', function (): void {
    $this->mock(ProductService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProductShowData')
            ->once()
            ->with('slug-tidak-ditemukan', false)
            ->andThrow(new ModelNotFoundException);
    });

    $this->get(route('shop.show', ['slug' => 'slug-tidak-ditemukan']))
        ->assertRedirect(route('shop.index'))
        ->assertSessionHas('error', 'Produk tidak ditemukan.');
});

it('redirects to shop index when product payload is invalid', function (): void {
    $this->mock(ProductService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProductShowData')
            ->once()
            ->with('payload-kosong', false)
            ->andReturn([]);
    });

    $this->get(route('shop.show', ['slug' => 'payload-kosong']))
        ->assertRedirect(route('shop.index'))
        ->assertSessionHas('error', 'Produk tidak ditemukan.');
});

it('renders product show page with infinite-scroll reviews data', function (): void {
    $reviewsPaginator = new LengthAwarePaginator(
        items: collect([
            [
                'id' => 10,
                'name' => 'Reviewer Approved',
                'rating' => 5,
                'title' => 'Mantap',
                'body' => 'Produk sesuai deskripsi.',
                'date' => '2026-03-01',
                'verified' => true,
            ],
        ]),
        total: 1,
        perPage: 8,
        currentPage: 1,
        options: [
            'path' => route('shop.show', ['slug' => 'produk-contoh']),
            'pageName' => 'reviews_page',
        ],
    );

    $this->mock(ProductService::class, function (MockInterface $mock) use ($reviewsPaginator): void {
        $mock->shouldReceive('getProductShowData')
            ->once()
            ->with('produk-contoh', false)
            ->andReturn([
                'product' => [
                    'id' => 99,
                    'slug' => 'produk-contoh',
                    'name' => 'Produk Contoh',
                    'rating' => 5,
                    'reviewsCount' => 1,
                ],
                'reviews' => [],
                'recommendations' => [],
            ]);

        $mock->shouldReceive('getApprovedReviewsForInfiniteScroll')
            ->once()
            ->with(99, 8)
            ->andReturn($reviewsPaginator);
    });

    $this->get(route('shop.show', ['slug' => 'produk-contoh']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Show')
            ->where('product.id', 99)
            ->has('reviews.data', 1)
            ->where('reviews.data.0.name', 'Reviewer Approved')
            ->where('reviews.data.0.verified', true)
            ->etc());
});
