<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class BerandaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/home",
     *     tags={"Home"},
     *     summary="Ambil data beranda",
     *     description="Mengembalikan banner promo, produk unggulan, dan showcase brand untuk halaman beranda.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data beranda berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data beranda berhasil diambil.",
     *                 "data":{
     *                     "heroBanners":{{"id":1,"name":"Promo Mingguan","image":"https://example.com/banner.jpg"}},
     *                     "featuredProducts":{{"id":11,"name":"Produk A","price":125000}},
     *                     "brands":{{"name":"Brand X","slug":"brand-x","productCount":10}}
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function index(HomeService $homeService): JsonResponse
    {
        $payload = [];

        foreach ($homeService->getIndexPageData() as $key => $value) {
            $payload[$key] = $value instanceof \Closure ? $value() : $value;
        }

        return response()->json([
            'message' => 'Data beranda berhasil diambil.',
            'data' => $payload,
        ]);
    }
}
