<?php

namespace App\Repositories\Checkout\Contracts;

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Support\Collection;

interface CheckoutRepositoryInterface
{
    /**
     * Ambil keranjang beserta items, produk, dan media utama produk.
     */
    public function getCartWithItems(int $customerId): ?Cart;

    /**
     * Ambil semua alamat customer, default dahulu.
     *
     * @return Collection<int, CustomerAddress>
     */
    public function getCustomerAddresses(int $customerId): Collection;

    /**
     * Kosongkan isi keranjang dan reset total ke nol.
     */
    public function clearCart(Cart $cart): void;

    public function callBonusEngine(int $orderId): void;

    public function markOrderBonusGenerated(Order $order): void;
}
