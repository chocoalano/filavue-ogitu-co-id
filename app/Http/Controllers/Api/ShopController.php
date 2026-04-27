<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use App\Services\Products\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ShopController extends Controller
{
    use ResolvesSanctumCustomer;

    /**
     * @OA\Get(
     *     path="/api/shop",
     *     tags={"Shop"},
     *     summary="Daftar produk toko",
     *     description="Menampilkan daftar produk toko lengkap dengan data filter, kategori, dan brand.",
     *
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="brand", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data produk berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data produk berhasil diambil.",
     *                 "data":{
     *                     "products":{"data":{{"id":101,"name":"Produk A","slug":"produk-a","price":125000}}},
     *                     "categories":{{"id":1,"name":"Kategori A"}},
     *                     "brands":{{"name":"Brand X"}},
     *                     "filterStats":{"total":1},
     *                     "filters":{"search":"produk"}
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function index(Request $request, ProductService $productService): JsonResponse
    {
        return response()->json([
            'message' => 'Data produk berhasil diambil.',
            'data' => $productService->getShopData($request->all()),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/shop/{slug}",
     *     tags={"Shop"},
     *     summary="Detail produk",
     *     description="Menampilkan detail produk berdasarkan slug termasuk ulasan dan rekomendasi produk.",
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detail produk berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Detail produk berhasil diambil.",
     *                 "data":{
     *                     "slug":"produk-a",
     *                     "isInWishlist":false,
     *                     "product":{"id":101,"name":"Produk A","slug":"produk-a","priceFrom":125000},
     *                     "reviews":{},
     *                     "recommendations":{}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Produk tidak ditemukan",
     *
     *         @OA\JsonContent(example={"message":"Produk tidak ditemukan."})
     *     )
     * )
     */
    public function show(Request $request, string $slug, ProductService $productService): JsonResponse
    {
        try {
            $data = $productService->getProductShowData($slug);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        if (! is_array($data) || ! is_array($data['product'] ?? null)) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $productId = $data['product']['id'] ?? null;
        $isInWishlist = $this->checkIsInWishlist($request, $productId);

        return response()->json([
            'message' => 'Detail produk berhasil diambil.',
            'data' => [
                ...$data,
                'slug' => $slug,
                'isInWishlist' => $isInWishlist,
            ],
        ]);
    }

    private function checkIsInWishlist(Request $request, int|string|null $productId): bool
    {
        if (! $productId) {
            return false;
        }

        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return false;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($query) => $query->where('customer_id', $customer->id))
            ->where('product_id', $productId)
            ->exists();
    }
}
