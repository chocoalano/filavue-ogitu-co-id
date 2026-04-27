<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use App\Services\Shipping\LionParcelService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery as M;

function makeCheckoutServiceForBonusTest(): CheckoutService
{
    return new CheckoutService(
        M::mock(CheckoutRepositoryInterface::class),
        M::mock(LionParcelService::class),
    );
}

function makeCartForBonusTest(): Cart
{
    $firstItem = new CartItem([
        'qty' => 2,
    ]);
    $firstItem->setRelation('product', new Product([
        'bv' => 100,
        'b_sponsor' => 10,
        'b_matching' => 2,
        'b_pairing' => 3,
        'b_cashback' => 1,
    ]));

    $secondItem = new CartItem([
        'qty' => 1,
    ]);
    $secondItem->setRelation('product', new Product([
        'bv' => 50,
        'b_sponsor' => 5,
        'b_matching' => 1,
        'b_pairing' => 1.5,
        'b_cashback' => 0.5,
    ]));

    $cart = new Cart([
        'currency' => 'IDR',
        'subtotal_amount' => 350000,
        'discount_amount' => 25000,
        'tax_amount' => 3500,
    ]);
    $cart->setRelation('items', new EloquentCollection([$firstItem, $secondItem]));

    return $cart;
}

it('calculates bonus totals from cart items', function (): void {
    $service = makeCheckoutServiceForBonusTest();
    $cart = makeCartForBonusTest();

    $method = new ReflectionMethod(CheckoutService::class, 'calculateCartBonusAmounts');
    $method->setAccessible(true);

    /** @var array{bv: float, sponsor: float, match: float, pairing: float, cashback: float} $totals */
    $totals = $method->invoke($service, $cart);

    expect($totals)->toMatchArray([
        'bv' => 250.0,
        'sponsor' => 25.0,
        'match' => 5.0,
        'pairing' => 7.5,
        'cashback' => 2.5,
    ]);
});

it('includes calculated bonus amounts in checkout order payload', function (): void {
    $service = makeCheckoutServiceForBonusTest();
    $cart = makeCartForBonusTest();
    $customer = new Customer;
    $customer->id = 77;

    $method = new ReflectionMethod(CheckoutService::class, 'buildOrderPayload');
    $method->setAccessible(true);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke($service, $customer, $cart, 12, 'pending', 10000.0, 'invalid-plan');

    expect($payload['bv_amount'])->toBe(250.0)
        ->and($payload['sponsor_amount'])->toBe(25.0)
        ->and($payload['match_amount'])->toBe(5.0)
        ->and($payload['pairing_amount'])->toBe(7.5)
        ->and($payload['cashback_amount'])->toBe(2.5)
        ->and($payload['grand_total'])->toBe(338500.0)
        ->and($payload['type'])->toBe('planA');
});
