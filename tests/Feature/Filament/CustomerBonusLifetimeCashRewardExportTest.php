<?php

use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Pages\ManageCustomerBonusLifetimeCashRewards;
use App\Models\CustomerBonusLifetimeCashReward;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');
    Schema::dropIfExists('customer_bonus_lifetime_cash_rewards');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('ref_code')->nullable();
        $table->string('ewallet_id')->nullable();
        $table->string('name')->nullable();
        $table->string('username')->nullable();
        $table->decimal('ewallet_saldo', 16, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('customer_bonus_lifetime_cash_rewards', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id')->nullable();
        $table->string('reward_name');
        $table->decimal('reward', 16, 2)->default(0);
        $table->decimal('amount', 16, 2)->default(0);
        $table->decimal('bv', 16, 2)->default(0);
        $table->unsignedTinyInteger('status')->default(0);
        $table->text('description')->nullable();
        $table->timestamps();
    });
});

it('applies lifetime cash reward export filters by status and customer wallet balance', function (): void {
    DB::table('customers')->insert([
        [
            'id' => 10,
            'ref_code' => 'REF-10',
            'ewallet_id' => 'EW-10',
            'name' => 'Member Besar',
            'username' => 'member-besar',
            'ewallet_saldo' => 500000,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 20,
            'ref_code' => 'REF-20',
            'ewallet_id' => 'EW-20',
            'name' => 'Member Kecil',
            'username' => 'member-kecil',
            'ewallet_saldo' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    createLifetimeCashRewardRecord([
        'member_id' => 10,
        'reward_name' => 'Silver',
        'reward' => 20000000,
        'amount' => 15000000,
        'status' => 1,
        'created_at' => now()->subDay(),
    ]);

    createLifetimeCashRewardRecord([
        'member_id' => 20,
        'reward_name' => 'Gold',
        'reward' => 10000000,
        'amount' => 8000000,
        'status' => 1,
        'created_at' => now()->subDay(),
    ]);

    createLifetimeCashRewardRecord([
        'member_id' => 10,
        'reward_name' => 'Platinum',
        'reward' => 30000000,
        'amount' => 0,
        'status' => 0,
        'created_at' => now()->subDay(),
    ]);

    $query = invokeProtectedStaticMethod(
        ManageCustomerBonusLifetimeCashRewards::class,
        'applyExportFilters',
        [
            CustomerBonusLifetimeCashReward::query(),
            [
                'status' => 1,
                'min_wallet_balance' => 100000,
            ],
        ],
    );

    expect($query->pluck('reward_name')->all())->toBe(['Silver']);
});

it('defines the download excel action and customer wallet balance filters in source', function (): void {
    $pageSource = file_get_contents(app_path('Filament/Resources/CustomerBonusLifetimeCashRewards/Pages/ManageCustomerBonusLifetimeCashRewards.php'));
    $exporterSource = file_get_contents(app_path('Filament/Resources/CustomerBonusLifetimeCashRewards/Exports/CustomerBonusLifetimeCashRewardExporter.php'));

    expect($pageSource)->toBeString()
        ->and($pageSource)->toContain('ExportAction::make()')
        ->and($pageSource)->toContain("->label('Download Excel')")
        ->and($pageSource)->toContain('CustomerBonusLifetimeCashRewardExporter::class')
        ->and($pageSource)->toContain('applyExportFilters')
        ->and($pageSource)->toContain("where('ewallet_saldo', '>=', \$options['min_wallet_balance'])")
        ->and($exporterSource)->toBeString()
        ->and($exporterSource)->toContain("TextInput::make('min_wallet_balance')")
        ->and($exporterSource)->toContain("TextInput::make('max_wallet_balance')")
        ->and($exporterSource)->toContain('Gunakan filter saldo customer jika export penuh terasa berat.')
        ->and($exporterSource)->toContain('ExportFormat::Xlsx');
});

function createLifetimeCashRewardRecord(array $attributes = []): CustomerBonusLifetimeCashReward
{
    $createdAt = $attributes['created_at'] ?? now();
    $updatedAt = $attributes['updated_at'] ?? $createdAt;

    unset($attributes['created_at'], $attributes['updated_at']);

    $reward = new CustomerBonusLifetimeCashReward(array_merge([
        'member_id' => null,
        'reward_name' => 'Default Reward',
        'reward' => 0,
        'amount' => 0,
        'bv' => 0,
        'status' => 0,
        'description' => null,
    ], $attributes));

    $reward->created_at = $createdAt;
    $reward->updated_at = $updatedAt;
    $reward->save();

    return $reward;
}

function invokeProtectedStaticMethod(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
