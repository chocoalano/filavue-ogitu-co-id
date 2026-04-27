<?php

use App\Filament\Resources\ShippingTargets\Schemas\ShippingTargetForm;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    Cache::flush();
});

it('maps province and city options from rajaongkir for the shipping target form', function (): void {
    $this->mock(RajaOngkirService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProvinces')
            ->once()
            ->andReturn([
                ['id' => 9, 'province_name' => 'Jawa Barat'],
                ['province_id' => '31', 'province' => 'DKI Jakarta'],
                ['id' => null, 'province_name' => ''],
            ]);

        $mock->shouldReceive('getCities')
            ->once()
            ->with(32)
            ->andReturn([
                ['id' => 501, 'city_name' => 'Bandung', 'type' => 'Kota'],
                ['city_id' => 502, 'city' => 'Bandung Barat', 'type' => 'Kabupaten'],
                ['city_id' => null, 'city_name' => ''],
            ]);
    });

    $provinceOptions = invokePrivateStatic(ShippingTargetForm::class, 'provinceOptions');
    $cityOptions = invokePrivateStatic(ShippingTargetForm::class, 'cityOptions', [32]);

    expect($provinceOptions)->toBe([
        '9' => 'JAWA BARAT',
        '31' => 'DKI JAKARTA',
    ])->and($cityOptions)->toBe([
        '501' => 'KOTA BANDUNG',
        '502' => 'KABUPATEN BANDUNG BARAT',
    ]);
});

it('maps district options and fills district lion labels from rajaongkir rows', function (): void {
    $this->mock(RajaOngkirService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getDistricts')
            ->twice()
            ->with(501)
            ->andReturn([
                ['district_id' => 7001, 'district_name' => 'Coblong'],
                ['subdistrict_id' => '7002', 'subdistrict_name' => 'Sukasari', 'district_lion' => 'SUKASARI'],
                ['district_id' => null, 'district_name' => ''],
            ]);
    });

    $districtOptions = invokePrivateStatic(ShippingTargetForm::class, 'districtOptions', [501]);
    $districtLionOptions = invokePrivateStatic(ShippingTargetForm::class, 'districtLionOptions', [501, 'KOTA BANDUNG']);

    expect($districtOptions)->toBe([
        'COBLONG' => 'COBLONG',
        'SUKASARI' => 'SUKASARI',
    ])->and($districtLionOptions)->toBe([
        'COBLONG' => 'COBLONG, BANDUNG',
        'SUKASARI' => 'SUKASARI',
    ]);
});

it('stores the shipping target form as rajaongkir-powered cascading selects', function (): void {
    $source = file_get_contents(app_path('Filament/Resources/ShippingTargets/Schemas/ShippingTargetForm.php'));

    expect($source)->toBeString()
        ->and($source)->toContain("Select::make('province_id')")
        ->and($source)->toContain("Select::make('city_id')")
        ->and($source)->toContain("Select::make('district')")
        ->and($source)->toContain('self::provinceOptions()')
        ->and($source)->toContain("self::cityOptions(\$get('province_id'))")
        ->and($source)->toContain("self::districtOptions(\$get('city_id'))")
        ->and($source)->toContain("self::districtLionOptions(\$get('city_id'), \$get('city'))");
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
