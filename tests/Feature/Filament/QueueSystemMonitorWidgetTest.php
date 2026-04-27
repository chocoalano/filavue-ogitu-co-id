<?php

use App\Filament\Widgets\QueueSystemMonitorWidget;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.connection', 'sqlite');
    config()->set('queue.connections.database.table', 'jobs');
    config()->set('queue.failed.driver', 'database-uuids');
    config()->set('queue.failed.database', 'sqlite');
    config()->set('queue.failed.table', 'failed_jobs');

    Schema::dropIfExists('jobs');
    Schema::dropIfExists('failed_jobs');

    Schema::create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue');
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    Schema::create('failed_jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });
});

it('calculates pending and failed job counts from queue tables', function (): void {
    DB::table('jobs')->insert([
        [
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => 1,
            'created_at' => 1,
        ],
        [
            'queue' => 'emails',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => 1,
            'created_at' => 1,
        ],
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => 'f8165af1-cf2f-4a1d-ab72-cafca54c7001',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\ExampleJob'], JSON_THROW_ON_ERROR),
        'exception' => 'RuntimeException: sample',
        'failed_at' => now(),
    ]);

    $widget = app(QueueSystemMonitorWidget::class);
    $snapshot = $widget->getQueueHealthSnapshot();

    expect($snapshot['queue_driver'])->toBe('database')
        ->and($snapshot['failed_driver'])->toBe('database-uuids')
        ->and($snapshot['pending_jobs'])->toBe(2)
        ->and($snapshot['failed_jobs'])->toBe(1)
        ->and(is_bool($snapshot['worker_running']))->toBeTrue();
});

it('returns null counts when queue tables are unavailable', function (): void {
    Schema::dropIfExists('jobs');
    Schema::dropIfExists('failed_jobs');

    $widget = app(QueueSystemMonitorWidget::class);
    $snapshot = $widget->getQueueHealthSnapshot();

    expect($snapshot['pending_jobs'])->toBeNull()
        ->and($snapshot['failed_jobs'])->toBeNull();
});
