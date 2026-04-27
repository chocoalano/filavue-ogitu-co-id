<?php

use App\Filament\Resources\ShippingTargets\Imports\ShippingTargetImporter;
use App\Models\ShippingTarget;
use Filament\Actions\Imports\Models\Import;
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
        $table->string('three_lc_code')->unique();
        $table->string('country');
        $table->unsignedBigInteger('province_id')->nullable()->index();
        $table->string('province')->nullable();
        $table->unsignedBigInteger('city_id')->nullable()->index();
        $table->string('city')->nullable();
        $table->string('district')->nullable();
        $table->string('district_lion')->nullable();
        $table->timestamps();
    });
});

it('imports shipping target row with default country and normalized district lion', function (): void {
    $importer = new ShippingTargetImporter(
        import: new Import,
        columnMap: [
            'three_lc_code' => 'Kode',
            'province' => 'Provinsi',
            'city' => 'Kota',
            'district' => 'Kecamatan',
            'district_lion' => 'District Lion',
        ],
        options: [],
    );

    $importer([
        'Kode' => ' jkt ',
        'Provinsi' => 'DKI Jakarta',
        'Kota' => 'Jakarta Barat',
        'Kecamatan' => 'Kebon Jeruk',
        'District Lion' => 'kebon jeruk, jakarta barat',
    ]);

    $record = ShippingTarget::query()->first();

    expect($record)->not->toBeNull()
        ->and($record?->three_lc_code)->toBe('JKT')
        ->and($record?->country)->toBe('Indonesia')
        ->and($record?->district_lion)->toBe('KEBON JERUK, JAKARTA BARAT');
});

it('upserts shipping target row by three lc code', function (): void {
    $importer = new ShippingTargetImporter(
        import: new Import,
        columnMap: [
            'three_lc_code' => '3LC Code',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'district_lion' => 'District Lion',
        ],
        options: [],
    );

    $importer([
        '3LC Code' => 'bdg',
        'Country' => 'Indonesia',
        'Province' => 'Jawa Barat',
        'City' => 'Bandung',
        'District' => 'Coblong',
        'District Lion' => 'coblong, bandung',
    ]);

    $importer([
        '3LC Code' => ' BDG ',
        'Country' => 'Indonesia',
        'Province' => 'Jawa Barat',
        'City' => 'Bandung',
        'District' => 'Sukajadi',
        'District Lion' => 'sukajadi, bandung',
    ]);

    $record = ShippingTarget::query()->firstWhere('three_lc_code', 'BDG');

    expect(ShippingTarget::query()->count())->toBe(1)
        ->and($record)->not->toBeNull()
        ->and($record?->district)->toBe('Sukajadi')
        ->and($record?->district_lion)->toBe('SUKAJADI, BANDUNG');
});
