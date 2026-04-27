<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZennerAcademy\Content\IndexContentRequest;
use App\Http\Requests\ZennerAcademy\Content\StoreContentRequest;
use App\Http\Requests\ZennerAcademy\Content\UpdateContentRequest;
use App\Services\ZennerAcademy\ContentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * Controller untuk CRUD konten Zenner Academy.
 *
 * Endpoint publik: index, show, byCategory.
 * Endpoint terproteksi (auth:sanctum): store, update, destroy.
 */
class ZennerAccademyContentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/zenner-academy/contents",
     *     tags={"Zenner Academy - Content"},
     *     summary="Daftar konten",
     *     description="Daftar konten dengan filter: search, status, category_id.",
     *
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"published","draft"})),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *
     *     @OA\Response(response=200, description="Daftar konten berhasil diambil")
     * )
     */
    public function index(IndexContentRequest $request, ContentService $contentService): JsonResponse
    {
        $payload = $request->payload();

        $contents = $contentService->getContentList(
            filters: [
                'search' => $payload['search'],
                'status' => $payload['status'],
                'category_id' => $payload['category_id'],
            ],
            perPage: $payload['per_page'],
        );

        return response()->json([
            'message' => 'Daftar konten berhasil diambil.',
            'data' => $contents,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/zenner-academy/contents",
     *     tags={"Zenner Academy - Content"},
     *     summary="Buat konten baru",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"title","status"},
     *
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="category_id", type="integer"),
     *         @OA\Property(property="slug", type="string"),
     *         @OA\Property(property="content", type="string"),
     *         @OA\Property(property="file", type="string"),
     *         @OA\Property(property="vlink", type="string"),
     *         @OA\Property(property="status", type="string", enum={"published","draft"}),
     *         @OA\Property(property="created_by", type="integer")
     *     )),
     *
     *     @OA\Response(response=201, description="Konten berhasil dibuat")
     * )
     */
    public function store(StoreContentRequest $request, ContentService $contentService): JsonResponse
    {
        $content = $contentService->storeContent($request->payload());

        return response()->json([
            'message' => 'Konten berhasil dibuat.',
            'data' => $content,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/contents/{id}",
     *     tags={"Zenner Academy - Content"},
     *     summary="Detail konten",
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Detail konten berhasil diambil"),
     *     @OA\Response(response=404, description="Konten tidak ditemukan")
     * )
     */
    public function show(int $content, ContentService $contentService): JsonResponse
    {
        try {
            $data = $contentService->getContentDetail($content);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Konten tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail konten berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/zenner-academy/contents/{id}",
     *     tags={"Zenner Academy - Content"},
     *     summary="Update konten",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Konten berhasil diperbarui"),
     *     @OA\Response(response=404, description="Konten tidak ditemukan")
     * )
     */
    public function update(UpdateContentRequest $request, int $content, ContentService $contentService): JsonResponse
    {
        try {
            $data = $contentService->updateContent($content, $request->payload());
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Konten tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Konten berhasil diperbarui.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/zenner-academy/contents/{id}",
     *     tags={"Zenner Academy - Content"},
     *     summary="Hapus konten",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Konten berhasil dihapus"),
     *     @OA\Response(response=404, description="Konten tidak ditemukan")
     * )
     */
    public function destroy(int $content, ContentService $contentService): JsonResponse
    {
        try {
            $contentService->deleteContent($content);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Konten tidak ditemukan.'], 404);
        }

        return response()->json(['message' => 'Konten berhasil dihapus.']);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/categories/{categorySlug}/contents",
     *     tags={"Zenner Academy - Content"},
     *     summary="Daftar konten berdasarkan slug kategori",
     *
     *     @OA\Parameter(name="categorySlug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"published","draft"})),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *
     *     @OA\Response(response=200, description="Daftar konten berhasil diambil"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function byCategory(IndexContentRequest $request, string $categorySlug, ContentService $contentService): JsonResponse
    {
        $payload = $request->payload();

        try {
            $data = $contentService->getContentByCategory(
                categorySlug: $categorySlug,
                filters: [
                    'search' => $payload['search'],
                    'status' => $payload['status'],
                ],
                perPage: $payload['per_page'],
            );
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Daftar konten berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/contents/slug/{slug}",
     *     tags={"Zenner Academy - Content"},
     *     summary="Detail konten berdasarkan slug",
     *
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Detail konten berhasil diambil"),
     *     @OA\Response(response=404, description="Konten tidak ditemukan")
     * )
     */
    public function showBySlug(string $slug, ContentService $contentService): JsonResponse
    {
        try {
            $data = $contentService->getContentDetailBySlug($slug);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Konten tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail konten berhasil diambil.',
            'data' => $data,
        ]);
    }
}
