<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\Customer;

interface CustomerRegistrationRepositoryInterface
{
    public function findBySponsorCode(string $referralCode): ?Customer;

    /** @param array<string, mixed> $data */
    public function create(array $data): Customer;
}
