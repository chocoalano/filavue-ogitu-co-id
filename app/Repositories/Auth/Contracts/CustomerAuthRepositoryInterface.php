<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\Customer;

interface CustomerAuthRepositoryInterface
{
    public function findByUsername(string $username): ?Customer;

    public function createApiToken(Customer $customer, string $tokenName, array $abilities = ['*']): string;

    public function revokeCurrentAccessToken(Customer $customer): void;
}
