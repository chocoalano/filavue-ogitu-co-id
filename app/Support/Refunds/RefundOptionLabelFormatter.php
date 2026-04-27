<?php

namespace App\Support\Refunds;

use App\Models\Order;
use App\Models\Payment;

class RefundOptionLabelFormatter
{
    public static function formatOrder(Order $order): string
    {
        $orderNumber = trim((string) ($order->order_no ?? ''));

        if ($orderNumber !== '') {
            return $orderNumber;
        }

        $identifier = $order->getKey();

        if (filled($identifier)) {
            return 'Pesanan #'.$identifier;
        }

        return 'Pesanan tanpa nomor';
    }

    public static function formatPayment(Payment $payment): string
    {
        $identifier = $payment->getKey();
        $paymentPrefix = filled($identifier) ? '#'.$identifier : 'Pembayaran';
        $paymentMethod = trim((string) ($payment->method?->name ?? ''));

        if ($paymentMethod === '') {
            $paymentMethod = 'Tanpa metode';
        }

        $transactionId = self::firstFilledString([
            $payment->transaction_id,
            $payment->provider_txn_id,
        ], '-');

        return "{$paymentPrefix} - {$paymentMethod} - TX: {$transactionId}";
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function firstFilledString(array $values, string $fallback): string
    {
        foreach ($values as $value) {
            $stringValue = trim((string) $value);

            if ($stringValue !== '') {
                return $stringValue;
            }
        }

        return $fallback;
    }
}
