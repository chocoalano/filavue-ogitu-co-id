<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\PlaceMemberRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class MlmPlacementController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/mlm/place-member",
     *     tags={"MLM"},
     *     summary="Tempatkan member pada binary tree",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"member_id","upline_id","position"},
     *
     *             @OA\Property(property="member_id", type="integer", example=120),
     *             @OA\Property(property="upline_id", type="integer", example=11),
     *             @OA\Property(property="position", type="string", enum={"left","right"}, example="left")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Member berhasil ditempatkan",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Member Andi berhasil ditempatkan di posisi left.",
     *                 "data":{"id":120,"name":"Andi","position":"left","upline_id":11}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function store(PlaceMemberRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $placedMember = $this->dashboardService->placeMember($customer, $request->payload());

        return response()->json([
            'message' => "Member {$placedMember['name']} berhasil ditempatkan di posisi {$placedMember['position']}.",
            'data' => $placedMember,
        ]);
    }
}
