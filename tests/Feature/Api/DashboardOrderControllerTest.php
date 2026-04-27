<?php

use App\Http\Controllers\Api\DashboardOrderController;
use App\Http\Requests\Dashboard\SubmitOrderItemReviewRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Dashboard\DashboardService;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('registers dashboard order api routes', function (): void {
    expect(route('api.dashboard.orders.index', [], false))->toBe('/api/dashboard/orders')
        ->and(route('api.dashboard.orders.show', ['order' => 1201], false))->toBe('/api/dashboard/orders/1201')
        ->and(route('api.dashboard.orders.review', ['order' => 1201], false))->toBe('/api/dashboard/orders/1201/review');
});

it('requires sanctum authentication for dashboard order list endpoint', function (): void {
    $this->getJson(route('api.dashboard.orders.index'))
        ->assertUnauthorized();
});

it('requires sanctum authentication for dashboard order detail endpoint', function (): void {
    $this->getJson(route('api.dashboard.orders.show', ['order' => 12]))
        ->assertUnauthorized();
});

it('returns dashboard order list payload for authenticated customer', function (): void {
    $customer = makeDashboardOrderCustomer(2201);

    $requestPayload = [
        'page' => 2,
        'per_page' => 25,
        'q' => 'ORD-2026',
        'status' => 'unpaid',
        'sort' => 'highest',
        'date_from' => '2026-03-01',
        'date_to' => '2026-03-31',
    ];

    $expectedOrders = [
        'data' => [
            [
                'id' => 501,
                'code' => 'ORD-20260301-001',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total' => 550000,
            ],
        ],
        'current_page' => 2,
        'next_page' => 3,
        'has_more' => true,
        'per_page' => 25,
        'total' => 80,
    ];

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer, $expectedOrders): void {
        $mock->shouldReceive('getOrdersPagination')
            ->once()
            ->withArgs(function (
                Customer $authenticatedCustomer,
                int $page,
                int $perPage,
                array $filters
            ) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $page === 2
                    && $perPage === 25
                    && ($filters['q'] ?? null) === 'ORD-2026'
                    && ($filters['status'] ?? null) === 'unpaid'
                    && ($filters['sort'] ?? null) === 'highest'
                    && ($filters['date_from'] ?? null) === '2026-03-01'
                    && ($filters['date_to'] ?? null) === '2026-03-31';
            })
            ->andReturn($expectedOrders);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.orders.index', $requestPayload))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data order berhasil diambil.')
        ->assertJsonPath('data.current_page', 2)
        ->assertJsonPath('data.per_page', 25)
        ->assertJsonPath('data.total', 80)
        ->assertJsonPath('data.data.0.id', 501)
        ->assertJsonPath('data.data.0.code', 'ORD-20260301-001');
});

it('validates dashboard order list filters', function (): void {
    Sanctum::actingAs(makeDashboardOrderCustomer(2202), ['customer:api']);

    $this->getJson(route('api.dashboard.orders.index', [
        'page' => 0,
        'per_page' => 101,
        'status' => 'wrong-status',
        'sort' => 'not-valid',
        'date_from' => '2026-03-15',
        'date_to' => '2026-03-10',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page', 'per_page', 'status', 'sort', 'date_to']);
});

it('rejects customer id injection on dashboard order list endpoint', function (): void {
    Sanctum::actingAs(makeDashboardOrderCustomer(2206), ['customer:api']);

    $this->getJson(route('api.dashboard.orders.index', [
        'customer_id' => 9999,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

it('returns dashboard order detail payload for authenticated customer', function (): void {
    $customer = makeDashboardOrderCustomer(2203);

    $expectedOrder = [
        'id' => 1201,
        'code' => 'ORD-20260301-ABC123',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 500000,
        'shipping_cost' => 25000,
        'tax_amount' => 25000,
        'discount_amount' => 0,
        'total' => 550000,
        'items_count' => 2,
        'items' => [],
        'items_preview' => [],
        'customer' => [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
        ],
    ];

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer, $expectedOrder): void {
        $mock->shouldReceive('getOrderDetail')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer, int $orderId) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $orderId === 1201;
            })
            ->andReturn($expectedOrder);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.orders.show', ['order' => 1201]))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Detail order berhasil diambil.')
        ->assertJsonPath('data.id', 1201)
        ->assertJsonPath('data.code', 'ORD-20260301-ABC123')
        ->assertJsonPath('data.customer.name', 'Budi Santoso');
});

it('returns validation error when dashboard order detail is not found', function (): void {
    $customer = makeDashboardOrderCustomer(2204);

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('getOrderDetail')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer, int $orderId) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $orderId === 99999;
            })
            ->andThrow(
                ValidationException::withMessages([
                    'order' => ['Order tidak ditemukan.'],
                ])
            );
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.orders.show', ['order' => 99999]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Order tidak ditemukan.')
        ->assertJsonPath('errors.order.0', 'Order tidak ditemukan.');
});

it('validates dashboard order detail route parameter', function (): void {
    Sanctum::actingAs(makeDashboardOrderCustomer(2205), ['customer:api']);

    $this->getJson(route('api.dashboard.orders.show', ['order' => 0]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order']);
});

it('rejects customer id injection on dashboard order detail endpoint', function (): void {
    Sanctum::actingAs(makeDashboardOrderCustomer(2207), ['customer:api']);

    $this->getJson(route('api.dashboard.orders.show', [
        'order' => 1201,
        'customer_id' => 9999,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

it('returns unauthorized when api review submission is called without sanctum customer', function (): void {
    $order = new Order([
        'customer_id' => 2208,
    ]);
    $order->setAttribute('id', 3201);
    $order->exists = true;

    $request = SubmitOrderItemReviewRequest::create('/api/dashboard/orders/3201/review', 'POST', [
        'order_item_id' => 9001,
        'rating' => 5,
        'comment' => 'Produk bagus',
    ]);
    $request->setUserResolver(static fn (?string $guard = null): ?Customer => null);

    $controller = new DashboardOrderController(\Mockery::mock(DashboardService::class));
    $response = $controller->submitReview($request, $order);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))
        ->toMatchArray([
            'message' => 'Tidak terautentikasi.',
        ]);
});

it('returns review submission payload for authenticated customer', function (): void {
    $customer = makeDashboardOrderCustomer(2209);
    $order = new Order([
        'customer_id' => 2209,
    ]);
    $order->setAttribute('id', 3202);
    $order->exists = true;

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('submitOrderItemReview')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer, int $orderId, array $payload) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $orderId === 3202
                    && (int) ($payload['order_item_id'] ?? 0) === 9002
                    && (int) ($payload['rating'] ?? 0) === 5
                    && ($payload['title'] ?? null) === 'Sangat puas'
                    && ($payload['comment'] ?? null) === 'Produk sesuai deskripsi.';
            })
            ->andReturn([
                'message' => 'Ulasan berhasil dikirim. Menunggu persetujuan admin.',
                'order' => [
                    'id' => 3202,
                    'code' => 'ORD-3202',
                    'status' => 'delivered',
                    'pending_review_count' => 0,
                    'has_pending_review' => false,
                ],
                'review' => [
                    'id' => 7801,
                    'order_item_id' => 9002,
                    'rating' => 5,
                    'title' => 'Sangat puas',
                    'comment' => 'Produk sesuai deskripsi.',
                    'is_approved' => false,
                ],
            ]);
    });

    $request = SubmitOrderItemReviewRequest::create('/api/dashboard/orders/3202/review', 'POST', [
        'order_item_id' => 9002,
        'rating' => 5,
        'title' => 'Sangat puas',
        'comment' => 'Produk sesuai deskripsi.',
    ]);
    $request->setUserResolver(static fn (?string $guard = null): ?Customer => $guard === 'sanctum' ? $customer : null);

    $controller = app(DashboardOrderController::class);
    $response = $controller->submitReview($request, $order);
    $payload = $response->getData(true);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['message'] ?? null)->toBe('Ulasan berhasil dikirim. Menunggu persetujuan admin.')
        ->and($payload['data']['order']['id'] ?? null)->toBe(3202)
        ->and($payload['data']['review']['order_item_id'] ?? null)->toBe(9002)
        ->and($payload['data']['review']['is_approved'] ?? null)->toBeFalse();
});

function makeDashboardOrderCustomer(int $id): Customer
{
    $customer = new Customer;
    $customer->forceFill([
        'name' => 'Customer '.$id,
        'username' => 'member'.$id,
        'email' => 'member'.$id.'@example.test',
        'phone' => '08123456789',
        'password' => bcrypt('secret123'),
        'status' => 3,
    ]);
    $customer->setAttribute('id', $id);
    $customer->exists = true;

    return $customer;
}
