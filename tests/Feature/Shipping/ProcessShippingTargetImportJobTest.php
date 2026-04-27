<?php

use App\Jobs\ProcessShippingTargetImportJob;
use App\Services\Shipping\ShippingTargetImportService;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
});

it('imports shipping target file using service and deletes temporary file', function (): void {
    Storage::fake('local');

    $storedFilePath = 'imports/shipping-targets/targets.csv';
    Storage::disk('local')->put($storedFilePath, 'dummy');
    $absoluteFilePath = Storage::disk('local')->path($storedFilePath);

    $importService = $this->mock(ShippingTargetImportService::class, function (MockInterface $mock) use ($absoluteFilePath): void {
        $mock->shouldReceive('importFromFile')
            ->once()
            ->with($absoluteFilePath, 'targets.csv')
            ->andReturn([
                'processed_rows' => 10,
                'failed_rows' => 2,
            ]);
    });

    $job = new ProcessShippingTargetImportJob(
        storedFilePath: $storedFilePath,
        initiatorUserId: 1,
        originalFileName: 'targets.csv',
    );

    $job->handle($importService);

    expect(Storage::disk('local')->exists($storedFilePath))->toBeFalse();
});

it('does not call import service when stored file is missing', function (): void {
    Storage::fake('local');

    $importService = $this->mock(ShippingTargetImportService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('importFromFile');
    });

    $job = new ProcessShippingTargetImportJob(
        storedFilePath: 'imports/shipping-targets/missing.csv',
        initiatorUserId: 1,
        originalFileName: 'missing.csv',
    );

    $job->handle($importService);

    expect(Storage::disk('local')->exists('imports/shipping-targets/missing.csv'))->toBeFalse();
});

it('deletes temporary file when import service throws exception', function (): void {
    Storage::fake('local');

    $storedFilePath = 'imports/shipping-targets/broken.csv';
    Storage::disk('local')->put($storedFilePath, 'dummy');
    $absoluteFilePath = Storage::disk('local')->path($storedFilePath);

    $importService = $this->mock(ShippingTargetImportService::class, function (MockInterface $mock) use ($absoluteFilePath): void {
        $mock->shouldReceive('importFromFile')
            ->once()
            ->with($absoluteFilePath, 'broken.csv')
            ->andThrow(new RuntimeException('import failed'));
    });

    $job = new ProcessShippingTargetImportJob(
        storedFilePath: $storedFilePath,
        initiatorUserId: 1,
        originalFileName: 'broken.csv',
    );

    expect(fn () => $job->handle($importService))->toThrow(RuntimeException::class, 'import failed');
    expect(Storage::disk('local')->exists($storedFilePath))->toBeFalse();
});

it('uses database connection and default queue for shipping target import job', function (): void {
    $job = new ProcessShippingTargetImportJob(
        storedFilePath: 'imports/shipping-targets/targets.csv',
        initiatorUserId: 1,
        originalFileName: 'targets.csv',
    );

    expect($job->connection)->toBe('database')
        ->and($job->queue)->toBe('default');
});
