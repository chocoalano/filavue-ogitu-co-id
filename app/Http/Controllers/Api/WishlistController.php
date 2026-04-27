<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wishlist\BulkWishlistItemsRequest;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class WishlistController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly WishlistService $wishlistService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/wishlist/toggle",
     *     tags={"Wishlist"},
     *     summary="Toggle wishlist item",
     *     description="Menambahkan produk ke wishlist jika belum ada, atau menghapus jika sudah ada.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"product_id"},
     *
     *             @OA\Property(property="product_id", type="integer", example=101)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Wishlist berhasil diperbarui", @OA\JsonContent(example={"message":"Wishlist berhasil diperbarui.","data":{"action":"added"}})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function toggle(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::query()->findOrFail((int) $request->input('product_id'));
        $action = $this->wishlistService->toggle($customer, $product);

        return response()->json([
            'message' => 'Wishlist berhasil diperbarui.',
            'data' => [
                'action' => $action,
            ],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlist/items/{wishlistItem}",
     *     tags={"Wishlist"},
     *     summary="Hapus item wishlist",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="wishlistItem", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Item wishlist berhasil dihapus", @OA\JsonContent(example={"message":"Item berhasil dihapus dari wishlist."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(example={"message":"Akses ditolak."}))
     * )
     */
    public function removeItem(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        if ((int) $wishlistItem->wishlist->customer_id !== (int) $customer->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $this->wishlistService->removeItem($wishlistItem);

        return response()->json([
            'message' => 'Item berhasil dihapus dari wishlist.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/wishlist/remove-selected",
     *     tags={"Wishlist"},
     *     summary="Hapus beberapa item wishlist",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"ids"},
     *
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 example={1,2,3},
     *
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Item terpilih berhasil dihapus", @OA\JsonContent(example={"message":"Item yang dipilih berhasil dihapus dari wishlist."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function removeSelected(BulkWishlistItemsRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->wishlistService->removeSelected($customer, $request->itemIds());

        return response()->json([
            'message' => 'Item yang dipilih berhasil dihapus dari wishlist.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlist",
     *     tags={"Wishlist"},
     *     summary="Kosongkan wishlist",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Wishlist berhasil dikosongkan", @OA\JsonContent(example={"message":"Wishlist berhasil dikosongkan."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function clearWishlist(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->wishlistService->clearWishlist($customer);

        return response()->json([
            'message' => 'Wishlist berhasil dikosongkan.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/wishlist/items/{wishlistItem}/move-to-cart",
     *     tags={"Wishlist"},
     *     summary="Pindahkan item wishlist ke keranjang",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="wishlistItem", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(response=200, description="Produk berhasil dipindahkan ke keranjang", @OA\JsonContent(example={"message":"Produk berhasil dipindahkan ke keranjang."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(example={"message":"Akses ditolak."}))
     * )
     */
    public function moveToCart(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        if ((int) $wishlistItem->wishlist->customer_id !== (int) $customer->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $this->wishlistService->moveToCart($customer, $wishlistItem);

        return response()->json([
            'message' => 'Produk berhasil dipindahkan ke keranjang.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/wishlist/move-to-cart",
     *     tags={"Wishlist"},
     *     summary="Pindahkan beberapa item wishlist ke keranjang",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"ids"},
     *
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 example={1,2,3},
     *
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Item terpilih berhasil dipindahkan ke keranjang", @OA\JsonContent(example={"message":"Produk yang dipilih berhasil dipindahkan ke keranjang."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function bulkMoveToCart(BulkWishlistItemsRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->wishlistService->bulkMoveToCart($customer, $request->itemIds());

        return response()->json([
            'message' => 'Produk yang dipilih berhasil dipindahkan ke keranjang.',
        ]);
    }
}
