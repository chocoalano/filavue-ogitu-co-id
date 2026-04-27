<?php

use App\Filament\Widgets\QueueListWidget;
use App\Models\FailedJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('queue.failed.driver', 'database-uuids');
    config()->set('queue.failed.database', 'sqlite');
    config()->set('queue.failed.table', 'failed_jobs');

    Schema::dropIfExists('failed_jobs');
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

it('can be viewed when failed queue table exists', function (): void {
    expect(QueueListWidget::canView())->toBeTrue();
});

it('retries selected failed jobs using artisan queue retry command', function (): void {
    seedFailedJob(1, '1b93c0e8-22de-4db8-8f46-a6f8a19ec100');
    seedFailedJob(2, '1b93c0e8-22de-4db8-8f46-a6f8a19ec200');

    $records = FailedJob::query()->whereIn('id', [1, 2])->get();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:retry', ['id' => ['1', '2']])
        ->andReturn(0);

    $widget = app(QueueListWidget::class);
    $retried = invokePrivate($widget, 'retryFailedJobs', [$records]);

    expect($retried)->toBe(2);
});

it('deletes selected failed jobs from queue list', function (): void {
    seedFailedJob(1, 'f1e2d3c4-22de-4db8-8f46-a6f8a19ec101');
    seedFailedJob(2, 'f1e2d3c4-22de-4db8-8f46-a6f8a19ec202');

    $records = FailedJob::query()->whereIn('id', [1])->get();

    $widget = app(QueueListWidget::class);
    $deleted = invokePrivate($widget, 'deleteFailedJobs', [$records]);

    expect($deleted)->toBe(1)
        ->and((int) FailedJob::query()->count())->toBe(1);
});

it('deletes all failed jobs from queue list', function (): void {
    seedFailedJob(1, 'e1e2d3c4-22de-4db8-8f46-a6f8a19ec101');
    seedFailedJob(2, 'e1e2d3c4-22de-4db8-8f46-a6f8a19ec202');

    $widget = app(QueueListWidget::class);
    $deleted = invokePrivate($widget, 'deleteAllFailedJobs');

    expect($deleted)->toBe(2)
        ->and((int) FailedJob::query()->count())->toBe(0);
});

it('resolves readable job name from failed payload', function (): void {
    $payload = json_encode([
        'displayName' => 'App\\Jobs\\SendWithdrawalApprovedWhatsAppJob',
    ], JSON_THROW_ON_ERROR);

    $widget = app(QueueListWidget::class);
    $jobName = invokePrivate($widget, 'resolveJobName', [$payload]);

    expect($jobName)->toBe('SendWithdrawalApprovedWhatsAppJob');
});

function seedFailedJob(int $id, string $uuid): void
{
    DB::table('failed_jobs')->insert([
        'id' => $id,
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\SendWithdrawalApprovedWhatsAppJob',
        ], JSON_THROW_ON_ERROR),
        'exception' => 'RuntimeException: queue failed',
        'failed_at' => now(),
    ]);
}

function invokePrivate(object $instance, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($instance, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($instance, $arguments);
}
