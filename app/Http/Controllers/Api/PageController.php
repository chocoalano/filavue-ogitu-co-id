<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Pages\PageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class PageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pages/{slug}",
     *     tags={"Pages"},
     *     summary="Detail halaman statis",
     *     description="Menampilkan detail halaman statis berdasarkan slug.",
     *
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Halaman berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Halaman berhasil diambil.",
     *                 "data":{
     *                     "page":{"id":1,"title":"Tentang Kami","slug":"tentang-kami"},
     *                     "seo":{"title":"Tentang Kami"}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Halaman tidak ditemukan",
     *
     *         @OA\JsonContent(example={"message":"Halaman tidak ditemukan."})
     *     )
     * )
     */
    public function show(string $slug, PageService $pageService): JsonResponse
    {
        try {
            $data = $pageService->getShowPageData($slug);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Halaman tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Halaman berhasil diambil.',
            'data' => $data,
        ]);
    }
}
