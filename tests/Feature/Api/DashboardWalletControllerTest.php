<?php

use App\Models\Customer;
use App\Services\Dashboard\DashboardService;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('registers dashboard wallet transaction api route', function (): void {
    expect(route('api.dashboard.wallet.transactions.index', [], false))
        ->toBe('/api/dashboard/wallet/transactions');
});

it('requires sanctum authentication for dashboard wallet transaction list endpoint', function (): void {
    $this->getJson(route('api.dashboard.wallet.transactions.index'))
        ->assertUnauthorized();
});

it('returns dashboard wallet transaction list payload for authenticated customer', function (): void {
    $customer = makeDashboardWalletCustomer(3201);

    $requestPayload = [
        'page' => 2,
        'per_page' => 20,
        'search' => 'TOPUP-3201',
        'type' => 'topup',
        'status' => 'completed',
        'direction' => 'credit',
        'payment_method' => 'midtrans',
        'date_from' => '2026-03-01',
        'date_to' => '2026-03-31',
        'amount_min' => 10000,
        'amount_max' => 250000,
        'sort' => 'highest',
    ];

    $expectedTransactions = [
        'summary' => [
            'balance_available' => 2993250.0,
            'topup_30d' => 640000.0,
            'withdrawal_30d' => 150000.0,
            'netflow_30d' => 490000.0,
            'pending_count' => 12,
        ],
        'window' => [
            'days' => 30,
            'from' => '2026-02-02T00:00:00+07:00',
            'to' => '2026-03-03T23:59:59+07:00',
            'timezone' => 'Asia/Jakarta',
        ],
        'data' => [
            [
                'id' => 1207,
                'type' => 'topup',
                'type_label' => 'Top Up Saldo',
                'direction' => 'credit',
                'status' => 'pending',
                'status_label' => 'Menunggu',
                'amount' => 50000,
                'balance_before' => 2993250,
                'balance_after' => 2993250,
                'payment_method' => 'midtrans',
                'transaction_ref' => 'TOPUP-24-20260303180112-F24707',
                'created_at' => '2026-03-03T18:01:12+07:00',
                'completed_at' => null,
                'description' => 'Top Up Saldo • Ref: TOPUP-24-20260303180112-F24707 • MIDTRANS',
            ],
        ],
        'current_page' => 2,
        'next_page' => 3,
        'has_more' => true,
        'per_page' => 20,
        'total' => 75,
    ];

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer, $expectedTransactions): void {
        $mock->shouldReceive('getWalletTransactionsPagination')
            ->once()
            ->withArgs(function (
                Customer $authenticatedCustomer,
                int $page,
                int $perPage,
                array $filters
            ) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $page === 2
                    && $perPage === 20
                    && ($filters['search'] ?? null) === 'TOPUP-3201'
                    && ($filters['type'] ?? null) === 'topup'
                    && ($filters['status'] ?? null) === 'completed'
                    && ($filters['direction'] ?? null) === 'credit'
                    && ($filters['payment_method'] ?? null) === 'midtrans'
                    && ($filters['date_from'] ?? null) === '2026-03-01'
                    && ($filters['date_to'] ?? null) === '2026-03-31'
                    && (($filters['amount_min'] ?? null) === 10000.0)
                    && (($filters['amount_max'] ?? null) === 250000.0)
                    && ($filters['sort'] ?? null) === 'highest';
            })
            ->andReturn($expectedTransactions);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.wallet.transactions.index', $requestPayload))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data transaksi wallet berhasil diambil.')
        ->assertJsonPath('data.summary.balance_available', 2993250)
        ->assertJsonPath('data.summary.netflow_30d', 490000)
        ->assertJsonPath('data.window.days', 30)
        ->assertJsonPath('data.window.timezone', 'Asia/Jakarta')
        ->assertJsonPath('data.current_page', 2)
        ->assertJsonPath('data.per_page', 20)
        ->assertJsonPath('data.total', 75)
        ->assertJsonPath('data.data.0.id', 1207)
        ->assertJsonPath('data.data.0.transaction_ref', 'TOPUP-24-20260303180112-F24707')
        ->assertJsonPath('data.data.0.direction', 'credit');
});

it('supports q alias for dashboard wallet transaction search filter', function (): void {
    $customer = makeDashboardWalletCustomer(3204);

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('getWalletTransactionsPagination')
            ->once()
            ->withArgs(function (
                Customer $authenticatedCustomer,
                int $page,
                int $perPage,
                array $filters
            ) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $page === 1
                    && $perPage === 15
                    && ($filters['search'] ?? null) === 'bank_transfer';
            })
            ->andReturn([
                'summary' => [
                    'balance_available' => 0.0,
                    'topup_30d' => 0.0,
                    'withdrawal_30d' => 0.0,
                    'netflow_30d' => 0.0,
                    'pending_count' => 0,
                ],
                'window' => [
                    'days' => 30,
                    'from' => '2026-02-02T00:00:00+07:00',
                    'to' => '2026-03-03T23:59:59+07:00',
                    'timezone' => 'Asia/Jakarta',
                ],
                'data' => [],
                'current_page' => 1,
                'next_page' => null,
                'has_more' => false,
                'per_page' => 15,
                'total' => 0,
            ]);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.wallet.transactions.index', ['q' => 'bank_transfer']))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data transaksi wallet berhasil diambil.')
        ->assertJsonPath('data.summary.pending_count', 0)
        ->assertJsonPath('data.window.days', 30);
});

it('validates dashboard wallet transaction list filters', function (): void {
    Sanctum::actingAs(makeDashboardWalletCustomer(3202), ['customer:api']);

    $this->getJson(route('api.dashboard.wallet.transactions.index', [
        'page' => 0,
        'per_page' => 101,
        'type' => 'wrong-type',
        'status' => 'wrong-status',
        'direction' => 'wrong-direction',
        'date_from' => '2026-03-20',
        'date_to' => '2026-03-10',
        'amount_min' => -10,
        'amount_max' => -20,
        'sort' => 'not-valid',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'page',
            'per_page',
            'type',
            'status',
            'direction',
            'date_to',
            'amount_min',
            'amount_max',
            'sort',
        ]);
});

it('rejects customer id injection on dashboard wallet transaction list endpoint', function (): void {
    Sanctum::actingAs(makeDashboardWalletCustomer(3203), ['customer:api']);

    $this->getJson(route('api.dashboard.wallet.transactions.index', [
        'customer_id' => 9999,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

function makeDashboardWalletCustomer(int $id): Customer
{
    $customer = new Customer;
    $customer->forceFill([
        'name' => 'Wallet Customer '.$id,
        'username' => 'walletmember'.$id,
        'email' => 'walletmember'.$id.'@example.test',
        'phone' => '08123456789',
        'password' => bcrypt('secret123'),
        'status' => 3,
    ]);
    $customer->setAttribute('id', $id);
    $customer->exists = true;

    return $customer;
}
