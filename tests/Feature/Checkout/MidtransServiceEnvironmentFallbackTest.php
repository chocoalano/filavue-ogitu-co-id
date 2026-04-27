<?php

use App\Models\Customer;
use App\Services\Payment\MidtransService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('uses production snap endpoint when midtrans env is empty but production flag is enabled', function (): void {
    config()->set('services.midtrans.server_key', ' mid-server-test ');
    config()->set('services.midtrans.env', '');
    config()->set('services.midtrans.is_production', true);

    Http::fake([
        'https://app.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-production',
        ], 200),
        '*' => Http::response([
            'error_messages' => ['Unexpected endpoint'],
        ], 500),
    ]);

    $customer = new Customer;
    $customer->forceFill([
        'name' => 'Test Customer',
        'email' => 'customer@example.test',
        'phone' => '081234567890',
    ]);

    $token = app(MidtransService::class)
        ->createSnapTokenForWalletTopup('TOPUP-TEST-1', 100000, $customer);

    expect($token)->toBe('snap-token-production');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://app.midtrans.com/snap/v1/transactions';
    });
});

it('uses production status endpoint when midtrans env is empty but production flag is enabled', function (): void {
    config()->set('services.midtrans.server_key', 'mid-server-test');
    config()->set('services.midtrans.env', '');
    config()->set('services.midtrans.is_production', true);

    Http::fake([
        'https://api.midtrans.com/v2/*/status' => Http::response([
            'transaction_status' => 'pending',
            'status_message' => 'Success, transaction found',
        ], 200),
        '*' => Http::response([
            'status_message' => 'Unexpected endpoint',
        ], 500),
    ]);

    $payload = app(MidtransService::class)->getTransactionStatus('ORD-TEST-1');

    expect($payload)->toBeArray()
        ->and($payload['transaction_status'] ?? null)->toBe('pending');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.midtrans.com/v2/ORD-TEST-1/status';
    });
});
