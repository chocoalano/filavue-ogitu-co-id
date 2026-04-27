<?php

namespace App\Services\Auth;

use App\Models\Customer;
use Illuminate\Http\Request;

class ReferralContextService
{
    /**
     * @return array{referralCode:?string, referralUsername:?string}
     */
    public function captureFromRequest(Request $request): array
    {
        if ($request->has('referral_code')) {
            return $this->syncSession(
                $request,
                $this->resolveCustomerFromReferralCode((string) $request->query('referral_code'))
            );
        }

        if ($request->has('username')) {
            return $this->syncSession(
                $request,
                $this->resolveCustomerFromUsername((string) $request->query('username'))
            );
        }

        return $this->sessionPayload($request);
    }

    public function resolveCustomerFromReferralCode(?string $referralCode): ?Customer
    {
        $normalizedReferralCode = trim((string) $referralCode);

        if ($normalizedReferralCode === '') {
            return null;
        }

        return Customer::query()
            ->where('ref_code', $normalizedReferralCode)
            ->first();
    }

    public function resolveCustomerFromUsername(?string $username): ?Customer
    {
        $rawUsername = trim((string) $username);
        $normalizedUsername = mb_strtolower($rawUsername);

        if ($normalizedUsername === '') {
            return null;
        }

        return Customer::query()
            ->where('username', $normalizedUsername)
            ->orWhere('username', $rawUsername)
            ->first();
    }

    /**
     * @return array{referralCode:?string, referralUsername:?string}
     */
    public function sessionPayload(Request $request): array
    {
        if (! $request->hasSession()) {
            return [
                'referralCode' => null,
                'referralUsername' => null,
            ];
        }

        $payload = [
            'referralCode' => $this->normalizeString($request->session()->get('referral_code')),
            'referralUsername' => $this->normalizeString($request->session()->get('referral_username')),
        ];

        if ($payload['referralCode'] !== null && $payload['referralUsername'] === null) {
            return $this->syncSession(
                $request,
                $this->resolveCustomerFromReferralCode($payload['referralCode'])
            );
        }

        if ($payload['referralUsername'] !== null && $payload['referralCode'] === null) {
            return $this->syncSession(
                $request,
                $this->resolveCustomerFromUsername($payload['referralUsername'])
            );
        }

        return $payload;
    }

    /**
     * @return array{referralCode:?string, referralUsername:?string}
     */
    public function syncSession(Request $request, ?Customer $customer): array
    {
        $payload = [
            'referralCode' => $this->normalizeString($customer?->ref_code),
            'referralUsername' => $this->normalizeString($customer?->username),
        ];

        if (! $request->hasSession()) {
            return $payload;
        }

        if ($payload['referralCode'] !== null) {
            $request->session()->put('referral_code', $payload['referralCode']);
        } else {
            $request->session()->forget('referral_code');
        }

        if ($payload['referralUsername'] !== null) {
            $request->session()->put('referral_username', $payload['referralUsername']);
        } else {
            $request->session()->forget('referral_username');
        }

        return $payload;
    }

    private function normalizeString(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
