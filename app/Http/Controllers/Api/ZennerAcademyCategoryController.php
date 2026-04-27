<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZennerAcademy\ContentCategory\IndexContentCategoryRequest;
use App\Http\Requests\ZennerAcademy\ContentCategory\StoreContentCategoryRequest;
use App\Http\Requests\ZennerAcademy\ContentCategory\UpdateContentCategoryRequest;
use App\Services\ZennerAcademy\ContentCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk CRUD kategori konten Zenner Academy.
 *
 * Endpoint publik: index, parents, show, showBySlug.
 * Endpoint terproteksi (auth:sanctum): store, update, destroy.
 */
class ZennerAcademyCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/zenner-academy/categories",
     *     tags={"Zenner Academy - Category"},
     *     summary="Daftar kategori (pohon hierarki)",
     *     description="Menampilkan semua kategori beserta sub-kategori dan jumlah konten. Gunakan ?parents_only=true untuk hanya mendapatkan kategori root.",
     *
     *     @OA\Parameter(name="parents_only", in="query", required=false, @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="Daftar kategori berhasil diambil")
     * )
     */
    public function index(IndexContentCategoryRequest $request, ContentCategoryService $categoryService): JsonResponse
    {
        $parentsOnly = $request->payload()['parents_only'];

        $categories = $parentsOnly
            ? $categoryService->getParentCategories()
            : $categoryService->getCategoryTree();

        return response()->json([
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => $categories,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/categories/parents",
     *     tags={"Zenner Academy - Category"},
     *     summary="Daftar kategori root atau sub-kategori dari parent tertentu",
     *     description="Tanpa ?parent: kembalikan semua kategori root. Dengan ?parent=<id>: kembalikan info parent beserta sub-kategorinya.",
     *
     *     @OA\Parameter(name="parent", in="query", required=false, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Berhasil diambil"),
     *     @OA\Response(response=404, description="Kategori parent tidak ditemukan")
     * )
     */
    public function parents(Request $request, ContentCategoryService $categoryService): JsonResponse
    {
        $parentId = $request->integer('parent', 0) ?: null;

        if ($parentId !== null) {
            try {
                $data = $categoryService->getSubCategoriesByParent($parentId);
            } catch (ModelNotFoundException) {
                return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
            }

            return response()->json([
                'message' => 'Daftar sub-kategori berhasil diambil.',
                'data' => $data,
            ]);
        }

        return response()->json([
            'message' => 'Daftar kategori induk berhasil diambil.',
            'data' => $categoryService->getParentCategories(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/zenner-academy/categories",
     *     tags={"Zenner Academy - Category"},
     *     summary="Buat kategori baru",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="parent_id", type="integer"),
     *         @OA\Property(property="slug", type="string")
     *     )),
     *
     *     @OA\Response(response=201, description="Kategori berhasil dibuat")
     * )
     */
    public function store(StoreContentCategoryRequest $request, ContentCategoryService $categoryService): JsonResponse
    {
        $category = $categoryService->storeCategory($request->payload());

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data' => $category,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/categories/{id}",
     *     tags={"Zenner Academy - Category"},
     *     summary="Detail kategori",
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Detail kategori berhasil diambil"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function show(int $category, ContentCategoryService $categoryService): JsonResponse
    {
        try {
            $data = $categoryService->getCategoryDetail($category);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/categories/slug/{slug}",
     *     tags={"Zenner Academy - Category"},
     *     summary="Detail kategori berdasarkan slug",
     *
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Detail kategori berhasil diambil"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function showBySlug(string $slug, ContentCategoryService $categoryService): JsonResponse
    {
        try {
            $data = $categoryService->getCategoryDetailBySlug($slug);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/zenner-academy/categories/{id}",
     *     tags={"Zenner Academy - Category"},
     *     summary="Update kategori",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Kategori berhasil diperbarui"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function update(UpdateContentCategoryRequest $request, int $category, ContentCategoryService $categoryService): JsonResponse
    {
        try {
            $data = $categoryService->updateCategory($category, $request->payload());
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/zenner-academy/categories/{id}",
     *     tags={"Zenner Academy - Category"},
     *     summary="Hapus kategori",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Kategori berhasil dihapus"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function destroy(int $category, ContentCategoryService $categoryService): JsonResponse
    {
        try {
            $categoryService->deleteCategory($category);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
