<?php

use App\Filament\Resources\CommodityCodes\Imports\CommodityCodeImporter;
use App\Models\CommodityCode;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('commodity_codes');

    Schema::create('commodity_codes', function (Blueprint $table): void {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->boolean('dangerous_good')->default(true);
        $table->boolean('is_quarantine')->default(true);
        $table->timestamps();
    });
});

it('imports commodity code row with normalized values', function (): void {
    $importer = new CommodityCodeImporter(
        import: new Import,
        columnMap: [
            'code' => 'Kode',
            'name' => 'Nama',
            'dangerous_good' => 'Barang Berbahaya',
            'is_quarantine' => 'Karantina',
        ],
        options: [],
    );

    $importer([
        'Kode' => ' HS-001 ',
        'Nama' => 'Produk Berisiko',
        'Barang Berbahaya' => 'ya',
        'Karantina' => 'tidak',
    ]);

    $record = CommodityCode::query()->first();

    expect($record)->not->toBeNull()
        ->and($record?->code)->toBe('HS-001')
        ->and($record?->name)->toBe('Produk Berisiko')
        ->and((bool) $record?->dangerous_good)->toBeTrue()
        ->and((bool) $record?->is_quarantine)->toBeFalse();
});

it('upserts commodity code row by code', function (): void {
    $importer = new CommodityCodeImporter(
        import: new Import,
        columnMap: [
            'code' => 'code',
            'name' => 'name',
        ],
        options: [],
    );

    $importer([
        'code' => 'ABC-123',
        'name' => 'Nama Awal',
    ]);

    $importer([
        'code' => ' ABC-123 ',
        'name' => 'Nama Baru',
    ]);

    $record = CommodityCode::query()->firstWhere('code', 'ABC-123');

    expect(CommodityCode::query()->count())->toBe(1)
        ->and($record)->not->toBeNull()
        ->and($record?->name)->toBe('Nama Baru')
        ->and((bool) $record?->dangerous_good)->toBeTrue()
        ->and((bool) $record?->is_quarantine)->toBeTrue();
});

it('uses database connection and default queue for import jobs', function (): void {
    $importer = new CommodityCodeImporter(
        import: new Import,
        columnMap: [],
        options: [],
    );

    expect($importer->getJobConnection())->toBe('database')
        ->and($importer->getJobQueue())->toBe('default');
});
