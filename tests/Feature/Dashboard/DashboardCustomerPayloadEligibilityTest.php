<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Mockery as M;

function buildDashboardServiceForCustomerPayloadEligibilityTest(): DashboardService
{
    return new DashboardService(
        M::mock(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );
}

it('includes status and placement flags in formatted customer payload', function (): void {
    $service = buildDashboardServiceForCustomerPayloadEligibilityTest();
    $method = new ReflectionMethod(DashboardService::class, 'formatCustomer');
    $method->setAccessible(true);

    $activePlacedCustomer = new Customer;
    $activePlacedCustomer->forceFill([
        'id' => 101,
        'name' => 'Active Placed',
        'username' => 'active-placed',
        'email' => 'active-placed@example.test',
        'status' => 3,
        'upline_id' => 77,
        'position' => 'left',
        'created_at' => now(),
    ]);
    $activePlacedCustomer->setRelation('npwp', null);

    /** @var array<string, mixed> $activePayload */
    $activePayload = $method->invoke($service, $activePlacedCustomer);

    $prospectCustomer = new Customer;
    $prospectCustomer->forceFill([
        'id' => 102,
        'name' => 'Prospect Member',
        'username' => 'prospect-member',
        'email' => 'prospect@example.test',
        'status' => 1,
        'upline_id' => null,
        'position' => null,
        'created_at' => now(),
    ]);
    $prospectCustomer->setRelation('npwp', null);

    /** @var array<string, mixed> $prospectPayload */
    $prospectPayload = $method->invoke($service, $prospectCustomer);

    expect($activePayload['status'])->toBe(3)
        ->and($activePayload['has_placement'])->toBeTrue()
        ->and($prospectPayload['status'])->toBe(1)
        ->and($prospectPayload['has_placement'])->toBeFalse();
});
