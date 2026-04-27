<?php

use App\Models\Setting;
use App\Support\QontakWhatsAppSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('cache.default', 'array');
    Cache::flush();

    Schema::dropIfExists('settings');

    Schema::create('settings', function (Blueprint $table): void {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->string('type')->default('text');
        $table->string('group')->default('general');
        $table->timestamps();
    });
});

it('returns default qontak whatsapp settings from config when storage is empty', function (): void {
    config()->set('services.qontak.base_url', 'https://service-chat.qontak.com/api/open/v1');
    config()->set('services.qontak.api_token', 'env-token');
    config()->set('services.qontak.channel_integration_id', 'env-channel');
    config()->set('services.qontak.timeout', 45);
    config()->set('services.qontak.wd_approved_template_id', 'env-approved-template');
    config()->set('services.qontak.wd_approved_header_image_url', 'https://cdn.example.com/approved.png');
    config()->set('services.qontak.wd_rejected_template_id', 'env-rejected-template');
    config()->set('services.qontak.broadcast_template_id', 'env-broadcast-template');
    config()->set('services.qontak.broadcast_header_image_url', 'https://cdn.example.com/broadcast.png');
    config()->set('services.qontak.bulk_retry_attempts', 3);
    config()->set('services.qontak.bulk_retry_buffer_seconds', 5);

    $state = QontakWhatsAppSettings::getState();

    expect($state['connection']['api_token'])->toBe('env-token')
        ->and($state['connection']['channel_integration_id'])->toBe('env-channel')
        ->and($state['connection']['timeout'])->toBe(45)
        ->and($state['notifications']['withdrawal_approved']['template_id'])->toBe('env-approved-template')
        ->and($state['notifications']['withdrawal_approved']['parameters'])->toBe([])
        ->and($state['notifications']['withdrawal_rejected']['template_id'])->toBe('env-rejected-template')
        ->and($state['notifications']['withdrawal_rejected']['parameters'])->toBe([])
        ->and($state['broadcast']['default_template_id'])->toBe('env-broadcast-template')
        ->and($state['broadcast']['header_image_url'])->toBe('https://cdn.example.com/broadcast.png')
        ->and($state['broadcast']['bulk_retry_attempts'])->toBe(3)
        ->and($state['broadcast']['bulk_retry_buffer_seconds'])->toBe(5);
});

it('writes qontak whatsapp settings, encrypts api token, and preserves token when blank on update', function (): void {
    QontakWhatsAppSettings::writeState([
        'connection' => [
            'base_url' => 'https://qontak.example.com/api/open/v1/',
            'api_token' => 'secret-token',
            'channel_integration_id' => 'channel-001',
            'timeout' => 60,
        ],
        'notifications' => [
            'withdrawal_approved' => [
                'enabled' => false,
                'template_id' => 'approved-template',
                'header_image_url' => 'https://cdn.example.com/approved.jpg',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'full_name',
                        'source_table' => 'customers',
                        'source_column' => 'name',
                    ],
                ],
            ],
            'withdrawal_rejected' => [
                'enabled' => true,
                'template_id' => 'rejected-template',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'amount',
                        'source_table' => 'computed',
                        'source_column' => 'amount_formatted',
                    ],
                ],
            ],
        ],
        'broadcast' => [
            'default_template_id' => 'broadcast-template',
            'header_image_url' => 'https://cdn.example.com/broadcast.png',
            'bulk_retry_attempts' => 4,
            'bulk_retry_buffer_seconds' => 8,
        ],
    ]);

    $storedToken = Setting::query()->where('key', 'qontak.connection.api_token')->value('value');

    expect($storedToken)->toBeString()
        ->and($storedToken)->not->toBe('secret-token');

    Cache::flush();

    expect(QontakWhatsAppSettings::getState()['connection']['api_token'])->toBe('secret-token')
        ->and(QontakWhatsAppSettings::notificationEnabled('withdrawal_approved'))->toBeFalse()
        ->and(QontakWhatsAppSettings::get('notifications.withdrawal_approved.parameters.0.source_column'))->toBe('name')
        ->and(QontakWhatsAppSettings::get('broadcast.bulk_retry_attempts'))->toBe(4);

    QontakWhatsAppSettings::writeState([
        'connection' => [
            'base_url' => 'https://qontak.example.com/api/open/v1',
            'api_token' => '',
            'channel_integration_id' => 'channel-002',
            'timeout' => 30,
        ],
        'notifications' => [
            'withdrawal_approved' => [
                'enabled' => true,
                'template_id' => 'approved-template-2',
                'header_image_url' => '',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'full_name',
                        'source_table' => 'customers',
                        'source_column' => 'name',
                    ],
                    [
                        'key' => '2',
                        'value' => 'nominal',
                        'source_table' => 'computed',
                        'source_column' => 'nominal_text',
                    ],
                ],
            ],
            'withdrawal_rejected' => [
                'enabled' => false,
                'template_id' => 'rejected-template-2',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'full_name',
                        'source_table' => 'customers',
                        'source_column' => 'name',
                    ],
                ],
            ],
        ],
        'broadcast' => [
            'default_template_id' => 'broadcast-template-2',
            'header_image_url' => '',
            'bulk_retry_attempts' => 2,
            'bulk_retry_buffer_seconds' => 3,
        ],
    ]);

    Cache::flush();

    $updatedState = QontakWhatsAppSettings::getState();

    expect($updatedState['connection']['api_token'])->toBe('secret-token')
        ->and($updatedState['connection']['channel_integration_id'])->toBe('channel-002')
        ->and($updatedState['notifications']['withdrawal_rejected']['enabled'])->toBeFalse()
        ->and($updatedState['notifications']['withdrawal_approved']['parameters'][1]['source_column'])->toBe('nominal_text')
        ->and($updatedState['broadcast']['bulk_retry_buffer_seconds'])->toBe(3);
});
