<?php

namespace App\Services\Auth;

use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthService
{
    public function __construct(
        private readonly CustomerAuthRepositoryInterface $repository
    ) {}

    public function attemptLogin(string $username, string $password, bool $remember): bool
    {
        return Auth::guard('customer')->attempt(
            ['username' => $username, 'password' => $password],
            $remember
        );
    }

    /**
     * @return array{customer:Customer,access_token:string,token_type:string}|null
     */
    public function attemptApiLogin(string $username, string $password, ?string $deviceName = null): ?array
    {
        $customer = $this->repository->findByUsername(trim($username));

        if (! $customer || ! Hash::check($password, (string) $customer->password)) {
            return null;
        }

        if ((int) ($customer->status ?? 0) !== 3) {
            return null;
        }

        $tokenName = trim((string) $deviceName) !== '' ? trim((string) $deviceName) : 'customer-api';
        $accessToken = $this->repository->createApiToken($customer, $tokenName, ['customer:api']);

        return [
            'customer' => $customer,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
        ];
    }

    public function logoutApi(Customer $customer): void
    {
        $this->repository->revokeCurrentAccessToken($customer);
    }

    public function logout(Request $request): void
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
