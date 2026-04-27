<?php

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
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

it('applies table columns and filters from customer ui settings', function (): void {
    $state = CustomerUiSettingsConfig::defaultState();
    $state['table']['columns']['email']['enabled'] = false;
    $state['table']['columns']['bonus_pending']['enabled'] = false;
    $state['table']['filters']['package_id'] = false;
    $state['table']['filters']['network_generated'] = false;
    $state['status']['labels'] = [
        '1' => 'Calon',
        '2' => 'Dormant',
        '3' => 'Aktif',
    ];

    CustomerUiSettingsConfig::writeState($state);

    /** @var array<int, object> $columns */
    $columns = invokePrivateStatic(CustomersTable::class, 'columns');
    $columnNames = collect($columns)
        ->map(fn (object $column): string => (string) $column->getName())
        ->all();

    expect($columnNames)->toContain('username')
        ->and($columnNames)->toContain('status')
        ->and($columnNames)->not->toContain('email')
        ->and($columnNames)->not->toContain('bonus_pending');

    /** @var array<int, object> $filters */
    $filters = invokePrivateStatic(CustomersTable::class, 'filters');
    $filterNames = collect($filters)
        ->map(fn (object $filter): string => (string) $filter->getName())
        ->all();

    expect($filterNames)->toContain('status')
        ->and($filterNames)->toContain('created_at')
        ->and($filterNames)->not->toContain('package_id')
        ->and($filterNames)->not->toContain('network_generated');

    $statusFilter = collect($filters)->first(fn (object $filter): bool => (string) $filter->getName() === 'status');

    expect($statusFilter)->not->toBeNull();
    expect($statusFilter->getOptions())->toBe([
        1 => 'Calon',
        2 => 'Dormant',
        3 => 'Aktif',
    ]);
});

it('applies form section and status options from customer ui settings', function (): void {
    $state = CustomerUiSettingsConfig::defaultState();
    $state['form']['sections']['profile_basic'] = false;
    $state['form']['sections']['addresses'] = true;
    $state['status']['labels'] = [
        '1' => 'Candidate',
        '2' => 'Inactive',
        '3' => 'Active',
    ];

    CustomerUiSettingsConfig::writeState($state);

    $profileBasicEnabled = invokePrivateStatic(CustomerForm::class, 'isFormSectionEnabled', ['profile_basic']);
    $addressesEnabled = invokePrivateStatic(CustomerForm::class, 'isFormSectionEnabled', ['addresses']);
    $statusOptions = invokePrivateStatic(CustomerForm::class, 'statusFieldOptions');

    expect($profileBasicEnabled)->toBeFalse()
        ->and($addressesEnabled)->toBeTrue()
        ->and($statusOptions)->toBe([
            1 => 'Candidate',
            2 => 'Inactive',
            3 => 'Active',
        ]);
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}

it('stores customer ui settings with customers prefix keys', function (): void {
    CustomerUiSettingsConfig::writeState(CustomerUiSettingsConfig::defaultState());

    expect(Setting::query()->where('key', 'customers.table.columns')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.table.filters')->exists())->toBeTrue()
        ->and(Setting::query()->where('key', 'customers.form.sections')->exists())->toBeTrue();
});
