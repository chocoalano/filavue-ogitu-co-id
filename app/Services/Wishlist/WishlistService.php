<?php

namespace App\Services\Wishlist;

use App\Models\Customer;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Repositories\Wishlist\Contracts\WishlistRepositoryInterface;
use App\Services\Cart\CartService;

class WishlistService
{
    public function __construct(
        protected WishlistRepositoryInterface $wishlistRepository,
        protected CartService $cartService,
    ) {}

    /**
     * Toggle produk pada wishlist: tambah jika belum ada, hapus jika sudah.
     *
     * @return 'added'|'removed'
     */
    public function toggle(Customer $customer, Product $product): string
    {
        $wishlist = $this->wishlistRepository->findOrCreateForCustomer($customer->id);
        $existing = $this->wishlistRepository->findItemByProduct($wishlist, $product->id);

        if ($existing) {
            $this->wishlistRepository->removeItem($existing);

            return 'removed';
        }

        $this->wishlistRepository->addItem($wishlist, $product);

        return 'added';
    }

    /**
     * Hapus satu item dari wishlist.
     */
    public function removeItem(WishlistItem $item): void
    {
        $this->wishlistRepository->removeItem($item);
    }

    /**
     * Hapus beberapa item sekaligus berdasarkan ID.
     *
     * @param array<int> $itemIds
     */
    public function removeSelected(Customer $customer, array $itemIds): void
    {
        $wishlist = $this->wishlistRepository->findByCustomerId($customer->id);

        if (! $wishlist) {
            return;
        }

        $this->wishlistRepository->removeItemsByIds($wishlist, $itemIds);
    }

    /**
     * Kosongkan semua item wishlist customer.
     */
    public function clearWishlist(Customer $customer): void
    {
        $wishlist = $this->wishlistRepository->findByCustomerId($customer->id);

        if (! $wishlist) {
            return;
        }

        $this->wishlistRepository->clearItems($wishlist);
    }

    /**
     * Pindahkan satu item dari wishlist ke keranjang belanja.
     * Item dihapus dari wishlist setelah berhasil ditambahkan ke cart.
     */
    public function moveToCart(Customer $customer, WishlistItem $item): void
    {
        $product = $item->product;

        if (! $product || $product->stock < 1) {
            return;
        }

        $this->cartService->addItem($customer, $product, 1);
        $this->wishlistRepository->removeItem($item);
    }

    /**
     * Pindahkan beberapa item (yang masih stok) dari wishlist ke keranjang.
     *
     * @param array<int> $itemIds
     */
    public function bulkMoveToCart(Customer $customer, array $itemIds): void
    {
        $wishlist = $this->wishlistRepository->findWithItemsForCustomer($customer->id);

        if (! $wishlist) {
            return;
        }

        $items = $wishlist->items
            ->whereIn('id', $itemIds)
            ->filter(fn (WishlistItem $item) => ($item->product?->stock ?? 0) > 0);

        foreach ($items as $item) {
            try {
                $this->cartService->addItem($customer, $item->product, 1);
                $this->wishlistRepository->removeItem($item);
            } catch (\Throwable) {
                // Lewati item yang gagal ditambahkan (misal: stok baru habis)
            }
        }
    }
}
