<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wishlist\BulkWishlistItemsRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Toggle produk dalam wishlist customer (tambah jika belum ada, hapus jika sudah ada).
     */
    public function toggle(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        /** @var Customer $customer */
        $customer = $request->user('customer');

        $product = Product::query()->findOrFail($request->product_id);

        $action = $this->wishlistService->toggle($customer, $product);

        return back()->with('wishlist_action', $action);
    }

    /**
     * Hapus satu item dari wishlist.
     */
    public function removeItem(Request $request, WishlistItem $wishlistItem): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        abort_unless($wishlistItem->wishlist->customer_id === $customer->id, 403);

        $this->wishlistService->removeItem($wishlistItem);

        return back()->with('success', 'Item berhasil dihapus dari wishlist.');
    }

    /**
     * Hapus beberapa item yang dipilih dari wishlist.
     */
    public function removeSelected(BulkWishlistItemsRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->wishlistService->removeSelected($customer, $request->itemIds());

        return back()->with('success', 'Item yang dipilih berhasil dihapus dari wishlist.');
    }

    /**
     * Kosongkan seluruh wishlist customer.
     */
    public function clearWishlist(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->wishlistService->clearWishlist($customer);

        return back()->with('success', 'Wishlist berhasil dikosongkan.');
    }

    /**
     * Pindahkan satu item wishlist ke keranjang belanja.
     */
    public function moveToCart(Request $request, WishlistItem $wishlistItem): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        abort_unless($wishlistItem->wishlist->customer_id === $customer->id, 403);

        $this->wishlistService->moveToCart($customer, $wishlistItem);

        return back()->with('success', 'Produk berhasil dipindahkan ke keranjang.');
    }

    /**
     * Pindahkan beberapa item yang dipilih ke keranjang belanja.
     */
    public function bulkMoveToCart(BulkWishlistItemsRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->wishlistService->bulkMoveToCart($customer, $request->itemIds());

        return back()->with('success', 'Produk yang dipilih berhasil dipindahkan ke keranjang.');
    }
}
