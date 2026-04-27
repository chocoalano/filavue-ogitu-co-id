<?php

use App\Models\Customer;
use App\Services\Dashboard\DashboardLeaderboardService;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('returns leaderboard payload for authenticated customer', function (): void {
    $customer = makeDashboardApiCustomer(901);

    $expectedPayload = [
        'tabs' => ['Harian', 'Mingguan', 'Bulanan'],
        'selected_tab' => 2,
        'my_rank' => [
            'rank' => 12,
            'name' => 'Budi Santoso',
            'avatar' => 'BS',
            'level' => 'Gold Member',
            'trend' => 'up',
            'streak' => 7,
            'points' => 18500,
        ],
        'leaderboard' => [
            [
                'id' => 101,
                'name' => 'Andi Wijaya',
                'avatar' => 'AW',
                'level' => 'Diamond',
                'trend' => 'up',
                'streak' => 21,
                'points' => 125000,
            ],
            [
                'id' => 102,
                'name' => 'Citra Lestari',
                'avatar' => 'CL',
                'level' => 'Platinum',
                'trend' => 'down',
                'streak' => 15,
                'points' => 112500,
            ],
            [
                'id' => 103,
                'name' => 'Dewi Anggraini',
                'avatar' => 'DA',
                'level' => 'Gold',
                'trend' => 'neutral',
                'streak' => 10,
                'points' => 98400,
            ],
            [
                'id' => 104,
                'name' => 'Eko Pratama',
                'avatar' => 'EP',
                'level' => 'Gold',
                'trend' => 'up',
                'streak' => 8,
                'points' => 87500,
            ],
            [
                'id' => 105,
                'name' => 'Fajar Nugroho',
                'avatar' => 'FN',
                'level' => 'Silver',
                'trend' => 'neutral',
                'streak' => 5,
                'points' => 76400,
            ],
        ],
    ];

    $this->mock(DashboardLeaderboardService::class, function (MockInterface $mock) use ($customer, $expectedPayload): void {
        $mock->shouldReceive('getLeaderboardData')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer, string $periodKey, int $selectedTab) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && $periodKey === 'weekly'
                    && $selectedTab === 2;
            })
            ->andReturn($expectedPayload);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.dashboard.leaderboards', ['tab' => 2]))
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Leaderboard fetched successfully')
        ->assertJsonPath('data.tabs.0', 'Harian')
        ->assertJsonPath('data.tabs.1', 'Mingguan')
        ->assertJsonPath('data.tabs.2', 'Bulanan')
        ->assertJsonPath('data.selected_tab', 2)
        ->assertJsonPath('data.my_rank.rank', 12)
        ->assertJsonPath('data.my_rank.name', 'Budi Santoso')
        ->assertJsonPath('data.leaderboard.0.id', 101)
        ->assertJsonPath('data.leaderboard.0.name', 'Andi Wijaya')
        ->assertJsonCount(5, 'data.leaderboard');
});

it('validates leaderboard filter query', function (): void {
    Sanctum::actingAs(makeDashboardApiCustomer(902), ['customer:api']);

    $this->getJson(route('api.dashboard.leaderboards', ['tab' => 9]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tab']);
});

function makeDashboardApiCustomer(int $id): Customer
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
