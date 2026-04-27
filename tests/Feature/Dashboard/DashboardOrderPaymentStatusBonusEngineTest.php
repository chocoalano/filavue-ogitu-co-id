<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Mockery as M;

function makeDashboardOrderPaymentFixture(
    string $paymentStatus = 'unpaid',
    bool $bonusGenerated = false,
    int $customerStatus = 3,
): array {
    $customer = new Customer;
    $customer->forceFill([
        'id' => 1001,
        'name' => 'Customer Test',
        'email' => 'customer@example.test',
        'status' => $customerStatus,
    ]);
    $customer->exists = true;

    $paymentMethod = new PaymentMethod;
    $paymentMethod->forceFill([
        'id' => 3001,
        'name' => 'Midtrans',
        'code' => 'midtrans',
    ]);
    $paymentMethod->exists = true;

    $payment = new Payment;
    $payment->forceFill([
        'id' => 2001,
        'order_id' => 5001,
        'status' => $paymentStatus,
        'amount' => 150000,
        'created_at' => now(),
    ]);
    $payment->exists = true;
    $payment->setRelation('method', $paymentMethod);

    $order = new Order;
    $order->forceFill([
        'id' => 5001,
        'order_no' => 'ORD-TEST-5001',
        'customer_id' => 1001,
        'status' => 'unpaid',
        'currency' => 'IDR',
        'subtotal_amount' => 150000,
        'discount_amount' => 0,
        'shipping_amount' => 0,
        'tax_amount' => 0,
        'grand_total' => 150000,
        'bonus_generated' => $bonusGenerated,
        'created_at' => now(),
    ]);
    $order->exists = true;
    $order->setRelation('customer', $customer);
    $order->setRelation('payments', collect([$payment]));
    $order->setRelation('items', collect());
    $order->setRelation('shipments', collect());

    return [
        'customer' => $customer,
        'order' => $order,
        'payment' => $payment,
    ];
}

it('runs bonus engine on payment status check when order transitions from unpaid to paid', function (): void {
    $fixture = makeDashboardOrderPaymentFixture('unpaid', false);
    /** @var Customer $customer */
    $customer = $fixture['customer'];
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('findOrderForCustomer')
        ->twice()
        ->with(1001, 5001)
        ->andReturn($order, $order);
    $dashboardRepository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $dashboardRepository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $payload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($payload['transaction_status'] ?? null) === 'settlement';
        });
    $dashboardRepository->shouldReceive('markOrderAsPaid')
        ->once()
        ->with($order);
    $dashboardRepository->shouldReceive('markOrderBonusGenerated')
        ->once()
        ->with($order)
        ->andReturnTrue();
    $dashboardRepository->shouldReceive('callBonusEngine')
        ->once()
        ->with(5001);

    $midtransService = M::mock(MidtransService::class);
    $midtransService->shouldReceive('getTransactionStatus')
        ->once()
        ->with('ORD-TEST-5001')
        ->andReturn([
            'transaction_status' => 'settlement',
            'transaction_id' => 'txn-5001',
        ]);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        $midtransService,
    );

    $result = $service->checkOrderPaymentStatus($customer, 5001);

    expect($result['message'])->toBe('Status pembayaran berhasil diperbarui dari Midtrans.');
});

it('does not run bonus engine on payment status check when previous payment status is already paid', function (): void {
    $fixture = makeDashboardOrderPaymentFixture('paid', false);
    /** @var Customer $customer */
    $customer = $fixture['customer'];
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('findOrderForCustomer')
        ->twice()
        ->with(1001, 5001)
        ->andReturn($order, $order);
    $dashboardRepository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $dashboardRepository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $payload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($payload['transaction_status'] ?? null) === 'settlement';
        });
    $dashboardRepository->shouldReceive('markOrderAsPaid')
        ->once()
        ->with($order);
    $dashboardRepository->shouldReceive('markOrderBonusGenerated')->never();
    $dashboardRepository->shouldReceive('callBonusEngine')->never();

    $midtransService = M::mock(MidtransService::class);
    $midtransService->shouldReceive('getTransactionStatus')
        ->once()
        ->with('ORD-TEST-5001')
        ->andReturn([
            'transaction_status' => 'settlement',
            'transaction_id' => 'txn-5001',
        ]);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        $midtransService,
    );

    $result = $service->checkOrderPaymentStatus($customer, 5001);

    expect($result['message'])->toBe('Status pembayaran berhasil diperbarui dari Midtrans.');
});

it('does not run bonus engine on payment status check when customer status is not 3', function (): void {
    $fixture = makeDashboardOrderPaymentFixture('unpaid', false, 1);
    /** @var Customer $customer */
    $customer = $fixture['customer'];
    /** @var Order $order */
    $order = $fixture['order'];
    /** @var Payment $payment */
    $payment = $fixture['payment'];

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('findOrderForCustomer')
        ->twice()
        ->with(1001, 5001)
        ->andReturn($order, $order);
    $dashboardRepository->shouldReceive('updatePaymentFromGateway')
        ->once()
        ->with($payment, 'paid', M::type('array'));
    $dashboardRepository->shouldReceive('createPaymentTransaction')
        ->once()
        ->withArgs(function (Payment $targetPayment, string $status, float $amount, array $payload) use ($payment): bool {
            return $targetPayment === $payment
                && $status === 'paid'
                && $amount === 150000.0
                && ($payload['transaction_status'] ?? null) === 'settlement';
        });
    $dashboardRepository->shouldReceive('markOrderAsPaid')
        ->once()
        ->with($order);
    $dashboardRepository->shouldReceive('markOrderBonusGenerated')->never();
    $dashboardRepository->shouldReceive('callBonusEngine')->never();

    $midtransService = M::mock(MidtransService::class);
    $midtransService->shouldReceive('getTransactionStatus')
        ->once()
        ->with('ORD-TEST-5001')
        ->andReturn([
            'transaction_status' => 'settlement',
            'transaction_id' => 'txn-5001',
        ]);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        $midtransService,
    );

    $result = $service->checkOrderPaymentStatus($customer, 5001);

    expect($result['message'])->toBe('Status pembayaran berhasil diperbarui dari Midtrans.');
});
