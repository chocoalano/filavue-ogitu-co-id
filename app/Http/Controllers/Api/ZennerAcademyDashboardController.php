<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Services\ZennerAcademy\AcademyDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller untuk halaman dashboard Zenner Academy.
 *
 * Mengagregasikan data hero, kursus yang sedang ditonton,
 * dan daftar kategori dalam satu endpoint.
 * Memerlukan autentikasi Sanctum (customer).
 */
class ZennerAcademyDashboardController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly AcademyDashboardService $academyDashboardService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/zenner-academy/dashboard",
     *     tags={"Zenner Academy - Dashboard"},
     *     summary="Data dashboard Zenner Academy",
     *     description="Mengembalikan data lengkap halaman utama Zenner Academy: bagian hero, kursus yang sedang ditonton, dan daftar kategori.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="hero",
     *                     type="object",
     *                     description="Bagian atas halaman dengan judul dan notifikasi.",
     *                     @OA\Property(property="title", type="string", example="Pusat Edukasi"),
     *                     @OA\Property(property="subtitle", type="string", example="Belajar lebih cepat dengan materi singkat & terstruktur."),
     *                     @OA\Property(property="unreadNotifications", type="integer", example=3)
     *                 ),
     *                 @OA\Property(
     *                     property="continueWatching",
     *                     nullable=true,
     *                     type="object",
     *                     description="Kursus terakhir yang sedang ditonton. Null jika belum ada progres.",
     *                     @OA\Property(property="courseId", type="string", example="crs_pemasaran-digital"),
     *                     @OA\Property(property="courseTitle", type="string", example="Pemasaran Digital"),
     *                     @OA\Property(property="moduleId", type="string", nullable=true, example="mod_3"),
     *                     @OA\Property(property="moduleTitle", type="string", nullable=true, example="Modul 3: Lead Generation"),
     *                     @OA\Property(property="progress", type="number", format="float", example=0.65),
     *                     @OA\Property(
     *                         property="resume",
     *                         nullable=true,
     *                         type="object",
     *                         @OA\Property(property="contentType", type="string", example="video"),
     *                         @OA\Property(property="positionSec", type="integer", example=742),
     *                         @OA\Property(property="durationSec", type="integer", example=1142)
     *                     ),
     *                     @OA\Property(property="thumbnailUrl", type="string", nullable=true, example="https://cdn.example.com/thumbs/crs_001.jpg"),
     *                     @OA\Property(
     *                         property="action",
     *                         type="object",
     *                         @OA\Property(property="label", type="string", example="Lanjutkan"),
     *                         @OA\Property(property="deeplink", type="string", example="app://education/course/pemasaran-digital/module/slug-modul")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     description="Daftar kategori Zenner Academy terurut.",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="string", example="cat_marketing"),
     *                         @OA\Property(property="label", type="string", example="Pemasaran"),
     *                         @OA\Property(property="iconKey", type="string", nullable=true, example="campaign_rounded"),
     *                         @OA\Property(property="accentHex", type="string", nullable=true, example="#60A5FA"),
     *                         @OA\Property(property="sort", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tidak terautentikasi.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if ($customer === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $data = $this->academyDashboardService->getPageData($customer);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
        ]);
    }
}
