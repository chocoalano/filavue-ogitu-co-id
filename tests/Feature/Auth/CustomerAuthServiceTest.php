<?php

use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;
use App\Services\Auth\CustomerAuthService;
use Mockery as M;

it('returns null when api login customer status is not member active', function (): void {
    $customer = new Customer;
    $customer->forceFill([
        'username' => 'member1001',
        'password' => 'secret123',
        'status' => 2,
    ]);
    $customer->setAttribute('id', 1001);
    $customer->exists = true;

    $repository = M::mock(CustomerAuthRepositoryInterface::class);
    $repository->shouldReceive('findByUsername')
        ->once()
        ->with('member1001')
        ->andReturn($customer);
    $repository->shouldNotReceive('createApiToken');

    $service = new CustomerAuthService($repository);

    $result = $service->attemptApiLogin('member1001', 'secret123', 'android-app');

    expect($result)->toBeNull();
});

it('returns access token payload when api login customer status is member active', function (): void {
    $customer = new Customer;
    $customer->forceFill([
        'username' => 'member1002',
        'password' => 'secret123',
        'status' => 3,
    ]);
    $customer->setAttribute('id', 1002);
    $customer->exists = true;

    $repository = M::mock(CustomerAuthRepositoryInterface::class);
    $repository->shouldReceive('findByUsername')
        ->once()
        ->with('member1002')
        ->andReturn($customer);
    $repository->shouldReceive('createApiToken')
        ->once()
        ->with($customer, 'android-app', ['customer:api'])
        ->andReturn('1|service-test-token');

    $service = new CustomerAuthService($repository);

    $result = $service->attemptApiLogin('member1002', 'secret123', 'android-app');

    expect($result)->toBeArray()
        ->and($result['token_type'])->toBe('Bearer')
        ->and($result['access_token'])->toBe('1|service-test-token')
        ->and($result['customer'])->toBe($customer);
});
