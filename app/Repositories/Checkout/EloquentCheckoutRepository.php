<?php

namespace App\Repositories\Checkout;

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentCheckoutRepository implements CheckoutRepositoryInterface
{
    public function getCartWithItems(int $customerId): ?Cart
    {
        return Cart::query()
            ->where('customer_id', $customerId)
            ->with(['items.product.primaryMedia'])
            ->first();
    }

    public function getCustomerAddresses(int $customerId): Collection
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update([
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount'      => 0,
            'grand_total'     => 0,
        ]);
    }

    public function callBonusEngine(int $orderId): void
    {
        DB::statement('CALL sp_bonus_engine_run(?)', [$orderId]);
    }

    public function markOrderBonusGenerated(Order $order): void
    {
        if ((bool) $order->bonus_generated) {
            return;
        }

        $order->update([
            'bonus_generated' => true,
        ]);
    }
}
