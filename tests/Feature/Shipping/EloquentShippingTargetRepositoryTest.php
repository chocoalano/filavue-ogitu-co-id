<?php

use App\Repositories\Shipping\EloquentShippingTargetRepository;
use App\Services\RajaOngkirService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('shipping_targets');

    Schema::create('shipping_targets', function (Blueprint $table): void {
        $table->id();
        $table->string('three_lc_code')->nullable();
        $table->string('country')->default('ID');
        $table->unsignedBigInteger('province_id')->nullable()->index();
        $table->string('province')->nullable();
        $table->unsignedBigInteger('city_id')->nullable()->index();
        $table->string('city')->nullable();
        $table->string('district')->nullable();
        $table->string('district_lion')->nullable();
    });

    $rajaOngkir = Mockery::mock(RajaOngkirService::class);
    $rajaOngkir->shouldReceive('getProvinces')->andReturn([]);
    app()->instance(RajaOngkirService::class, $rajaOngkir);
});

it('backfills missing region ids when loading province options', function (): void {
    DB::table('shipping_targets')->insert([
        [
            'country' => 'ID',
            'province_id' => null,
            'province' => 'Jawa Barat',
            'city_id' => null,
            'city' => 'Bandung',
            'district' => 'Coblong',
            'district_lion' => 'COBLONG',
        ],
        [
            'country' => 'ID',
            'province_id' => null,
            'province' => 'Jawa Barat',
            'city_id' => null,
            'city' => 'Bandung',
            'district' => 'Sukajadi',
            'district_lion' => 'SUKAJADI',
        ],
        [
            'country' => 'ID',
            'province_id' => null,
            'province' => 'Jawa Tengah',
            'city_id' => null,
            'city' => 'Semarang',
            'district' => 'Tembalang',
            'district_lion' => 'TEMBALANG',
        ],
    ]);

    $repository = new EloquentShippingTargetRepository;
    $provinceOptions = $repository->provinceOptions();
    $cityOptions = $repository->cityOptions();
    $districtOptions = $repository->districtOptions();

    expect($provinceOptions)->toHaveCount(2)
        ->and($cityOptions)->toHaveCount(2)
        ->and($districtOptions)->toHaveCount(3);

    $remainingMissingIds = DB::table('shipping_targets')
        ->whereNull('province_id')
        ->orWhereNull('city_id')
        ->count();

    expect($remainingMissingIds)->toBe(0);

    $bandungCity = collect($cityOptions)->first(
        fn (array $city): bool => $city['label'] === 'Bandung'
    );

    expect($bandungCity)->not->toBeNull()
        ->and((int) ($bandungCity['province_id'] ?? 0))->toBeGreaterThan(0)
        ->and((int) ($bandungCity['id'] ?? 0))->toBeGreaterThan(0);
});

it('uses rajaongkir province ordering while keeping local province ids', function (): void {
    DB::table('shipping_targets')->insert([
        [
            'country' => 'ID',
            'province_id' => 10,
            'province' => 'Jawa Barat',
            'city_id' => 110,
            'city' => 'Bandung',
            'district' => 'Coblong',
            'district_lion' => 'COBLONG',
        ],
        [
            'country' => 'ID',
            'province_id' => 20,
            'province' => 'DKI Jakarta',
            'city_id' => 120,
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'district_lion' => 'KEBAYORAN BARU',
        ],
    ]);

    $rajaOngkir = Mockery::mock(RajaOngkirService::class);
    $rajaOngkir->shouldReceive('getProvinces')->andReturn([
        ['province_id' => 31, 'province_name' => 'DKI Jakarta'],
        ['province_id' => 32, 'province_name' => 'Jawa Barat'],
    ]);
    app()->instance(RajaOngkirService::class, $rajaOngkir);

    $repository = new EloquentShippingTargetRepository;
    $provinceOptions = $repository->provinceOptions();

    expect($provinceOptions)->toHaveCount(2)
        ->and($provinceOptions[0]['label'])->toBe('DKI Jakarta')
        ->and($provinceOptions[0]['id'])->toBe(20)
        ->and($provinceOptions[1]['label'])->toBe('Jawa Barat')
        ->and($provinceOptions[1]['id'])->toBe(10);
});
