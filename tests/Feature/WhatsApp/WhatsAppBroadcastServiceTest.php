<?php

use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Services\QontactService;
use App\Services\WhatsApp\WhatsAppBroadcastService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery as M;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });
});

it('creates qontak contact list csv and sends broadcast through bulk endpoint', function (): void {
    DB::table('customers')->insert([
        'id' => 5,
        'name' => 'Customer Bulk',
        'phone' => '081234567890',
        'email' => 'customer.bulk@example.test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $broadcast = new WhatsAppBroadcast;
    $broadcast->forceFill([
        'id' => 101,
        'title' => 'Promo Ramadan 2026',
        'template_id' => 'tmpl-001',
        'channel_integration_id' => 'channel-123',
        'body_params' => [
            [
                'value' => 'promo_label',
                'value_text' => 'Ramadan Special',
            ],
            [
                'value' => 'email',
                'value_text' => 'customers.email',
            ],
        ],
    ]);

    $recipient = new WhatsAppBroadcastRecipient;
    $recipient->forceFill([
        'id' => 202,
        'broadcast_id' => 101,
        'customer_id' => 5,
        'customer_name' => 'Customer Bulk',
        'normalized_phone' => '6281234567890',
    ]);

    $repository = M::mock(WhatsAppBroadcastRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with(101)
        ->andReturn($broadcast);
    $repository->shouldReceive('markProcessing')
        ->once()
        ->with($broadcast);
    $repository->shouldReceive('countRecipients')
        ->once()
        ->with(101)
        ->andReturn(1);
    $repository->shouldReceive('getPendingRecipients')
        ->once()
        ->with(101)
        ->andReturn(new Collection([$recipient]));
    $repository->shouldReceive('markRecipientSent')
        ->once()
        ->with(202, 'Queued via Qontak bulk broadcast broadcast-uuid (HTTP 201)');
    $repository->shouldReceive('summarizeRecipientStats')
        ->once()
        ->with(101)
        ->andReturn([
            'total' => 1,
            'success' => 1,
            'failed' => 0,
            'pending' => 0,
        ]);
    $repository->shouldReceive('markCompleted')
        ->once()
        ->with($broadcast, 1, 1, 0, null);

    $capturedCsvPath = null;

    $qontactService = M::mock(QontactService::class);
    $qontactService->shouldReceive('createContactListAsync')
        ->once()
        ->withArgs(function (string $name, string $filePath) use (&$capturedCsvPath): bool {
            $capturedCsvPath = $filePath;

            expect($name)->toContain('Promo Ramadan 2026')
                ->and(file_exists($filePath))->toBeTrue();

            $rows = array_map('str_getcsv', file($filePath, FILE_IGNORE_NEW_LINES) ?: []);

            expect($rows)->toHaveCount(2)
                ->and($rows[0])->toBe(['full_name', 'phone_number', 'promo_label', 'email'])
                ->and($rows[1])->toBe([
                    'Customer Bulk',
                    '6281234567890',
                    'Ramadan Special',
                    'customer.bulk@example.test',
                ]);

            return true;
        })
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [
                'data' => [
                    'id' => 'contact-list-uuid',
                ],
            ],
        ]);
    $qontactService->shouldReceive('waitUntilContactListReady')
        ->once()
        ->with('contact-list-uuid')
        ->andReturn([
            'success' => true,
            'status' => 200,
            'error' => null,
            'data' => [
                'id' => 'contact-list-uuid',
                'progress' => 'success',
                'contacts_count_success' => 1,
            ],
        ]);
    $qontactService->shouldReceive('sendWhatsAppBulk')
        ->once()
        ->with(
            'Promo Ramadan 2026',
            'tmpl-001',
            'contact-list-uuid',
            [
                ['key' => '1', 'value' => 'promo_label'],
                ['key' => '2', 'value' => 'email'],
            ],
            'channel-123',
            101,
        )
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [
                'data' => [
                    'id' => 'broadcast-uuid',
                ],
            ],
        ]);

    $service = new WhatsAppBroadcastService($repository, $qontactService);

    $service->process(101);

    expect($capturedCsvPath)->not->toBeNull()
        ->and(file_exists((string) $capturedCsvPath))->toBeFalse();
});

it('marks recipients failed when qontak bulk broadcast returns an error', function (): void {
    DB::table('customers')->insert([
        'id' => 6,
        'name' => 'Customer Failed',
        'phone' => '081298765432',
        'email' => 'customer.failed@example.test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $broadcast = new WhatsAppBroadcast;
    $broadcast->forceFill([
        'id' => 301,
        'title' => 'Promo Flash Sale',
        'template_id' => 'tmpl-002',
        'channel_integration_id' => 'channel-456',
        'body_params' => [
            [
                'value' => 'email',
                'value_text' => 'customers.email',
            ],
        ],
    ]);

    $recipient = new WhatsAppBroadcastRecipient;
    $recipient->forceFill([
        'id' => 302,
        'broadcast_id' => 301,
        'customer_id' => 6,
        'customer_name' => 'Customer Failed',
        'normalized_phone' => '6281298765432',
    ]);

    $repository = M::mock(WhatsAppBroadcastRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with(301)
        ->andReturn($broadcast);
    $repository->shouldReceive('markProcessing')
        ->once()
        ->with($broadcast);
    $repository->shouldReceive('countRecipients')
        ->once()
        ->with(301)
        ->andReturn(1);
    $repository->shouldReceive('getPendingRecipients')
        ->once()
        ->with(301)
        ->andReturn(new Collection([$recipient]));
    $repository->shouldReceive('markRecipientFailed')
        ->once()
        ->with(302, 'Too many requests');
    $repository->shouldReceive('summarizeRecipientStats')
        ->once()
        ->with(301)
        ->andReturn([
            'total' => 1,
            'success' => 0,
            'failed' => 1,
            'pending' => 0,
        ]);
    $repository->shouldReceive('markCompleted')
        ->once()
        ->with($broadcast, 1, 0, 1, 'Too many requests');

    $capturedCsvPath = null;

    $qontactService = M::mock(QontactService::class);
    $qontactService->shouldReceive('createContactListAsync')
        ->once()
        ->withArgs(function (string $name, string $filePath) use (&$capturedCsvPath): bool {
            $capturedCsvPath = $filePath;

            expect($name)->toContain('Promo Flash Sale')
                ->and(file_exists($filePath))->toBeTrue();

            return true;
        })
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [
                'data' => [
                    'id' => 'contact-list-failed',
                ],
            ],
        ]);
    $qontactService->shouldReceive('waitUntilContactListReady')
        ->once()
        ->with('contact-list-failed')
        ->andReturn([
            'success' => true,
            'status' => 200,
            'error' => null,
            'data' => [
                'id' => 'contact-list-failed',
                'progress' => 'success',
                'contacts_count_success' => 1,
            ],
        ]);
    $qontactService->shouldReceive('sendWhatsAppBulk')
        ->once()
        ->with(
            'Promo Flash Sale',
            'tmpl-002',
            'contact-list-failed',
            [
                ['key' => '1', 'value' => 'email'],
            ],
            'channel-456',
            301,
        )
        ->andReturn([
            'success' => false,
            'status' => 429,
            'error' => 'Too many requests',
            'body' => [],
        ]);

    $service = new WhatsAppBroadcastService($repository, $qontactService);

    $service->process(301);

    expect($capturedCsvPath)->not->toBeNull()
        ->and(file_exists((string) $capturedCsvPath))->toBeFalse();
});
