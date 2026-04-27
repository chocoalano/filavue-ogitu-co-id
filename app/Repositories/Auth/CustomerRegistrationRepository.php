<?php

namespace App\Repositories\Auth;

use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerRegistrationRepositoryInterface;

class CustomerRegistrationRepository implements CustomerRegistrationRepositoryInterface
{
    public function findBySponsorCode(string $referralCode): ?Customer
    {
        return Customer::query()->where('ref_code', $referralCode)->first();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }
}
