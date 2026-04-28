<?php

use App\Models\JneDestination;
use App\Repositories\Shipping\EloquentShippingTargetRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Cache::flush();

    Schema::dropIfExists('jne_destinations');

    Schema::create('jne_destinations', function (Blueprint $table): void {
        $table->id();
        $table->string('country_name')->nullable();
        $table->string('province_name')->nullable()->index();
        $table->string('city_name')->nullable()->index();
        $table->string('district_name')->nullable()->index();
        $table->string('subdistrict_name')->nullable()->index();
        $table->string('zip_code', 20)->nullable()->index();
        $table->string('tariff_code', 50)->nullable()->index();
        $table->timestamps();
    });

    DB::table('jne_destinations')->insert([
        [
            'country_name' => 'INDONESIA',
            'province_name' => 'JAWA BARAT',
            'city_name' => 'BANDUNG',
            'district_name' => 'COBLONG',
            'subdistrict_name' => 'DAGO',
            'zip_code' => '40135',
            'tariff_code' => 'BDO10000',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'country_name' => 'INDONESIA',
            'province_name' => 'JAWA BARAT',
            'city_name' => 'BANDUNG',
            'district_name' => 'SUKAJADI',
            'subdistrict_name' => 'SUKAJADI',
            'zip_code' => '40162',
            'tariff_code' => 'BDO10000',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'country_name' => 'INDONESIA',
            'province_name' => 'JAWA TENGAH',
            'city_name' => 'SEMARANG',
            'district_name' => 'TEMBALANG',
            'subdistrict_name' => 'TEMBALANG',
            'zip_code' => '50275',
            'tariff_code' => 'SRG10000',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'country_name' => 'INDONESIA',
            'province_name' => 'DKI JAKARTA',
            'city_name' => 'JAKARTA SELATAN',
            'district_name' => 'KEBAYORAN BARU',
            'subdistrict_name' => 'GUNUNG',
            'zip_code' => '12120',
            'tariff_code' => 'CGK10000',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

afterEach(function (): void {
    Cache::flush();
});

it('loads province, city, and district options from jne destinations', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $provinceOptions = $repository->provinceOptions();
    $cityOptions = $repository->cityOptions();
    $districtOptions = $repository->districtOptions();

    expect($provinceOptions)->toHaveCount(3)
        ->and($cityOptions)->toHaveCount(3)
        ->and($districtOptions)->toHaveCount(4);

    expect(collect($provinceOptions)->pluck('label')->all())
        ->toContain('DKI JAKARTA')
        ->toContain('JAWA BARAT')
        ->toContain('JAWA TENGAH');

    expect(collect($cityOptions)->pluck('label')->all())
        ->toContain('BANDUNG')
        ->toContain('SEMARANG')
        ->toContain('JAKARTA SELATAN');

    expect(collect($districtOptions)->pluck('label')->all())
        ->toContain('COBLONG')
        ->toContain('SUKAJADI')
        ->toContain('TEMBALANG')
        ->toContain('KEBAYORAN BARU');
});

it('returns district options by city id from jne destinations', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $bandungCityId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->where('city_name', 'BANDUNG')
        ->min('id');

    $districtOptions = $repository->districtOptionsByCityId((int) $bandungCityId);

    expect($districtOptions)->toHaveCount(2);

    expect(collect($districtOptions)->pluck('label')->all())
        ->toContain('COBLONG')
        ->toContain('SUKAJADI');

    expect(collect($districtOptions)->pluck('district_lion')->all())
        ->toContain('COBLONG')
        ->toContain('SUKAJADI');

    foreach ($districtOptions as $district) {
        expect($district)
            ->toHaveKeys(['id', 'city_id', 'label', 'district_lion'])
            ->and($district['city_id'])->toBe((int) $bandungCityId)
            ->and($district['id'])->toBeGreaterThan(0);
    }
});

it('returns cities by province name from jne destinations', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $cities = $repository->citiesByProvince('Jawa Barat');

    expect($cities)->toHaveCount(1)
        ->and($cities[0])->toBe('BANDUNG');
});

it('returns districts by province and city name from jne destinations', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $districts = $repository->districtsByProvinceAndCity('Jawa Barat', 'Bandung');

    expect($districts)->toHaveCount(2);

    expect($districts)->toContain([
        'label' => 'COBLONG',
        'value' => 'COBLONG',
    ]);

    expect($districts)->toContain([
        'label' => 'SUKAJADI',
        'value' => 'SUKAJADI',
    ]);
});

it('finds city by jne region ids', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $provinceId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->min('id');

    $cityId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->where('city_name', 'BANDUNG')
        ->min('id');

    $city = $repository->findCityByIds((int) $provinceId, (int) $cityId);

    expect($city)->not->toBeNull()
        ->and($city['province_id'])->toBe((int) $provinceId)
        ->and($city['province_label'])->toBe('JAWA BARAT')
        ->and($city['city_id'])->toBe((int) $cityId)
        ->and($city['city_label'])->toBe('BANDUNG');
});

it('finds district by jne region ids', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $provinceId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->min('id');

    $cityId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->where('city_name', 'BANDUNG')
        ->min('id');

    $district = $repository->findDistrictByRegionIds(
        provinceId: (int) $provinceId,
        cityId: (int) $cityId,
        district: 'Coblong',
    );

    expect($district)->not->toBeNull()
        ->and($district['district'])->toBe('COBLONG')
        ->and($district['district_lion'])->toBe('COBLONG');
});

it('finds district lion from jne destinations', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $districtLion = $repository->findDistrictLion(
        province: 'Jawa Barat',
        city: 'Bandung',
        district: 'Coblong',
    );

    expect($districtLion)->toBe('COBLONG');
});

it('returns null when city ids are not from the same province', function (): void {
    $repository = new EloquentShippingTargetRepository();

    $jawaBaratProvinceId = JneDestination::query()
        ->where('province_name', 'JAWA BARAT')
        ->min('id');

    $semarangCityId = JneDestination::query()
        ->where('province_name', 'JAWA TENGAH')
        ->where('city_name', 'SEMARANG')
        ->min('id');

    $city = $repository->findCityByIds(
        provinceId: (int) $jawaBaratProvinceId,
        cityId: (int) $semarangCityId,
    );

    expect($city)->toBeNull();
});
