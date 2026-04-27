<?php

use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use App\Services\Shipping\LionParcelService;
use Mockery as M;

it('forces shipping amount to zero for self pickup mode', function (): void {
    $service = new CheckoutService(
        M::mock(CheckoutRepositoryInterface::class),
        M::mock(LionParcelService::class),
    );

    $method = new ReflectionMethod(CheckoutService::class, 'resolveShippingAmount');
    $method->setAccessible(true);

    $shippingAmount = $method->invoke($service, [
        'address_mode' => 'pickup',
        'shipping_cost' => 45000,
    ]);

    expect($shippingAmount)->toBe(0.0);
});

it('uses payload shipping amount for non pickup mode', function (): void {
    $service = new CheckoutService(
        M::mock(CheckoutRepositoryInterface::class),
        M::mock(LionParcelService::class),
    );

    $method = new ReflectionMethod(CheckoutService::class, 'resolveShippingAmount');
    $method->setAccessible(true);

    $shippingAmount = $method->invoke($service, [
        'address_mode' => 'manual',
        'shipping_cost' => 45000,
    ]);

    expect($shippingAmount)->toBe(45000.0);
});
