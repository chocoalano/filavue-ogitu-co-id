<?php

namespace App\Repositories\CustomerAddress;

use App\Models\CustomerAddress;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCustomerAddressRepository implements CustomerAddressRepositoryInterface
{
    public function getByCustomerId(int $customerId): Collection
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
    }

    public function findByCustomerIdAndAddressId(int $customerId, int $addressId): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->whereKey($addressId)
            ->first();
    }

    public function countByCustomerId(int $customerId): int
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->count();
    }

    public function hasDefaultAddress(int $customerId): bool
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('is_default', true)
            ->exists();
    }

    public function clearDefaultAddress(int $customerId): void
    {
        CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    public function createForCustomer(int $customerId, array $attributes): CustomerAddress
    {
        return CustomerAddress::query()->create([
            ...$attributes,
            'customer_id' => $customerId,
        ]);
    }

    public function update(CustomerAddress $address, array $attributes): void
    {
        $address->update($attributes);
    }

    public function setDefault(CustomerAddress $address, bool $isDefault = true): void
    {
        $address->update(['is_default' => $isDefault]);
    }

    public function delete(CustomerAddress $address): void
    {
        $address->delete();
    }

    public function getLatestByCustomerId(int $customerId): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }
}
