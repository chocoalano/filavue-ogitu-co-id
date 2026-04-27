<?php

use App\Models\Setting;
use App\Support\CustomerUiSettingsConfig;
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

it('returns default customer ui settings when no record exists', function (): void {
    $state = CustomerUiSettingsConfig::getState();

    expect($state['table']['columns']['username']['enabled'])->toBeTrue()
        ->and($state['table']['columns']['ref_code']['hidden_by_default'])->toBeTrue()
        ->and($state['table']['filters']['status'])->toBeTrue()
        ->and($state['form']['sections']['profile_basic'])->toBeTrue()
        ->and($state['status']['labels']['1'])->toBe('Prospek')
        ->and($state['status']['colors']['3'])->toBe('success');
});

it('loads and normalizes stored customer ui settings', function (): void {
    Setting::query()->create([
        'key' => 'customers.table.columns',
        'value' => json_encode([
            'username' => ['enabled' => false, 'hidden_by_default' => true],
            'ref_code' => ['enabled' => true, 'hidden_by_default' => false],
        ], JSON_UNESCAPED_UNICODE),
        'type' => 'text',
        'group' => 'general',
    ]);

    Setting::query()->create([
        'key' => 'customers.table.filters',
        'value' => json_encode(['status', 'created_at'], JSON_UNESCAPED_UNICODE),
        'type' => 'text',
        'group' => 'general',
    ]);

    Setting::query()->create([
        'key' => 'customers.form.sections',
        'value' => json_encode(['addresses' => false], JSON_UNESCAPED_UNICODE),
        'type' => 'text',
        'group' => 'general',
    ]);

    Setting::query()->create([
        'key' => 'customers.status.labels',
        'value' => json_encode([
            '1' => 'Calon Member',
            '2' => 'Dormant',
            '3' => 'Aktif Jalan',
        ], JSON_UNESCAPED_UNICODE),
        'type' => 'text',
        'group' => 'general',
    ]);

    Setting::query()->create([
        'key' => 'customers.status.colors',
        'value' => json_encode([
            '1' => 'info',
            '2' => 'danger',
            '3' => 'success',
        ], JSON_UNESCAPED_UNICODE),
        'type' => 'text',
        'group' => 'general',
    ]);

    $state = CustomerUiSettingsConfig::getState();

    expect($state['table']['columns']['username']['enabled'])->toBeFalse()
        ->and($state['table']['columns']['username']['hidden_by_default'])->toBeTrue()
        ->and($state['table']['columns']['ref_code']['hidden_by_default'])->toBeFalse()
        ->and($state['table']['columns']['name']['enabled'])->toBeTrue()
        ->and($state['table']['filters']['status'])->toBeTrue()
        ->and($state['table']['filters']['created_at'])->toBeTrue()
        ->and($state['table']['filters']['package_id'])->toBeFalse()
        ->and($state['form']['sections']['addresses'])->toBeFalse()
        ->and($state['form']['sections']['profile_basic'])->toBeTrue()
        ->and($state['status']['labels']['1'])->toBe('Calon Member')
        ->and($state['status']['colors']['2'])->toBe('danger');
});

it('writes normalized customer ui settings into storage', function (): void {
    CustomerUiSettingsConfig::writeState([
        'table' => [
            'columns' => [
                'username' => ['enabled' => false, 'hidden_by_default' => true],
            ],
            'filters' => [
                'status' => false,
                'package_id' => true,
            ],
        ],
        'form' => [
            'sections' => [
                'profile_basic' => false,
            ],
        ],
        'status' => [
            'labels' => [
                '1' => '',
                '2' => 'Dormant',
            ],
            'colors' => [
                '1' => 'invalid',
                '2' => 'danger',
                '3' => 'info',
            ],
        ],
    ]);

    $state = CustomerUiSettingsConfig::getState();

    expect(Setting::query()->where('key', 'customers.table.columns')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.table.filters')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.form.sections')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.status.labels')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.status.colors')->exists())->toBeTrue()
        ->and($state['table']['columns']['username']['enabled'])->toBeFalse()
        ->and($state['table']['columns']['username']['hidden_by_default'])->toBeTrue()
        ->and($state['table']['columns']['name']['enabled'])->toBeTrue()
        ->and($state['table']['filters']['status'])->toBeFalse()
        ->and($state['table']['filters']['package_id'])->toBeTrue()
        ->and($state['table']['filters']['created_at'])->toBeTrue()
        ->and($state['form']['sections']['profile_basic'])->toBeFalse()
        ->and($state['form']['sections']['addresses'])->toBeTrue()
        ->and($state['status']['labels']['1'])->toBe('Prospek')
        ->and($state['status']['labels']['2'])->toBe('Dormant')
        ->and($state['status']['colors']['1'])->toBe('gray')
        ->and($state['status']['colors']['2'])->toBe('danger')
        ->and($state['status']['colors']['3'])->toBe('info');
});
