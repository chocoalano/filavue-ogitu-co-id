<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CartController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly CartService $cartService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/cart/items",
     *     tags={"Cart"},
     *     summary="Tambah item ke keranjang",
     *     description="Menambahkan produk ke keranjang customer yang terautentikasi.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"product_id","qty"},
     *
     *             @OA\Property(property="product_id", type="integer", example=101),
     *             @OA\Property(property="qty", type="integer", minimum=1, example=2)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Item berhasil ditambahkan", @OA\JsonContent(example={"message":"Produk berhasil ditambahkan ke keranjang."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function addItem(AddItemRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $product = Product::query()->findOrFail($request->productId());

        $this->cartService->addItem($customer, $product, $request->qty());

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/cart/items/{cartItem}",
     *     tags={"Cart"},
     *     summary="Perbarui qty item keranjang",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="cartItem", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"qty"},
     *
     *             @OA\Property(property="qty", type="integer", minimum=1, example=3)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Qty item berhasil diperbarui", @OA\JsonContent(example={"message":"Jumlah produk berhasil diperbarui."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(example={"message":"Akses ditolak."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        if ((int) $cartItem->cart->customer_id !== (int) $customer->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $this->cartService->updateQty($cartItem, $request->qty());

        return response()->json([
            'message' => 'Jumlah produk berhasil diperbarui.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/items/{cartItem}",
     *     tags={"Cart"},
     *     summary="Hapus item keranjang",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="cartItem", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Item berhasil dihapus", @OA\JsonContent(example={"message":"Produk berhasil dihapus dari keranjang."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Akses ditolak", @OA\JsonContent(example={"message":"Akses ditolak."}))
     * )
     */
    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        if ((int) $cartItem->cart->customer_id !== (int) $customer->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $this->cartService->removeItem($cartItem);

        return response()->json([
            'message' => 'Produk berhasil dihapus dari keranjang.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Kosongkan keranjang",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Keranjang berhasil dikosongkan", @OA\JsonContent(example={"message":"Keranjang belanja berhasil dikosongkan."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function clearCart(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->cartService->clearCart($customer);

        return response()->json([
            'message' => 'Keranjang belanja berhasil dikosongkan.',
        ]);
    }
}
