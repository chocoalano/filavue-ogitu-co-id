<?php

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Services\CustomerAddress\CustomerAddressService;
use Mockery\MockInterface;

it('stores district lion as uppercase district and city label', function (): void {
    $repository = $this->mock(CustomerAddressRepositoryInterface::class, function (MockInterface $mock): void {
        $mock->shouldReceive('countByCustomerId')
            ->once()
            ->with(10)
            ->andReturn(0);

        $mock->shouldReceive('clearDefaultAddress')
            ->once()
            ->with(10);

        $mock->shouldReceive('createForCustomer')
            ->once()
            ->withArgs(function (int $customerId, array $attributes): bool {
                return $customerId === 10
                    && ($attributes['district'] ?? null) === 'Kebon Jeruk'
                    && ($attributes['city_label'] ?? null) === 'Kota Jakarta Barat'
                    && ($attributes['district_lion'] ?? null) === 'KEBON JERUK, JAKARTA BARAT'
                    && ($attributes['is_default'] ?? null) === true;
            })
            ->andReturn(new CustomerAddress);

        $mock->shouldReceive('hasDefaultAddress')
            ->once()
            ->with(10)
            ->andReturn(true);
    });

    $customer = new Customer;
    $customer->id = 10;

    $service = new CustomerAddressService($repository);

    $storedAddress = $service->create($customer, [
        'label' => 'Rumah',
        'is_default' => true,
        'recipient_name' => 'John Doe',
        'recipient_phone' => '081234567890',
        'address_line1' => 'Jl. Mawar No. 1',
        'province_label' => 'DKI Jakarta',
        'province_id' => 31,
        'city_label' => 'Kota Jakarta Barat',
        'city_id' => 3174,
        'district' => 'Kebon Jeruk',
    ]);

    expect($storedAddress)->toBeInstanceOf(CustomerAddress::class);
});
