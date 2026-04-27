<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Repositories\Payments\Contracts\MidtransCallbackRepositoryInterface;
use App\Services\Payment\MidtransCallbackService;
use Mockery as M;

function makeMidtransCallbackSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

/**
 * @return array{order:Order,payment:Payment,payload:array<string,mixed>}
 */
function makeMidtransCallbackFixture(string $currentPaymentStatus = 'unpaid', int $customerStatus = 3): array
{
    $customer = new Customer;
    $customer->forceFill([
        'id' => 1001,
        'status' => $customerStatus,
        'omzet' => 0,
    ]);
    $customer->exists = true;

    $order = new Order;
    $order->forceFill([
        'id' => 7001,
        'order_no' => 'ORD-TEST-7001',
        'customer_id' => 1001,
        'status' => 'unpaid',
        'grand_total' => 150000,
        'bonus_generated' => false,
    ]);
    $order->exists = true;
    $order->setRelation('customer', $customer);

    $orderItem = new OrderItem;
    $orderItem->forceFill([
        'id' => 8001,
        'order_id' => 7001,
        'product_id' => 9001,
        'qty' => 2,
    ]);
    $orderItem->exists = true;
    $order->setRelation('items', collect([$orderItem]));

    $payment = new Payment;
    $payment->forceFill([
        'id' => 7101,
        'order_id' => 7001,
        'status' => $currentPaymentStatus,
        'amount' => 150000,
        'transaction_id' => null,
    ]);
    $payment->exists = true;
    $payment->setRelation('order', $order);

    $statusCode = '200';
    $grossAmount = '150000.00';
    $serverKey = 'midtrans-test-server-key';

    config()->set('services.midtrans.server_key', $serverKey);

    $payload = [
        'order_id' => 'ORD-TEST-7001',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransCallbackSignature('ORD-TEST-7001', $statusCode, $grossAmount, $serverKey),
        'transaction_status' => 'settlement',
        'transaction_id' => 'txn-7001',
    ];

    return [
        'order' => $order,
        'payment' => $payment,
        'payload' => $payload,
    ];
}

it('runs bonus engine from webhook when payment transitions from unpaid to paid', function (): void {
    $fixture = makeMidtransCallbackFixture('unpaid');
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];
    /** @var array<string,mixed> $payload */
    $payload = $fixture['payload'];

    $repository = M::mock(MidtransCallbackRepositoryInterface::class);
    $repository->shouldReceive('findPaymentByOrderReference')
        ->once()
        ->with('ORD-TEST-7001', true)
        ->andReturn($payment);
    $repository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $repository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $rawPayload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($rawPayload['transaction_status'] ?? null) === 'settlement';
        });
    $repository->shouldReceive('updateOrderFromPaymentCallback')
        ->once()
        ->with($order, 'processing', true);
    $repository->shouldReceive('markOrderBonusGenerated')
        ->once()
        ->with($order)
        ->andReturnTrue();
    $repository->shouldReceive('decrementProductStock')
        ->once()
        ->with(9001, 2);
    $repository->shouldReceive('incrementCustomerOmzet')
        ->once()
        ->with(1001, 150000.0);
    $repository->shouldReceive('callBonusEngine')
        ->once()
        ->with(7001);
    $repository->shouldReceive('clearCustomerCart')
        ->once()
        ->with(1001);

    $service = new MidtransCallbackService($repository);
    $result = $service->handle($payload);

    expect($result['status'])->toBe('success')
        ->and($result['http_code'])->toBe(200)
        ->and($result['message'])->toBe('Order callback processed.');
});

it('does not run bonus engine from webhook when customer status is not 3', function (): void {
    $fixture = makeMidtransCallbackFixture('unpaid', 1);
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];
    /** @var array<string,mixed> $payload */
    $payload = $fixture['payload'];

    $repository = M::mock(MidtransCallbackRepositoryInterface::class);
    $repository->shouldReceive('findPaymentByOrderReference')
        ->once()
        ->with('ORD-TEST-7001', true)
        ->andReturn($payment);
    $repository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $repository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $rawPayload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($rawPayload['transaction_status'] ?? null) === 'settlement';
        });
    $repository->shouldReceive('updateOrderFromPaymentCallback')
        ->once()
        ->with($order, 'processing', true);
    $repository->shouldReceive('markOrderBonusGenerated')
        ->once()
        ->with($order)
        ->andReturnTrue();
    $repository->shouldReceive('decrementProductStock')
        ->once()
        ->with(9001, 2);
    $repository->shouldReceive('incrementCustomerOmzet')
        ->once()
        ->with(1001, 150000.0);
    $repository->shouldReceive('callBonusEngine')->never();
    $repository->shouldReceive('clearCustomerCart')
        ->once()
        ->with(1001);

    $service = new MidtransCallbackService($repository);
    $result = $service->handle($payload);

    expect($result['status'])->toBe('success')
        ->and($result['http_code'])->toBe(200);
});

it('does not run bonus engine from webhook when payment status is already paid', function (): void {
    $fixture = makeMidtransCallbackFixture('paid');
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];
    /** @var array<string,mixed> $payload */
    $payload = $fixture['payload'];

    $repository = M::mock(MidtransCallbackRepositoryInterface::class);
    $repository->shouldReceive('findPaymentByOrderReference')
        ->once()
        ->with('ORD-TEST-7001', true)
        ->andReturn($payment);
    $repository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $repository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $rawPayload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($rawPayload['transaction_status'] ?? null) === 'settlement';
        });
    $repository->shouldReceive('updateOrderFromPaymentCallback')
        ->once()
        ->with($order, 'processing', true);
    $repository->shouldReceive('markOrderBonusGenerated')->never();
    $repository->shouldReceive('decrementProductStock')->never();
    $repository->shouldReceive('incrementCustomerOmzet')->never();
    $repository->shouldReceive('callBonusEngine')->never();
    $repository->shouldReceive('clearCustomerCart')->never();

    $service = new MidtransCallbackService($repository);
    $result = $service->handle($payload);

    expect($result['status'])->toBe('success')
        ->and($result['http_code'])->toBe(200)
        ->and($result['message'])->toBe('Order callback processed.');
});
