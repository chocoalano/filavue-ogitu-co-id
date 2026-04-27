<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Tambah produk ke keranjang belanja customer.
     */
    public function addItem(AddItemRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $product = Product::query()->findOrFail($request->productId());

        $this->cartService->addItem($customer, $product, $request->qty());

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Perbarui qty item di keranjang.
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        abort_unless($cartItem->cart->customer_id === $customer->id, 403);

        $this->cartService->updateQty($cartItem, $request->qty());

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Hapus satu item dari keranjang.
     */
    public function removeItem(Request $request, CartItem $cartItem): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        abort_unless($cartItem->cart->customer_id === $customer->id, 403);

        $this->cartService->removeItem($cartItem);

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Kosongkan semua item di keranjang customer.
     */
    public function clearCart(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->cartService->clearCart($customer);

        return back()->with('success', 'Keranjang belanja berhasil dikosongkan.');
    }
}
