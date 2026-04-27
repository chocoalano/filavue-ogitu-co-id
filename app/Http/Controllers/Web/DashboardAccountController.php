<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Models\Customer;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardAccountController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function update(UpdateAccountProfileRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $this->dashboardService->updateAccountProfile($customer, $request->payload());

            if ($this->isInertiaRequest($request)) {
                return back()->with('account', [
                    'action' => 'profile_updated',
                    'message' => 'Profil akun berhasil diperbarui.',
                ]);
            }

            return response()->json([
                'message' => 'Profil akun berhasil diperbarui.',
            ]);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal memperbarui profil akun.');
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
                ->with('account', [
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
