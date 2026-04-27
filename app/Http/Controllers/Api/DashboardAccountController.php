<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class DashboardAccountController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/dashboard/account/profile",
     *     tags={"Dashboard Account"},
     *     summary="Perbarui profil akun customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"username","name","nik","gender","email","phone","bank_name","bank_account"},
     *
     *             @OA\Property(property="username", type="string", example="budi.santoso"),
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="nik", type="string", example="3276010101010001"),
     *             @OA\Property(property="gender", type="string", enum={"L","P"}, example="L"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="bank_name", type="string", example="BCA"),
     *             @OA\Property(property="bank_account", type="string", example="1234567890"),
     *             @OA\Property(property="npwp_nama", type="string", nullable=true, example="Budi Santoso"),
     *             @OA\Property(property="npwp_number", type="string", nullable=true, example="12.345.678.9-012.000"),
     *             @OA\Property(property="npwp_jk", type="integer", nullable=true, enum={1,2}, example=1),
     *             @OA\Property(property="npwp_date", type="string", format="date", nullable=true, example="2024-01-31"),
     *             @OA\Property(property="npwp_alamat", type="string", nullable=true, example="Jl. Merdeka No. 1, Bandung"),
     *             @OA\Property(property="npwp_menikah", type="string", nullable=true, enum={"Y","N"}, example="Y"),
     *             @OA\Property(property="npwp_anak", type="string", nullable=true, example="2"),
     *             @OA\Property(property="npwp_kerja", type="string", nullable=true, enum={"Y","N"}, example="Y"),
     *             @OA\Property(property="npwp_office", type="string", nullable=true, example="PT Contoh Sukses")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Profil berhasil diperbarui", @OA\JsonContent(example={"message":"Profil akun berhasil diperbarui."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function update(UpdateAccountProfileRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $this->dashboardService->updateAccountProfile($customer, $request->payload());

            return response()->json([
                'message' => 'Profil akun berhasil diperbarui.',
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memperbarui profil akun.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
