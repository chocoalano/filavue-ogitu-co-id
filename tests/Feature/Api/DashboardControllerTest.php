<?php

use App\Models\Customer;
use App\Services\Dashboard\DashboardService;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('forwards order filters to dashboard service on dashboard api endpoint', function (): void {
    $customer = makeApiDashboardCustomer(4401);

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('getPageData')
            ->once()
            ->withArgs(function (
                Customer $authenticatedCustomer,
                int $ordersPage,
                int $walletPage,
                array $walletFilters,
                array $orderFilters
            ) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $ordersPage === 2
                    && $walletPage === 3
                    && ($walletFilters['search'] ?? null) === 'midtrans'
                    && ($walletFilters['type'] ?? null) === 'topup'
                    && ($walletFilters['status'] ?? null) === 'pending'
                    && ($orderFilters['q'] ?? null) === 'ORD-2026'
                    && ($orderFilters['status'] ?? null) === 'unpaid'
                    && ($orderFilters['sort'] ?? null) === 'highest'
                    && ($orderFilters['date_from'] ?? null) === null
                    && ($orderFilters['date_to'] ?? null) === null;
            })
            ->andReturn([
                'customer' => [
                    'id' => 4401,
                    'name' => 'Customer 4401',
                ],
                'orders' => [
                    'data' => [],
                    'current_page' => 2,
                    'next_page' => null,
                    'has_more' => false,
                    'per_page' => 10,
                    'total' => 0,
                    'filters' => [
                        'q' => 'ORD-2026',
                        'status' => 'unpaid',
                        'sort' => 'highest',
                        'date_from' => null,
                        'date_to' => null,
                    ],
                ],
            ]);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.index', [
        'orders_page' => 2,
        'wallet_page' => 3,
        'wallet_search' => 'midtrans',
        'wallet_type' => 'topup',
        'wallet_status' => 'pending',
        'orders_q' => 'ORD-2026',
        'orders_status' => 'unpaid',
        'orders_sort' => 'highest',
    ]))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data dashboard berhasil diambil.')
        ->assertJsonPath('data.customer.id', 4401)
        ->assertJsonPath('data.orders.current_page', 2)
        ->assertJsonPath('data.orders.filters.status', 'unpaid');
});

function makeApiDashboardCustomer(int $id): Customer
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
