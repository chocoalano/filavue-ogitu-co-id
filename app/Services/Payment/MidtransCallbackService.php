<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Repositories\Payments\Contracts\MidtransCallbackRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackService
{
    public function __construct(
        protected MidtransCallbackRepositoryInterface $callbackRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{status:string,message:string,http_code:int}
     */
    public function handle(array $payload): array
    {
        $orderId = trim((string) ($payload['order_id'] ?? ''));
        $statusCode = trim((string) ($payload['status_code'] ?? ''));
        $grossAmount = trim((string) ($payload['gross_amount'] ?? ''));
        $signatureKey = trim((string) ($payload['signature_key'] ?? ''));
        $transactionStatus = strtolower(trim((string) ($payload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($payload['fraud_status'] ?? '')));

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            Log::warning('Midtrans callback missing required fields.', [
                'order_id' => $orderId,
                'status_code' => $statusCode,
            ]);

            return [
                'status' => 'error',
                'message' => 'Invalid payload.',
                'http_code' => 400,
            ];
        }

        if (! $this->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans callback invalid signature.', [
                'order_id' => $orderId,
            ]);

            return [
                'status' => 'error',
                'message' => 'Invalid signature.',
                'http_code' => 403,
            ];
        }

        try {
            foreach ($this->buildReferenceCandidates($orderId) as $reference) {
                $orderResult = $this->processOrderPayment($reference, $payload, $transactionStatus, $fraudStatus);

                if ($orderResult !== null) {
                    $this->runOrderPaidSideEffects($orderResult);

                    Log::info('Midtrans callback order processed.', [
                        'order_id' => $orderResult['order_id'],
                        'order_no' => $orderResult['order_no'],
                        'payment_status' => $orderResult['payment_status'],
                    ]);

                    return [
                        'status' => 'success',
                        'message' => 'Order callback processed.',
                        'http_code' => 200,
                    ];
                }
            }

            foreach ($this->buildReferenceCandidates($orderId) as $reference) {
                $walletResult = $this->processWalletTopup($reference, $payload, $transactionStatus, $fraudStatus);

                if ($walletResult !== null) {
                    Log::info('Midtrans callback wallet topup processed.', [
                        'wallet_transaction_id' => $walletResult['wallet_transaction_id'],
                        'transaction_ref' => $walletResult['transaction_ref'],
                        'status' => $walletResult['status'],
                    ]);

                    return [
                        'status' => 'success',
                        'message' => 'Wallet callback processed.',
                        'http_code' => 200,
                    ];
                }
            }

            Log::warning('Midtrans callback reference not found.', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            return [
                'status' => 'success',
                'message' => 'Reference not found, ignored.',
                'http_code' => 200,
            ];
        } catch (\Throwable $exception) {
            Log::error('Midtrans callback processing failed.', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Server error.',
                'http_code' => 500,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   order_id:int,
     *   order_no:string,
     *   customer_id:int,
     *   customer_status:int,
     *   run_side_effects:bool,
     *   payment_status:string
     * }|null
     */
    private function processOrderPayment(
        string $reference,
        array $payload,
        string $transactionStatus,
        string $fraudStatus,
    ): ?array {
        return DB::transaction(function () use ($reference, $payload, $transactionStatus, $fraudStatus): ?array {
            $payment = $this->callbackRepository->findPaymentByOrderReference($reference, true);

            if (! $payment || ! $payment->order) {
                return null;
            }

            $order = $payment->order;
            $currentPaymentStatus = $this->normalizePaymentStatus((string) ($payment->status ?? 'pending'));
            $incomingPaymentStatus = $this->mapMidtransPaymentStatus($transactionStatus, $fraudStatus);
            $resolvedPaymentStatus = $this->resolvePaymentStatusTransition($currentPaymentStatus, $incomingPaymentStatus);
            $gatewayTransactionId = trim((string) ($payload['transaction_id'] ?? ''));
            $shouldCreatePaymentLog = $currentPaymentStatus !== $resolvedPaymentStatus
                || ($gatewayTransactionId !== '' && $gatewayTransactionId !== (string) ($payment->transaction_id ?? ''));

            $this->callbackRepository->updatePaymentFromGateway($payment, $resolvedPaymentStatus, $payload);

            if ($shouldCreatePaymentLog) {
                $this->callbackRepository->createPaymentTransaction(
                    $payment,
                    $resolvedPaymentStatus,
                    (float) ($payment->amount ?? $order->grand_total ?? 0),
                    $payload
                );
            }

            $runSideEffects = false;

            if ($resolvedPaymentStatus === 'paid') {
                $this->callbackRepository->updateOrderFromPaymentCallback($order, 'processing', true);

                if (
                    $currentPaymentStatus !== 'paid'
                    && $this->callbackRepository->markOrderBonusGenerated($order)
                ) {
                    foreach ($order->items as $item) {
                        $productId = (int) ($item->product_id ?? 0);
                        $quantity = max(0, (int) ($item->qty ?? 0));

                        if ($productId > 0 && $quantity > 0) {
                            $this->callbackRepository->decrementProductStock($productId, $quantity);
                        }
                    }

                    $this->callbackRepository->incrementCustomerOmzet(
                        (int) $order->customer_id,
                        (float) ($order->grand_total ?? 0)
                    );

                    $customer = $order->customer;
                    $isFirstPurchase = $customer
                        && (int) ($customer->status ?? 0) === 1
                        && ! Order::query()
                            ->where('customer_id', $customer->id)
                            ->where('id', '!=', $order->id)
                            ->whereIn('status', ['processing', 'completed'])
                            ->exists();

                    if ($isFirstPurchase) {
                        $customer->update(['status' => 2]);
                    }

                    $runSideEffects = true;
                }
            } elseif ($resolvedPaymentStatus === 'refunded') {
                $this->callbackRepository->updateOrderFromPaymentCallback($order, 'refunded');
            } elseif ($resolvedPaymentStatus === 'failed') {
                if ($this->isOrderAwaitingPayment((string) ($order->status ?? 'pending'))) {
                    $this->callbackRepository->updateOrderFromPaymentCallback($order, 'cancelled');
                }
            } elseif ($this->isOrderAwaitingPayment((string) ($order->status ?? 'pending'))) {
                $this->callbackRepository->updateOrderFromPaymentCallback($order, 'pending');
            }

            return [
                'order_id' => (int) $order->id,
                'order_no' => (string) $order->order_no,
                'customer_id' => (int) $order->customer_id,
                'customer_status' => (int) ($order->customer?->status ?? 0),
                'run_side_effects' => $runSideEffects,
                'payment_status' => $resolvedPaymentStatus,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   wallet_transaction_id:int,
     *   transaction_ref:string,
     *   status:string
     * }|null
     */
    private function processWalletTopup(
        string $reference,
        array $payload,
        string $transactionStatus,
        string $fraudStatus,
    ): ?array {
        return DB::transaction(function () use ($reference, $payload, $transactionStatus, $fraudStatus): ?array {
            $transaction = $this->callbackRepository->findWalletTopupByReference($reference, true);

            if (! $transaction) {
                return null;
            }

            $customer = $this->callbackRepository->findCustomerByIdForUpdate((int) $transaction->customer_id);

            if (! $customer) {
                $this->callbackRepository->updateWalletTransaction($transaction, [
                    'status' => 'failed',
                ]);

                return [
                    'wallet_transaction_id' => (int) $transaction->id,
                    'transaction_ref' => (string) ($transaction->transaction_ref ?? ''),
                    'status' => 'failed',
                ];
            }

            $currentStatus = $this->normalizeWalletStatus((string) ($transaction->status ?? 'pending'));
            $incomingStatus = $this->mapMidtransWalletStatus($transactionStatus, $fraudStatus);
            $resolvedStatus = $this->resolveWalletStatusTransition($currentStatus, $incomingStatus);
            $gatewayTransactionId = trim((string) ($payload['transaction_id'] ?? ''));
            $signatureKey = trim((string) ($payload['signature_key'] ?? ''));

            if ($resolvedStatus === 'completed') {
                if ($currentStatus !== 'completed') {
                    $amount = (float) ($transaction->amount ?? 0);
                    $balanceBefore = (float) ($customer->ewallet_saldo ?? 0);
                    $balanceAfter = $balanceBefore + $amount;

                    $this->callbackRepository->adjustCustomerWalletBalance($customer, $amount);

                    $this->callbackRepository->updateWalletTransaction($transaction, [
                        'status' => 'completed',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'midtrans_transaction_id' => $gatewayTransactionId !== '' ? $gatewayTransactionId : $transaction->midtrans_transaction_id,
                        'midtrans_signature_key' => $signatureKey !== '' ? $signatureKey : $transaction->midtrans_signature_key,
                        'completed_at' => now(),
                    ]);
                } else {
                    $this->callbackRepository->updateWalletTransaction($transaction, [
                        'status' => 'completed',
                        'midtrans_transaction_id' => $gatewayTransactionId !== '' ? $gatewayTransactionId : $transaction->midtrans_transaction_id,
                        'midtrans_signature_key' => $signatureKey !== '' ? $signatureKey : $transaction->midtrans_signature_key,
                    ]);
                }
            } else {
                $finalStatus = $resolvedStatus === 'cancelled'
                    ? 'cancelled'
                    : ($resolvedStatus === 'failed' ? 'failed' : 'pending');

                $this->callbackRepository->updateWalletTransaction($transaction, [
                    'status' => $finalStatus,
                    'midtrans_transaction_id' => $gatewayTransactionId !== '' ? $gatewayTransactionId : $transaction->midtrans_transaction_id,
                    'midtrans_signature_key' => $signatureKey !== '' ? $signatureKey : $transaction->midtrans_signature_key,
                    'completed_at' => in_array($finalStatus, ['failed', 'cancelled'], true)
                        ? now()
                        : $transaction->completed_at,
                ]);
            }

            return [
                'wallet_transaction_id' => (int) $transaction->id,
                'transaction_ref' => (string) ($transaction->transaction_ref ?? ''),
                'status' => $resolvedStatus,
            ];
        });
    }

    /**
     * @param array{
     *   order_id:int,
     *   customer_id:int,
     *   customer_status:int,
     *   run_side_effects:bool,
     *   payment_status:string
     * } $result
     */
    private function runOrderPaidSideEffects(array $result): void
    {
        if (! ($result['run_side_effects'] ?? false)) {
            return;
        }

        $customerStatus = (int) ($result['customer_status'] ?? 0);
        $paymentStatus = strtoupper((string) ($result['payment_status'] ?? ''));

        if ($customerStatus === 3 && $paymentStatus === 'PAID') {
            try {
                $this->callbackRepository->callBonusEngine((int) $result['order_id']);
            } catch (QueryException $exception) {
                // SQLSTATE 45000 = SP SIGNAL: order already processed (idempotency guard).
                // Log sebagai warning karena ini kondisi yang expected pada duplicate webhook.
                if ($exception->getCode() === '45000') {
                    Log::warning('Bonus engine skipped: order already processed.', [
                        'order_id' => $result['order_id'],
                        'error' => $exception->getMessage(),
                    ]);
                } else {
                    Log::error('Failed to run bonus engine after Midtrans callback.', [
                        'order_id' => $result['order_id'],
                        'error' => $exception->getMessage(),
                    ]);
                }
            } catch (\Throwable $exception) {
                Log::error('Failed to run bonus engine after Midtrans callback.', [
                    'order_id' => $result['order_id'],
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->callbackRepository->clearCustomerCart((int) $result['customer_id']);
    }

    /**
     * @return list<string>
     */
    private function buildReferenceCandidates(string $orderId): array
    {
        $candidates = [$orderId];
        $baseOrderNo = $this->extractBaseOrderNo($orderId);

        if ($baseOrderNo !== null) {
            $candidates[] = $baseOrderNo;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function extractBaseOrderNo(string $orderId): ?string
    {
        if (preg_match('/^(ORD-\d{8}-[A-Z0-9]{6})-\d+$/', $orderId, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function isValidSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey,
    ): bool {
        $serverKey = (string) config('services.midtrans.server_key', '');

        if ($serverKey === '') {
            return false;
        }

        $localSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($localSignature, $signatureKey);
    }

    private function mapMidtransPaymentStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        return match (strtolower(trim($transactionStatus))) {
            'settlement' => 'paid',
            'capture' => strtolower(trim($fraudStatus)) === 'challenge' ? 'pending' : 'paid',
            'refund', 'partial_refund' => 'refunded',
            'deny', 'expire', 'expired', 'cancel', 'cancelled', 'canceled', 'failure' => 'failed',
            default => 'pending',
        };
    }

    private function mapMidtransWalletStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        return match (strtolower(trim($transactionStatus))) {
            'settlement' => 'completed',
            'capture' => strtolower(trim($fraudStatus)) === 'challenge' ? 'pending' : 'completed',
            'deny', 'failure' => 'failed',
            'expire', 'expired', 'cancel', 'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    private function resolvePaymentStatusTransition(string $currentStatus, string $incomingStatus): string
    {
        if ($currentStatus === 'refunded') {
            return 'refunded';
        }

        if ($currentStatus === 'paid' && in_array($incomingStatus, ['pending', 'failed'], true)) {
            return 'paid';
        }

        if ($currentStatus === 'failed' && $incomingStatus === 'pending') {
            return 'failed';
        }

        return $incomingStatus;
    }

    private function resolveWalletStatusTransition(string $currentStatus, string $incomingStatus): string
    {
        if ($currentStatus === 'completed') {
            return 'completed';
        }

        if (in_array($currentStatus, ['failed', 'cancelled'], true) && $incomingStatus === 'pending') {
            return $currentStatus;
        }

        return $incomingStatus;
    }

    private function normalizePaymentStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'paid', 'settlement', 'capture' => 'paid',
            'refunded', 'refund' => 'refunded',
            'failed', 'deny', 'expire', 'cancel', 'cancelled', 'canceled' => 'failed',
            default => 'pending',
        };
    }

    private function normalizeWalletStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed', 'settlement', 'capture' => 'completed',
            'failed', 'deny', 'failure' => 'failed',
            'cancelled', 'canceled', 'cancel', 'expire', 'expired' => 'cancelled',
            default => 'pending',
        };
    }

    private function isOrderAwaitingPayment(string $status): bool
    {
        return in_array(
            strtolower(trim($status)),
            ['pending', 'unpaid', 'waiting_payment', 'awaiting_payment'],
            true
        );
    }
}
