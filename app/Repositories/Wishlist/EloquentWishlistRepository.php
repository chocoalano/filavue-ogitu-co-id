<?php

namespace App\Repositories\Wishlist;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Repositories\Wishlist\Contracts\WishlistRepositoryInterface;

class EloquentWishlistRepository implements WishlistRepositoryInterface
{
    public function findOrCreateForCustomer(int $customerId): Wishlist
    {
        return Wishlist::query()->firstOrCreate(
            ['customer_id' => $customerId],
            ['name' => 'Default']
        );
    }

    public function findByCustomerId(int $customerId): ?Wishlist
    {
        return Wishlist::query()->where('customer_id', $customerId)->first();
    }

    public function findWithItemsForCustomer(int $customerId): ?Wishlist
    {
        return Wishlist::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customerId)
            ->first();
    }

    public function findItemByProduct(Wishlist $wishlist, int $productId): ?WishlistItem
    {
        return $wishlist->items()->where('product_id', $productId)->first();
    }

    public function addItem(Wishlist $wishlist, Product $product): WishlistItem
    {
        return $wishlist->items()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'product_name' => $product->name,
                'product_sku'  => $product->sku,
            ]
        );
    }

    public function removeItem(WishlistItem $item): void
    {
        $item->delete();
    }

    public function removeItemsByIds(Wishlist $wishlist, array $itemIds): void
    {
        $wishlist->items()->whereIn('id', $itemIds)->delete();
    }

    public function clearItems(Wishlist $wishlist): void
    {
        $wishlist->items()->delete();
    }
}
