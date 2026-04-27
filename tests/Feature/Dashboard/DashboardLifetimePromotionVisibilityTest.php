<?php

use App\Models\Customer;
use App\Models\Reward;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Mockery as M;

function buildDashboardServiceForLifetimePromotionVisibilityTest(): DashboardService
{
    return new DashboardService(
        M::mock(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );
}

it('uses group omzet fields for lifetime reward progress summary', function (): void {
    $service = buildDashboardServiceForLifetimePromotionVisibilityTest();
    $method = new ReflectionMethod(DashboardService::class, 'formatLifetimeRewardsData');
    $method->setAccessible(true);

    $customer = new Customer;
    $customer->forceFill([
        'id' => 88,
        'omzet_group_left' => 1250000,
        'omzet_group_right' => 980000,
        'omzet_group_left_planb' => 0,
        'omzet_group_right_planb' => 0,
    ]);

    $reward = new Reward;
    $reward->forceFill([
        'id' => 5,
        'name' => 'Lifetime Silver',
        'reward' => 'Cash Reward',
        'bv' => 500000,
        'value' => 2500000,
    ]);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke(
        $service,
        $customer,
        collect([$reward]),
        collect(),
        collect(),
    );

    expect(data_get($payload, 'summary.accumulated_left'))->toBe(1250000.0)
        ->and(data_get($payload, 'summary.accumulated_right'))->toBe(980000.0)
        ->and(data_get($payload, 'summary.eligible_count'))->toBe(1)
        ->and(data_get($payload, 'rewards.0.accumulated_left'))->toBe(1250000.0)
        ->and(data_get($payload, 'rewards.0.accumulated_right'))->toBe(980000.0)
        ->and(data_get($payload, 'rewards.0.can_claim'))->toBeTrue()
        ->and(data_get($payload, 'rewards.0.progress_percent'))->toBe(100.0);
});

it('renders promotion reward metric labels in the dashboard bonus summary source', function (): void {
    $source = file_get_contents(resource_path('js/composables/useDashboardBonus.ts'));

    expect($source)->toBeString()
        ->and($source)->toContain('Poin')
        ->and($source)->toContain('BV/Omzet');
});
