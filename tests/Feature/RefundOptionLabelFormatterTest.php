<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Support\Refunds\RefundOptionLabelFormatter;

it('formats order labels with safe fallbacks for legacy records', function (): void {
    $orderWithNumber = (new Order)->forceFill([
        'id' => 101,
        'order_no' => 'ORD-2026-0001',
    ]);

    $orderWithoutNumber = (new Order)->forceFill([
        'id' => 102,
        'order_no' => null,
    ]);

    $unsavedOrderWithoutNumber = (new Order)->forceFill([
        'order_no' => '   ',
    ]);

    expect(RefundOptionLabelFormatter::formatOrder($orderWithNumber))
        ->toBe('ORD-2026-0001')
        ->and(RefundOptionLabelFormatter::formatOrder($orderWithoutNumber))
        ->toBe('Pesanan #102')
        ->and(RefundOptionLabelFormatter::formatOrder($unsavedOrderWithoutNumber))
        ->toBe('Pesanan tanpa nomor');
});

it('formats payment labels with method and transaction fallbacks', function (): void {
    $paymentMethod = (new PaymentMethod)->forceFill([
        'name' => 'Transfer Bank',
    ]);

    $paymentWithTransactionId = (new Payment)->forceFill([
        'id' => 201,
        'transaction_id' => 'TX-2026-0001',
        'provider_txn_id' => null,
    ]);
    $paymentWithTransactionId->setRelation('method', $paymentMethod);

    $paymentWithProviderTransaction = (new Payment)->forceFill([
        'id' => 202,
        'transaction_id' => null,
        'provider_txn_id' => 'MID-2026-0009',
    ]);

    $paymentWithoutReference = new Payment;

    expect(RefundOptionLabelFormatter::formatPayment($paymentWithTransactionId))
        ->toBe('#201 - Transfer Bank - TX: TX-2026-0001')
        ->and(RefundOptionLabelFormatter::formatPayment($paymentWithProviderTransaction))
        ->toBe('#202 - Tanpa metode - TX: MID-2026-0009')
        ->and(RefundOptionLabelFormatter::formatPayment($paymentWithoutReference))
        ->toBe('Pembayaran - Tanpa metode - TX: -');
});
