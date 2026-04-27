<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\MidtransCallbackRequest;
use App\Services\Payment\MidtransCallbackService;
use Illuminate\Http\JsonResponse;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransCallbackService $midtransCallbackService,
    ) {}

    public function __invoke(MidtransCallbackRequest $request): JsonResponse
    {
        $result = $this->midtransCallbackService->handle($request->payload());

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['http_code']);
    }
}

