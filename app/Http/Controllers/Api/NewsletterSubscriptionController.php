<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\SubscribeNewsletterRequest;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class NewsletterSubscriptionController extends Controller
{
    public function __construct(
        private readonly NewsletterSubscriptionService $newsletterSubscriptionService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/newsletter/subscribe",
     *     tags={"Newsletter"},
     *     summary="Berlangganan newsletter",
     *     description="Menambahkan email ke daftar newsletter. Jika email sudah terdaftar, timestamp langganan akan diperbarui.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="member@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permintaan berlangganan berhasil diproses",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Berhasil berlangganan promo terbaru.",
     *                 "data":{"is_new_subscriber":true}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data tidak valid.",
     *                 "errors":{"email":{"Format alamat email tidak valid."}}
     *             }
     *         )
     *     )
     * )
     */
    public function __invoke(SubscribeNewsletterRequest $request): JsonResponse
    {
        $isNewSubscriber = $this->newsletterSubscriptionService->subscribe(
            $request->email(),
            $request->ip()
        );

        return response()->json([
            'message' => $isNewSubscriber
                ? 'Berhasil berlangganan promo terbaru.'
                : 'Email sudah terdaftar. Promo terbaru tetap akan kami kirimkan.',
            'data' => [
                'is_new_subscriber' => $isNewSubscriber,
            ],
        ]);
    }
}
