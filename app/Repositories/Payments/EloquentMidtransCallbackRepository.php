<?php

namespace App\Repositories\Payments;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Repositories\Payments\Contracts\MidtransCallbackRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EloquentMidtransCallbackRepository implements MidtransCallbackRepositoryInterface
{
    public function findPaymentByOrderReference(string $orderReference, bool $lockForUpdate = false): ?Payment
    {
        $query = Payment::query()
            ->with([
                'order:id,order_no,customer_id,status,grand_total,bonus_generated,paid_at',
                'order.items:id,order_id,product_id,qty',
                'order.customer:id,status,omzet',
            ])
            ->where(function (Builder $builder) use ($orderReference): void {
                $builder
                    ->where('provider_txn_id', $orderReference)
                    ->orWhere('transaction_id', $orderReference)
                    ->orWhereHas('order', function (Builder $orderQuery) use ($orderReference): void {
                        $orderQuery->where('order_no', $orderReference);
                    });
            })
            ->orderByDesc('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function findWalletTopupByReference(string $transactionRef, bool $lockForUpdate = false): ?CustomerWalletTransaction
    {
        $query = CustomerWalletTransaction::query()
            ->with(['customer:id,ewallet_saldo,status'])
            ->where('type', 'topup')
            ->where('transaction_ref', $transactionRef)
            ->orderByDesc('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function findCustomerByIdForUpdate(int $customerId): ?Customer
    {
        return Customer::query()
            ->whereKey($customerId)
            ->lockForUpdate()
            ->first();
    }

    public function updatePaymentFromGateway(Payment $payment, string $status, array $gatewayPayload): void
    {
        $transactionId = trim((string) ($gatewayPayload['transaction_id'] ?? ''));
        $signatureKey = trim((string) ($gatewayPayload['signature_key'] ?? ''));
        $metadata = is_array($payment->metadata_json) ? $payment->metadata_json : [];
        $notifications = $metadata['midtrans_notifications'] ?? [];

        if (! is_array($notifications)) {
            $notifications = [];
        }

        $notifications[] = [
            'received_at' => now()->toIso8601String(),
            'transaction_status' => $gatewayPayload['transaction_status'] ?? null,
            'fraud_status' => $gatewayPayload['fraud_status'] ?? null,
            'payload' => $gatewayPayload,
        ];

        $metadata['midtrans_notifications'] = array_slice($notifications, -25);
        $metadata['last_midtrans_payload'] = $gatewayPayload;

        $attributes = [
            'status' => $status,
            'metadata_json' => $metadata,
        ];

        if ($transactionId !== '') {
            $attributes['provider_txn_id'] = $transactionId;
            $attributes['transaction_id'] = $transactionId;
        }

        if ($signatureKey !== '') {
            $attributes['signature_key'] = $signatureKey;
        }

        $payment->update($attributes);
    }

    public function createPaymentTransaction(Payment $payment, string $status, float $amount, array $rawPayload): void
    {
        $payment->transactions()->create([
            'status' => $status,
            'amount' => $amount,
            'raw_json' => $rawPayload,
            'created_at' => now(),
        ]);
    }

    public function updateOrderFromPaymentCallback(Order $order, string $status, bool $markPaidAt = false): void
    {
        $attributes = [
            'status' => $status,
        ];

        if ($markPaidAt && $order->paid_at === null) {
            $attributes['paid_at'] = now();
        }

        $order->update($attributes);
    }

    public function markOrderBonusGenerated(Order $order): bool
    {
        if ((bool) ($order->bonus_generated ?? false)) {
            return false;
        }

        $updatedRows = Order::query()
            ->whereKey((int) $order->id)
            ->where('bonus_generated', false)
            ->update([
                'bonus_generated' => true,
            ]);

        if ($updatedRows > 0) {
            $order->setAttribute('bonus_generated', true);

            return true;
        }

        return false;
    }

    public function decrementProductStock(int $productId, int $quantity): void
    {
        $safeQuantity = max(0, $quantity);

        if ($safeQuantity === 0) {
            return;
        }

        Product::query()
            ->whereKey($productId)
            ->update([
                'stock' => DB::raw('GREATEST(stock - '.$safeQuantity.', 0)'),
            ]);
    }

    public function incrementCustomerOmzet(int $customerId, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        Customer::query()
            ->whereKey($customerId)
            ->increment('omzet', $amount);
    }

    public function updateWalletTransaction(CustomerWalletTransaction $transaction, array $attributes): void
    {
        $transaction->update($attributes);
    }

    public function adjustCustomerWalletBalance(Customer $customer, float $delta): void
    {
        if ($delta === 0.0) {
            return;
        }

        if ($delta > 0) {
            $customer->increment('ewallet_saldo', $delta);

            return;
        }

        $customer->decrement('ewallet_saldo', abs($delta));
    }

    public function clearCustomerCart(int $customerId): void
    {
        $cart = Cart::query()
            ->where('customer_id', $customerId)
            ->first();

        if (! $cart) {
            return;
        }

        $cart->items()->delete();

        $cart->update([
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
        ]);
    }

    public function callBonusEngine(int $orderId): void
    {
        DB::statement('CALL sp_bonus_engine_run(?)', [$orderId]);
    }
}
