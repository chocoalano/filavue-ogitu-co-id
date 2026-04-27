<?php

namespace App\Repositories\Wishlist\Contracts;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;

interface WishlistRepositoryInterface
{
    /**
     * Ambil wishlist aktif milik customer, atau buat baru jika belum ada.
     */
    public function findOrCreateForCustomer(int $customerId): Wishlist;

    /**
     * Ambil wishlist milik customer (tanpa eager loading) — untuk operasi tulis.
     */
    public function findByCustomerId(int $customerId): ?Wishlist;

    /**
     * Ambil wishlist milik customer beserta item dan produknya — untuk tampilan.
     */
    public function findWithItemsForCustomer(int $customerId): ?Wishlist;

    /**
     * Cari item wishlist berdasarkan produk.
     */
    public function findItemByProduct(Wishlist $wishlist, int $productId): ?WishlistItem;

    /**
     * Tambahkan produk ke wishlist (idempotent).
     */
    public function addItem(Wishlist $wishlist, Product $product): WishlistItem;

    /**
     * Hapus satu item dari wishlist.
     */
    public function removeItem(WishlistItem $item): void;

    /**
     * Hapus beberapa item sekaligus berdasarkan ID.
     *
     * @param array<int> $itemIds
     */
    public function removeItemsByIds(Wishlist $wishlist, array $itemIds): void;

    /**
     * Hapus semua item dari wishlist.
     */
    public function clearItems(Wishlist $wishlist): void;
}
