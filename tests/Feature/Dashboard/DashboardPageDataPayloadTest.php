<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Mockery as M;

function buildDashboardServiceForPageDataPayloadTest(): DashboardService
{
    return new DashboardService(
        M::mock(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );
}

it('uses total bonus summary from bonus stats for dashboard home statistics', function (): void {
    $service = buildDashboardServiceForPageDataPayloadTest();
    $method = new ReflectionMethod(DashboardService::class, 'buildPageDataPayload');
    $method->setAccessible(true);

    $customer = new Customer;
    $customer->forceFill([
        'id' => 44,
        'name' => 'Member Bonus',
        'username' => 'member-bonus',
        'email' => 'member-bonus@example.test',
        'status' => 3,
        'ewallet_saldo' => 250000,
        'created_at' => now(),
    ]);
    $customer->setRelation('npwp', null);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke(
        $service,
        $customer,
        [
            'default_address' => null,
            'addresses' => collect(),
        ],
        [
            'active' => [],
            'passive' => [],
            'prospect' => [],
        ],
        [
            'has_left' => false,
            'has_right' => false,
            'tree' => null,
            'stats' => [],
            'tree_root_id' => 44,
        ],
        [
            'data' => [],
            'current_page' => 1,
            'per_page' => 15,
            'total' => 0,
            'last_page' => 1,
            'from' => null,
            'to' => null,
            'filters' => [
                'level' => null,
            ],
            'available_generations' => [],
        ],
        [
            'data' => [],
            'current_page' => 1,
            'next_page' => null,
            'has_more' => false,
            'per_page' => 15,
            'total' => 0,
            'filters' => [
                'q' => null,
                'status' => 'all',
                'sort' => 'newest',
                'date_from' => null,
                'date_to' => null,
            ],
            'pending_review_count' => 0,
            'has_pending_review' => false,
        ],
        [
            'transactions' => [],
            'has_pending_withdrawal' => false,
        ],
        [
            'bonus_stats' => [
                [
                    'key' => 'referral_incentive',
                    'title' => 'Referral Incentive',
                    'icon' => 'i-lucide-users',
                    'amount' => 150000,
                    'count' => 2,
                ],
                [
                    'key' => 'total_bonus',
                    'title' => 'Total Bonus',
                    'icon' => 'i-lucide-wallet-cards',
                    'amount' => 987654,
                    'count' => 8,
                ],
            ],
            'bonus_tables' => [
                'referral_incentive' => [],
                'team_affiliate_commission' => [],
                'partner_team_commission' => [],
                'cashback_commission' => [],
                'promotions_rewards' => [],
                'retail_commission' => [],
                'lifetime_cash_rewards' => [],
            ],
            'lifetime_rewards' => [],
        ],
        [
            'promos' => [],
            'zenner_categories' => [],
            'zenner_contents' => [],
        ],
        [
            'orders_total' => 3,
            'orders_pending' => 1,
            'active_network_members' => 2,
            'network_level' => 4,
            'bonus_available' => 45000,
            'bonus_month' => 150000,
            'bonus_lifetime' => 987654,
            'promo_active' => 1,
            'last_order_at' => now(),
            'has_npwp' => false,
            'left_count' => 1,
            'right_count' => 1,
            'total_downline' => 2,
        ],
    );

    expect(data_get($payload, 'stats.bonus_total'))->toBe(987654.0)
        ->and(data_get($payload, 'stats.bonus_available'))->toBe(45000);
});
