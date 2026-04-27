<?php

use App\Models\Customer;
use App\Models\CustomerNpwp;
use App\Models\CustomerPackage;
use App\Repositories\Auth\Contracts\CustomerProfileRepositoryInterface;
use App\Services\Auth\CustomerProfileService;
use Carbon\CarbonInterface;
use Mockery as M;

it('maps referral code and marks account complete when required profile is valid', function (): void {
    $authenticatedCustomer = makeAuthenticatedCustomerForProfile(601);
    $loadedCustomer = makeLoadedCustomerForProfile(601);
    $service = makeCustomerProfileService(601, $loadedCustomer);

    $profile = $service->getApiProfile($authenticatedCustomer);

    expect($profile['member_package'])->toBe('Gold')
        ->and($profile['referral_code'])->toBe('REF601')
        ->and($profile['account_compleated'])->toBeTrue()
        ->and($profile['wallet']['balance'])->toBe(75000.0);
});

it('marks account as incomplete when required profile field is missing', function (): void {
    $authenticatedCustomer = makeAuthenticatedCustomerForProfile(602);
    $loadedCustomer = makeLoadedCustomerForProfile(602, [
        'bank_account' => null,
    ]);
    $service = makeCustomerProfileService(602, $loadedCustomer);

    $profile = $service->getApiProfile($authenticatedCustomer);

    expect($profile['account_compleated'])->toBeFalse();
});

it('marks account as incomplete when npwp profile field is empty', function (): void {
    $authenticatedCustomer = makeAuthenticatedCustomerForProfile(603);
    $loadedCustomer = makeLoadedCustomerForProfile(603, [], [
        'office' => null,
    ]);
    $service = makeCustomerProfileService(603, $loadedCustomer);

    $profile = $service->getApiProfile($authenticatedCustomer);

    expect($profile['account_compleated'])->toBeFalse();
});

/**
 * @return array{
 *   orders_total:int,
 *   orders_processing:int,
 *   orders_completed:int,
 *   network_count:int,
 *   sponsor_count:int,
 *   mitra_prospek:int,
 *   mitra_aktif:int,
 *   mitra_pasif:int,
 *   bonus_total:float,
 *   bonus_sponsor:float,
 *   bonus_matching:float,
 *   bonus_pairing:float,
 *   bonus_cashback:float,
 *   bonus_rewards:float,
 *   bonus_retail:float,
 *   bonus_lifetime_cash:float,
 *   promo_active_count:int,
 *   wallet_reward_points:float,
 *   wallet_has_activity:bool
 * }
 */
function customerProfileMetricsStub(): array
{
    return [
        'orders_total' => 0,
        'orders_processing' => 0,
        'orders_completed' => 0,
        'network_count' => 0,
        'sponsor_count' => 0,
        'mitra_prospek' => 0,
        'mitra_aktif' => 0,
        'mitra_pasif' => 0,
        'bonus_total' => 0.0,
        'bonus_sponsor' => 0.0,
        'bonus_matching' => 0.0,
        'bonus_pairing' => 0.0,
        'bonus_cashback' => 0.0,
        'bonus_rewards' => 0.0,
        'bonus_retail' => 0.0,
        'bonus_lifetime_cash' => 0.0,
        'promo_active_count' => 0,
        'wallet_reward_points' => 0.0,
        'wallet_has_activity' => false,
    ];
}

function makeAuthenticatedCustomerForProfile(int $customerId): Customer
{
    $customer = new Customer;
    $customer->setAttribute('id', $customerId);
    $customer->exists = true;

    return $customer;
}

/**
 * @param  array<string, mixed>  $overrides
 * @param  array<string, mixed>  $npwpOverrides
 */
function makeLoadedCustomerForProfile(int $customerId, array $overrides = [], array $npwpOverrides = []): Customer
{
    $customer = new Customer;
    $customer->forceFill(array_merge([
        'name' => 'Customer '.$customerId,
        'username' => 'member'.$customerId,
        'email' => 'member'.$customerId.'@example.test',
        'phone' => '08123456789',
        'status' => 3,
        'ref_code' => 'REF'.$customerId,
        'ewallet_saldo' => 75000,
        'nik' => '3276010101010001',
        'gender' => 'L',
        'bank_name' => 'BCA',
        'bank_account' => '1234567890',
    ], $overrides));
    $customer->setAttribute('id', $customerId);
    $customer->exists = true;

    $package = new CustomerPackage;
    $package->setAttribute('name', 'Gold');
    $package->exists = true;

    $customer->setRelation('package', $package);

    $npwp = new CustomerNpwp;
    $npwp->forceFill(array_merge([
        'member_id' => $customerId,
        'nama' => 'Customer '.$customerId.' NPWP',
        'npwp' => '12.345.678.9-012.000',
        'jk' => 1,
        'npwp_date' => '2024-01-31',
        'alamat' => 'Jl. Merdeka No. '.$customerId,
        'menikah' => 'Y',
        'anak' => '2',
        'kerja' => 'Y',
        'office' => 'PT Contoh',
    ], $npwpOverrides));
    $npwp->exists = true;

    $customer->setRelation('npwp', $npwp);

    return $customer;
}

function makeCustomerProfileService(int $customerId, Customer $loadedCustomer): CustomerProfileService
{
    $repository = M::mock(CustomerProfileRepositoryInterface::class);
    $repository->shouldReceive('findByIdWithPackage')
        ->once()
        ->with($customerId)
        ->andReturn($loadedCustomer);
    $repository->shouldReceive('getProfileMetrics')
        ->once()
        ->with($customerId, M::type(CarbonInterface::class))
        ->andReturn(customerProfileMetricsStub());

    return new CustomerProfileService($repository);
}
