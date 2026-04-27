<?php

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\CustomerAddress\CustomerAddressService;
use App\Services\RajaOngkirService;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
    $this->withoutMiddleware();
});

it('returns mapped province options from rajaongkir service', function (): void {
    $this->mock(RajaOngkirService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProvinces')
            ->once()
            ->andReturn([
                ['id' => 9, 'province_name' => 'Jawa Barat'],
                ['province_id' => '31', 'province' => 'DKI Jakarta'],
                ['id' => null, 'province_name' => ''],
            ]);
    });

    $this->getJson(route('account.addresses.options.provinces'))
        ->assertOk()
        ->assertExactJson([
            ['id' => 9, 'label' => 'Jawa Barat'],
            ['id' => 31, 'label' => 'DKI Jakarta'],
        ]);
});

it('returns mapped city and district options from rajaongkir service', function (): void {
    $this->mock(RajaOngkirService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCities')
            ->once()
            ->with(32)
            ->andReturn([
                ['id' => 501, 'city_name' => 'Bandung', 'type' => 'Kota'],
                ['city_id' => 502, 'city' => 'Bandung Barat', 'type' => 'Kabupaten'],
                ['city_id' => null, 'city_name' => ''],
            ]);

        $mock->shouldReceive('getDistricts')
            ->once()
            ->with(501)
            ->andReturn([
                ['district_id' => 7001, 'district_name' => 'Coblong'],
                ['subdistrict_id' => '7002', 'subdistrict_name' => 'Sukasari', 'district_lion' => 'SUKASARI'],
                ['district_id' => null, 'district_name' => ''],
            ]);
    });

    $this->getJson(route('account.addresses.options.cities', ['province_id' => 32]))
        ->assertOk()
        ->assertExactJson([
            ['id' => 501, 'province_id' => 32, 'label' => 'Kota Bandung'],
            ['id' => 502, 'province_id' => 32, 'label' => 'Kabupaten Bandung Barat'],
        ]);

    $this->getJson(route('account.addresses.options.districts', ['city_id' => 501]))
        ->assertOk()
        ->assertExactJson([
            ['id' => 7001, 'city_id' => 501, 'label' => 'Coblong', 'district_lion' => 'Coblong'],
            ['id' => 7002, 'city_id' => 501, 'label' => 'Sukasari', 'district_lion' => 'SUKASARI'],
        ]);
});

it('passes selected region labels to service when storing address', function (): void {
    $customer = new Customer;
    $customer->forceFill([
        'id' => 9001,
        'name' => 'Customer Test',
        'email' => 'customer@example.test',
        'password' => 'secret',
    ]);
    $customer->exists = true;

    $this->actingAs($customer, 'customer');

    $this->mock(CustomerAddressService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('create')
            ->once()
            ->withArgs(function (Customer $resolvedCustomer, array $payload) use ($customer): bool {
                return (int) $resolvedCustomer->id === (int) $customer->id
                    && $payload['province_id'] === 999
                    && $payload['province_label'] === 'Jawa Barat'
                    && $payload['city_id'] === 9999
                    && $payload['city_label'] === 'Kota Bandung'
                    && $payload['district'] === 'Coblong'
                    && $payload['district_lion'] === 'Coblong';
            })
            ->andReturn(new CustomerAddress);
    });

    $this->post(route('account.addresses.store'), [
        'label' => 'Rumah',
        'is_default' => true,
        'recipient_name' => 'John Doe',
        'recipient_phone' => '081234567890',
        'address_line1' => 'Jl. Mawar No. 1',
        'address_line2' => null,
        'province_id' => 999,
        'province_label' => 'Jawa Barat',
        'city_id' => 9999,
        'city_label' => 'Kota Bandung',
        'district' => 'Coblong',
        'district_lion' => 'Coblong',
        'postal_code' => '40111',
        'country' => 'Indonesia',
        'description' => 'Dekat taman',
    ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Alamat berhasil ditambahkan.');
});
