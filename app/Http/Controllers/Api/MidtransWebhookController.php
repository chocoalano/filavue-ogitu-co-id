<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\MidtransCallbackRequest;
use App\Services\Payment\MidtransCallbackService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransCallbackService $midtransCallbackService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/payments/midtrans/callback",
     *     tags={"Payments"},
     *     summary="Webhook callback Midtrans",
     *     description="Endpoint callback dari Midtrans untuk sinkronisasi status pembayaran order dan wallet topup.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"order_id","status_code","gross_amount","signature_key"},
     *
     *             @OA\Property(property="order_id", type="string", example="ORD-20260228-ABC123"),
     *             @OA\Property(property="status_code", type="string", example="200"),
     *             @OA\Property(property="gross_amount", type="number", format="float", example=125000),
     *             @OA\Property(property="signature_key", type="string", example="midtrans-signature"),
     *             @OA\Property(property="transaction_status", type="string", example="settlement"),
     *             @OA\Property(property="fraud_status", type="string", example="accept"),
     *             @OA\Property(property="payment_type", type="string", example="bank_transfer"),
     *             @OA\Property(property="transaction_id", type="string", example="txn-123456")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Callback berhasil diproses",
     *
     *         @OA\JsonContent(example={"status":"success","message":"Order callback processed."})
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Payload tidak valid",
     *
     *         @OA\JsonContent(example={"status":"error","message":"Invalid payload."})
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Signature tidak valid",
     *
     *         @OA\JsonContent(example={"status":"error","message":"Invalid signature."})
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Gagal memproses callback",
     *
     *         @OA\JsonContent(example={"status":"error","message":"Server error."})
     *     )
     * )
     */
    public function __invoke(MidtransCallbackRequest $request): JsonResponse
    {
        $result = $this->midtransCallbackService->handle($request->payload());

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['http_code']);
    }
}
