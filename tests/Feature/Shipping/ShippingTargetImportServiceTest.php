<?php

use App\Services\Shipping\ShippingTargetImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

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

it('imports shipping targets from csv file', function (): void {
    $filePath = createTempSpreadsheetPath('csv');
    $writer = new CsvWriter;

    $writer->openToFile($filePath);
    $writer->addRow(Row::fromValues([
        'three_lc_code',
        'country',
        'province',
        'city',
        'district',
        'district_lion',
    ]));
    $writer->addRow(Row::fromValues([
        'jkt',
        'Indonesia',
        'DKI Jakarta',
        'Jakarta Barat',
        'Kebon Jeruk',
        'kebon jeruk, jakarta barat',
    ]));
    $writer->close();

    $service = new ShippingTargetImportService;
    $result = $service->importFromFile($filePath, basename($filePath));

    $record = DB::table('shipping_targets')->first();

    expect($result['processed_rows'])->toBe(1)
        ->and($result['failed_rows'])->toBe(0)
        ->and($record)->not->toBeNull()
        ->and($record?->three_lc_code)->toBe('JKT')
        ->and($record?->district_lion)->toBe('KEBON JERUK, JAKARTA BARAT');

    @unlink($filePath);
});

it('imports shipping targets from xlsx file', function (): void {
    $filePath = createTempSpreadsheetPath('xlsx');
    $writer = new XlsxWriter;

    $writer->openToFile($filePath);
    $writer->addRow(Row::fromValues([
        'three_lc_code',
        'province',
        'city',
        'district',
        'district_lion',
    ]));
    $writer->addRow(Row::fromValues([
        'bdg',
        'Jawa Barat',
        'Bandung',
        'Coblong',
        'coblong, bandung',
    ]));
    $writer->close();

    $service = new ShippingTargetImportService;
    $result = $service->importFromFile($filePath, basename($filePath));

    $record = DB::table('shipping_targets')->first();

    expect($result['processed_rows'])->toBe(1)
        ->and($result['failed_rows'])->toBe(0)
        ->and($record)->not->toBeNull()
        ->and($record?->three_lc_code)->toBe('BDG')
        ->and($record?->country)->toBe('Indonesia')
        ->and($record?->district_lion)->toBe('COBLONG, BANDUNG');

    @unlink($filePath);
});

function createTempSpreadsheetPath(string $extension): string
{
    $temporaryFile = tempnam(sys_get_temp_dir(), 'shipping_targets_');

    if (! is_string($temporaryFile) || $temporaryFile === '') {
        throw new RuntimeException('Unable to create temporary file for import test.');
    }

    $path = $temporaryFile.'.'.$extension;

    if (! @rename($temporaryFile, $path)) {
        throw new RuntimeException('Unable to prepare temporary spreadsheet file path.');
    }

    return $path;
}
