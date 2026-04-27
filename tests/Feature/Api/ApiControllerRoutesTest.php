<?php

use App\Services\Home\HomeService;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Mockery\MockInterface;

it('registers main api routes', function (): void {
    expect(route('api.home.index', [], false))->toBe('/api/home')
        ->and(route('api.shop.index', [], false))->toBe('/api/shop')
        ->and(route('api.articles.index', [], false))->toBe('/api/articles')
        ->and(route('api.pages.show', ['slug' => 'tentang-kami'], false))->toBe('/api/pages/tentang-kami')
        ->and(route('api.newsletter.subscribe', [], false))->toBe('/api/newsletter/subscribe')
        ->and(route('api.auth.login-meta', [], false))->toBe('/api/auth/login-meta')
        ->and(route('api.auth.register-meta', [], false))->toBe('/api/auth/register-meta')
        ->and(route('api.auth.register', [], false))->toBe('/api/auth/register')
        ->and(route('api.auth.login', [], false))->toBe('/api/auth/login')
        ->and(route('api.auth.impersonation.stop', [], false))->toBe('/api/auth/impersonation/stop')
        ->and(route('api.dashboard.index', [], false))->toBe('/api/dashboard')
        ->and(route('api.dashboard.leaderboards', [], false))->toBe('/api/dashboard/leaderboards')
        ->and(route('api.cart.items.store', [], false))->toBe('/api/cart/items');
});

it('returns api health payload', function (): void {
    $this->getJson(route('api.health'))
        ->assertOk()
        ->assertExactJson([
            'status' => 'ok',
        ]);
});

it('returns home data through api beranda controller', function (): void {
    $this->mock(HomeService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getIndexPageData')
            ->once()
            ->andReturn([
                'heroBanners' => fn (): array => [
                    ['id' => 1, 'name' => 'Promo A'],
                ],
                'featuredProducts' => fn (): array => [
                    ['id' => 10, 'name' => 'Produk A'],
                ],
                'brands' => fn (): array => [
                    ['name' => 'Brand X'],
                ],
            ]);
    });

    $this->getJson(route('api.home.index'))
        ->assertOk()
        ->assertJsonPath('message', 'Data beranda berhasil diambil.')
        ->assertJsonPath('data.heroBanners.0.name', 'Promo A')
        ->assertJsonPath('data.featuredProducts.0.name', 'Produk A')
        ->assertJsonPath('data.brands.0.name', 'Brand X');
});

it('subscribes newsletter through api endpoint', function (): void {
    $this->mock(NewsletterSubscriptionService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('subscribe')
            ->once()
            ->with('member@example.com', \Mockery::type('string'))
            ->andReturnTrue();
    });

    $this->postJson(route('api.newsletter.subscribe'), [
        'email' => 'member@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Berhasil berlangganan promo terbaru.')
        ->assertJsonPath('data.is_new_subscriber', true);
});

it('requires sanctum authentication for dashboard endpoint', function (): void {
    $this->getJson(route('api.dashboard.index'))
        ->assertUnauthorized();
});

it('requires sanctum authentication for dashboard leaderboard endpoint', function (): void {
    $this->getJson(route('api.dashboard.leaderboards'))
        ->assertUnauthorized();
});
