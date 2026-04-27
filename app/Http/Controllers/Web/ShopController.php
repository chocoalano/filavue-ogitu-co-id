<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\WishlistItem;
use App\Services\Products\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request, ProductService $productService)
    {
        $data = $productService->getShopData($request->all());

        if ($request->wantsJson()) {
            return response()->json($data['products']);
        }

        return Inertia::render('Shop/Index', $data);
    }

    public function show(Request $request, string $slug, ProductService $productService)
    {
        try {
            $data = $productService->getProductShowData($slug, false);
        } catch (ModelNotFoundException) {
            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        if (! is_array($data) || ! is_array($data['product'] ?? null)) {
            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        $data['slug'] = $slug;

        $productId = $data['product']['id'] ?? null;

        if (! is_int($productId) && ! is_string($productId)) {
            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        $numericProductId = is_numeric($productId) ? (int) $productId : null;

        if ($numericProductId === null) {
            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        $data['isInWishlist'] = $this->checkIsInWishlist($request, $numericProductId);
        $data['reviews'] = Inertia::scroll(
            fn () => $productService->getApprovedReviewsForInfiniteScroll($numericProductId, 8)
        );

        return Inertia::render('Shop/Show', $data);
    }

    /**
     * Cek apakah produk sudah ada di wishlist customer yang sedang login.
     */
    private function checkIsInWishlist(Request $request, int|string|null $productId): bool
    {
        if (! $productId) {
            return false;
        }

        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        if (! $customer) {
            return false;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($q) => $q->where('customer_id', $customer->id))
            ->where('product_id', $productId)
            ->exists();
    }
}
