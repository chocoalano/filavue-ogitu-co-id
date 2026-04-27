<?php

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\Cart\Contracts\CartRepositoryInterface;
use App\Services\Products\ProductPromotionService;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductPromotionService $promotionService,
    ) {}

    /**
     * Tambah produk ke keranjang customer.
     * Harga yang disimpan adalah harga efektif setelah promo (jika ada).
     * Informasi promo disimpan dalam meta_json — tidak mengubah kolom DB.
     *
     * @throws ValidationException
     */
    public function addItem(Customer $customer, Product $product, int $qty): void
    {
        if ($product->stock < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Stok produk sedang habis.',
            ]);
        }

        $cart = $this->cartRepository->findOrCreateForCustomer(
            $customer->id,
            $product->currency ?? 'IDR'
        );

        $safeQty = min($qty, $product->stock);
        $existingItem = $this->cartRepository->findItemByProduct($cart, $product->id);

        if ($existingItem) {
            $newQty = min($existingItem->qty + $qty, $product->stock);
            $this->updateItemWithPromo($existingItem, $product, $newQty);
        } else {
            $priceData = $this->promotionService->resolvePrice($product, $safeQty);
            $this->cartRepository->createItem(
                $cart,
                $product,
                $safeQty,
                $priceData['unit_price'],
                $priceData['promo_meta'],
            );
        }

        $this->cartRepository->recalculate($cart);
    }

    /**
     * Perbarui qty item keranjang dan hitung ulang harga promo jika berlaku.
     */
    public function updateQty(CartItem $item, int $qty): void
    {
        $product = $item->product;

        if ($product && $qty > $product->stock) {
            $qty = max(1, $product->stock);
        }

        if ($product) {
            $this->updateItemWithPromo($item, $product, $qty);
        } else {
            $this->cartRepository->updateItemQty($item, $qty);
        }

        $this->cartRepository->recalculate($item->cart);
    }

    /**
     * Hapus satu item dari keranjang dan hitung ulang total.
     */
    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;

        $this->cartRepository->removeItem($item);
        $this->cartRepository->recalculate($cart);
    }

    /**
     * Kosongkan semua item keranjang customer.
     */
    public function clearCart(Customer $customer): void
    {
        $cart = $this->cartRepository->findForCustomer($customer->id);

        if (! $cart) {
            return;
        }

        $this->cartRepository->clearItems($cart);
    }

    /**
     * Hitung ulang harga promo untuk item yang ada, lalu update.
     * Promo dievaluasi ulang karena qty bisa berpengaruh pada min_qty threshold.
     */
    private function updateItemWithPromo(CartItem $item, Product $product, int $newQty): void
    {
        $priceData = $this->promotionService->resolvePrice($product, $newQty);

        $item->update([
            'qty' => $newQty,
            'unit_price' => $priceData['unit_price'],
            'row_total' => $newQty * $priceData['unit_price'],
            'meta_json' => $priceData['promo_meta'],
        ]);
    }
}
