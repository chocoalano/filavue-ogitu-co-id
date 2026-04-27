<?php

namespace App\Repositories\Cart\Contracts;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

interface CartRepositoryInterface
{
    /**
     * Ambil keranjang aktif milik customer, atau buat baru jika belum ada.
     */
    public function findOrCreateForCustomer(int $customerId, string $currency): Cart;

    /**
     * Ambil keranjang aktif milik customer beserta item dan produknya.
     */
    public function findForCustomer(int $customerId): ?Cart;

    /**
     * Cari item keranjang berdasarkan produk.
     */
    public function findItemByProduct(Cart $cart, int $productId): ?CartItem;

    /**
     * Tambahkan produk baru ke keranjang.
     * $unitPrice opsional — jika null maka menggunakan product->base_price.
     * $metaJson opsional — untuk menyimpan info promo (original_price, promo_code, dll).
     *
     * @param  array<string,mixed>|null  $metaJson
     */
    public function createItem(Cart $cart, Product $product, int $qty, ?float $unitPrice = null, ?array $metaJson = null): CartItem;

    /**
     * Perbarui qty item yang sudah ada di keranjang.
     */
    public function updateItemQty(CartItem $item, int $newQty): void;

    /**
     * Hapus satu item dari keranjang.
     */
    public function removeItem(CartItem $item): void;

    /**
     * Hapus semua item dan reset total keranjang.
     */
    public function clearItems(Cart $cart): void;

    /**
     * Hitung ulang subtotal dan grand total keranjang berdasarkan item-itemnya.
     */
    public function recalculate(Cart $cart): void;
}
