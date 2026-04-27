<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\ArticleIndexRequest;
use App\Services\Articles\ArticleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="Daftar artikel",
     *     description="Menampilkan daftar artikel publik dengan filter pencarian, tag, urutan, dan pagination.",
     *
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="tag", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", enum={"newest","oldest","az","za"})),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar artikel berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Daftar artikel berhasil diambil.",
     *                 "data":{
     *                     "articles":{"data":{{"id":1,"title":"Artikel A","slug":"artikel-a"}}},
     *                     "filters":{"search":null,"tag":null,"sort":"newest","page":1},
     *                     "stats":{"total_articles":10}
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function index(ArticleIndexRequest $request, ArticleService $articleService): JsonResponse
    {
        return response()->json([
            'message' => 'Daftar artikel berhasil diambil.',
            'data' => $articleService->getIndexPageData($request->payload()),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{slug}",
     *     tags={"Articles"},
     *     summary="Detail artikel",
     *     description="Menampilkan detail artikel publik berdasarkan slug, termasuk artikel terkait.",
     *
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detail artikel berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Detail artikel berhasil diambil.",
     *                 "data":{
     *                     "article":{"id":1,"title":"Artikel A","slug":"artikel-a"},
     *                     "relatedArticles":{}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Artikel tidak ditemukan",
     *
     *         @OA\JsonContent(example={"message":"Artikel tidak ditemukan."})
     *     )
     * )
     */
    public function show(string $slug, ArticleService $articleService): JsonResponse
    {
        try {
            $data = $articleService->getShowPageData($slug);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail artikel berhasil diambil.',
            'data' => $data,
        ]);
    }
}
