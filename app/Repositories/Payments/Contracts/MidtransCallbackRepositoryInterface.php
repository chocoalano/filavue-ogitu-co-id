<?php

namespace App\Repositories\Payments\Contracts;

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Payment;

interface MidtransCallbackRepositoryInterface
{
    public function findPaymentByOrderReference(string $orderReference, bool $lockForUpdate = false): ?Payment;

    public function findWalletTopupByReference(string $transactionRef, bool $lockForUpdate = false): ?CustomerWalletTransaction;

    public function findCustomerByIdForUpdate(int $customerId): ?Customer;

    /**
     * @param  array<string, mixed>  $gatewayPayload
     */
    public function updatePaymentFromGateway(Payment $payment, string $status, array $gatewayPayload): void;

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function createPaymentTransaction(Payment $payment, string $status, float $amount, array $rawPayload): void;

    public function updateOrderFromPaymentCallback(Order $order, string $status, bool $markPaidAt = false): void;

    public function markOrderBonusGenerated(Order $order): bool;

    public function decrementProductStock(int $productId, int $quantity): void;

    public function incrementCustomerOmzet(int $customerId, float $amount): void;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateWalletTransaction(CustomerWalletTransaction $transaction, array $attributes): void;

    public function adjustCustomerWalletBalance(Customer $customer, float $delta): void;

    public function clearCustomerCart(int $customerId): void;

    public function callBonusEngine(int $orderId): void;
}
