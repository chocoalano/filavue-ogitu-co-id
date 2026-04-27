<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\Cart\Contracts\CartRepositoryInterface;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function findOrCreateForCustomer(int $customerId, string $currency): Cart
    {
        return Cart::query()->firstOrCreate(
            ['customer_id' => $customerId],
            [
                'currency' => $currency,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]
        );
    }

    public function findForCustomer(int $customerId): ?Cart
    {
        return Cart::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customerId)
            ->first();
    }

    public function findItemByProduct(Cart $cart, int $productId): ?CartItem
    {
        return $cart->items()->where('product_id', $productId)->first();
    }

    public function createItem(Cart $cart, Product $product, int $qty, ?float $unitPrice = null, ?array $metaJson = null): CartItem
    {
        $price = $unitPrice ?? (float) $product->base_price;

        return $cart->items()->create([
            'product_id' => $product->id,
            'qty' => $qty,
            'unit_price' => $price,
            'currency' => $product->currency ?? 'IDR',
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'row_total' => $qty * $price,
            'meta_json' => $metaJson,
        ]);
    }

    public function updateItemQty(CartItem $item, int $newQty): void
    {
        $item->update([
            'qty' => $newQty,
            'row_total' => $newQty * (float) $item->unit_price,
        ]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearItems(Cart $cart): void
    {
        $cart->items()->delete();

        $cart->update([
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
        ]);
    }

    public function recalculate(Cart $cart): void
    {
        $subtotal = (float) $cart->items()->sum('row_total');

        $cart->update([
            'subtotal_amount' => $subtotal,
            'grand_total' => $subtotal
                + (float) $cart->shipping_amount
                + (float) $cart->tax_amount
                - (float) $cart->discount_amount,
        ]);
    }
}
