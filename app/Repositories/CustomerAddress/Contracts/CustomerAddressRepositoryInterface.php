<?php

namespace App\Repositories\CustomerAddress\Contracts;

use App\Models\CustomerAddress;
use Illuminate\Support\Collection;

interface CustomerAddressRepositoryInterface
{
    /**
     * @return Collection<int, CustomerAddress>
     */
    public function getByCustomerId(int $customerId): Collection;

    public function findByCustomerIdAndAddressId(int $customerId, int $addressId): ?CustomerAddress;

    public function countByCustomerId(int $customerId): int;

    public function hasDefaultAddress(int $customerId): bool;

    public function clearDefaultAddress(int $customerId): void;

    /**
     * @param array<string, mixed> $attributes
     */
    public function createForCustomer(int $customerId, array $attributes): CustomerAddress;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(CustomerAddress $address, array $attributes): void;

    public function setDefault(CustomerAddress $address, bool $isDefault = true): void;

    public function delete(CustomerAddress $address): void;

    public function getLatestByCustomerId(int $customerId): ?CustomerAddress;
}
