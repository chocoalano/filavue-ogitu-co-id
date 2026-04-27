<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CreateWalletTopupTokenRequest;
use App\Http\Requests\Dashboard\StoreWalletWithdrawalRequest;
use App\Http\Requests\Dashboard\SyncWalletTopupStatusRequest;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardWalletController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function createTopupToken(CreateWalletTopupTokenRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->createWalletTopupToken($customer, $request->payload());

            if ($this->isInertiaRequest($request)) {
                return back()->with('wallet', [
                    'action' => 'topup_token',
                    'message' => $result['message'] ?? 'Token topup Midtrans berhasil dibuat.',
                    'payload' => $result,
                ]);
            }

            return response()->json($result);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal membuat token topup Midtrans.');
        }
    }

    public function syncTopupStatus(
        SyncWalletTopupStatusRequest $request,
        CustomerWalletTransaction $walletTransaction
    ): JsonResponse|RedirectResponse {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->syncWalletTopupStatus($customer, (int) $walletTransaction->id);

            if ($this->isInertiaRequest($request)) {
                return back()->with('wallet', [
                    'action' => 'topup_synced',
                    'message' => $result['message'],
                    'payload' => [
                        'transaction' => $result['transaction'],
                        'balance' => $result['balance'],
                    ],
                ]);
            }

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal sinkronisasi status topup Midtrans.');
        }
    }

    public function storeWithdrawal(StoreWalletWithdrawalRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->submitWalletWithdrawal($customer, $request->payload());

            if ($this->isInertiaRequest($request)) {
                return back()->with('wallet', [
                    'action' => 'withdrawal_submitted',
                    'message' => $result['message'],
                    'payload' => [
                        'transaction' => $result['transaction'],
                        'balance' => $result['balance'],
                    ],
                ]);
            }

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal mengirim permintaan withdrawal.');
        }
    }

    private function isInertiaRequest(Request $request): bool
    {
        return $request->headers->has('X-Inertia');
    }

    private function validationFailure(
        Request $request,
        ValidationException $exception,
        string $fallbackMessage
    ): JsonResponse|RedirectResponse {
        $firstError = collect($exception->errors())->flatten()->first();
        $message = is_string($firstError) ? $firstError : $fallbackMessage;

        if ($this->isInertiaRequest($request)) {
            return back()
                ->withErrors($exception->errors())
                ->with('wallet', [
                    'action' => 'error',
                    'message' => $message,
                ]);
        }

        return response()->json([
            'message' => $message,
            'errors' => $exception->errors(),
        ], 422);
    }
}
