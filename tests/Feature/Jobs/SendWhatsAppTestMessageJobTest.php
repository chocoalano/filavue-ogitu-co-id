<?php

use App\Jobs\SendWhatsAppTestMessageJob;
use App\Services\QontactService;
use App\Support\QontakWhatsAppSettings;

beforeEach(function (): void {
    QontakWhatsAppSettings::forgetCache();
});

it('sends test whatsapp message through qontact service', function (): void {
    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldReceive('sendWhatsAppWithResultFromParams')
        ->once()
        ->with(
            'Tester Admin',
            '6281234567890',
            'tmpl-001',
            ['Tester Admin', 'Pesan test queue'],
            'id',
            'https://ogitu.id/logo.png',
        )
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [],
        ]);

    $job = new SendWhatsAppTestMessageJob(
        'Tester Admin',
        '6281234567890',
        'tmpl-001',
        'Pesan test queue',
    );

    $job->handle($qontact);

    expect($job->queue)->toBe('whatsapp');
});

it('throws runtime exception when qontact send returns failed result', function (): void {
    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldReceive('sendWhatsAppWithResultFromParams')
        ->once()
        ->andReturn([
            'success' => false,
            'status' => 400,
            'error' => 'Gateway timeout',
            'body' => [],
        ]);

    $job = new SendWhatsAppTestMessageJob(
        'Tester Admin',
        '6281234567890',
        'tmpl-001',
        'Pesan test queue',
    );

    expect(fn (): mixed => $job->handle($qontact))
        ->toThrow(RuntimeException::class, 'Gateway timeout');
});

it('throws runtime exception when template id is empty', function (): void {
    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldNotReceive('sendWhatsAppWithResultFromParams');

    $job = new SendWhatsAppTestMessageJob(
        'Tester Admin',
        '6281234567890',
        '',
        'Pesan test queue',
    );

    expect(fn (): mixed => $job->handle($qontact))
        ->toThrow(RuntimeException::class, 'Template ID Qontak wajib diisi untuk test message.');
});

it('falls back to png header when configured header uses unsupported extension', function (): void {
    config()->set('services.qontak.broadcast_header_image_url', '');
    config()->set('services.qontak.wd_approved_header_image_url', 'https://ogitu.id/assets/logo-ogitu.webp');

    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldReceive('sendWhatsAppWithResultFromParams')
        ->once()
        ->with(
            'Tester Admin',
            '6281234567890',
            'tmpl-001',
            ['Tester Admin', 'Pesan test queue'],
            'id',
            'https://ogitu.id/logo.png',
        )
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [],
        ]);

    $job = new SendWhatsAppTestMessageJob(
        'Tester Admin',
        '6281234567890',
        'tmpl-001',
        'Pesan test queue',
    );

    $job->handle($qontact);

    expect(true)->toBeTrue();
});
