<?php

use App\Http\Requests\Checkout\MidtransTokenRequest;
use App\Http\Requests\Checkout\SaldoPayRequest;
use Illuminate\Support\Facades\Validator;

it('accepts self pickup payload without manual address and shipping cost in midtrans request', function (): void {
    $request = new MidtransTokenRequest;

    $validator = Validator::make([
        'address_mode' => 'pickup',
        'order_type' => 'planA',
    ], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

it('requires shipping cost for manual delivery in midtrans request', function (): void {
    $request = new MidtransTokenRequest;

    $validator = Validator::make([
        'address_mode' => 'manual',
        'order_type' => 'planA',
        'recipient_name' => 'Member Pickup',
        'phone' => '081234567890',
        'address_line' => 'Jalan Merdeka No. 1',
        'province' => 'DKI JAKARTA',
        'city' => 'JAKARTA BARAT',
        'postal_code' => '11510',
    ], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('shipping_cost'))->toBeTrue();
});

it('accepts self pickup payload without manual address and shipping cost in saldo request', function (): void {
    $request = new SaldoPayRequest;

    $validator = Validator::make([
        'address_mode' => 'pickup',
        'order_type' => 'planA',
    ], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

it('requires shipping cost for manual delivery in saldo request', function (): void {
    $request = new SaldoPayRequest;

    $validator = Validator::make([
        'address_mode' => 'manual',
        'order_type' => 'planA',
        'recipient_name' => 'Member Pickup',
        'phone' => '081234567890',
        'address_line' => 'Jalan Merdeka No. 1',
        'province' => 'DKI JAKARTA',
        'city' => 'JAKARTA BARAT',
        'postal_code' => '11510',
    ], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('shipping_cost'))->toBeTrue();
});
