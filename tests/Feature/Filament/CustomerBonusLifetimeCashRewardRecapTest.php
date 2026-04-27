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
        $table->string('name')->nullable();
        $table->string('username')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_bonus_lifetime_cash_rewards', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id')->nullable();
        $table->string('reward_name');
        $table->decimal('reward', 16, 2)->default(0);
        $table->decimal('bv', 16, 2)->default(0);
        $table->decimal('amount', 16, 2)->default(0);
        $table->unsignedTinyInteger('status')->default(0);
        $table->text('description')->nullable();
        $table->timestamps();
    });
});

it('builds a lifetime cash reward recap summary and only lists claimed rewards', function (): void {
    createLifetimeCashReward([
        'member_id' => 101,
        'reward_name' => 'Silver',
        'reward' => 20000000,
        'bv' => 3500,
        'amount' => 15000000,
        'status' => 1,
        'description' => 'Klaim terbaru',
        'created_at' => now()->subMinutes(10),
    ]);

    createLifetimeCashReward([
        'member_id' => 102,
        'reward_name' => 'Gold',
        'reward' => 12000000,
        'bv' => 2500,
        'amount' => 8000000,
        'status' => 0,
        'description' => 'Masih pending',
        'created_at' => now()->subHours(3),
    ]);

    createLifetimeCashReward([
        'member_id' => 103,
        'reward_name' => 'Platinum',
        'reward' => 5000000,
        'bv' => 1800,
        'amount' => 4500000,
        'status' => 1,
        'description' => 'Klaim lama',
        'created_at' => now()->subDay(),
    ]);

    /** @var ManageCustomerBonusLifetimeCashRewards $page */
    $page = app(ManageCustomerBonusLifetimeCashRewards::class);
    $data = invokeProtectedMethod($page, 'getLifetimeCashRewardRecapViewData');

    expect($data['summary']['total_records'])->toBe(3)
        ->and($data['summary']['claimed_count'])->toBe(2)
        ->and($data['summary']['pending_count'])->toBe(1)
        ->and($data['summary']['claimed_amount'])->toBe(19500000.0)
        ->and($data['summary']['pending_amount'])->toBe(8000000.0)
        ->and($data['summary']['unique_claimed_members'])->toBe(2)
        ->and($data['displayedClaimedCount'])->toBe(2)
        ->and($data['claimedRewards'])->toHaveCount(2)
        ->and($data['claimedRewards'][0]['reward_name'])->toBe('Silver')
        ->and($data['claimedRewards'][0]['reward'])->toBe(20000000.0)
        ->and($data['claimedRewards'][1]['reward_name'])->toBe('Platinum');
});

it('renders the claimed lifetime cash reward recap modal view', function (): void {
    $html = view('filament.resources.customer-bonus-lifetime-cash-rewards.modals.rekapitulasi', [
        'summary' => [
            'total_records' => 4,
            'total_amount' => 27500000.0,
            'pending_count' => 1,
            'pending_amount' => 3500000.0,
            'claimed_count' => 3,
            'claimed_amount' => 24000000.0,
            'unique_claimed_members' => 2,
            'latest_claimed_at' => '06 Apr 2026 09:15',
        ],
        'claimedRewards' => [
            [
                'id' => 77,
                'member_username' => 'member-alpha',
                'member_name' => 'Member Alpha',
                'reward_name' => 'Diamond',
                'reward' => 25000000.0,
                'bv' => 2200.0,
                'amount' => 9000000.0,
                'description' => 'Sudah cair',
                'created_at' => '06 Apr 2026 09:15',
            ],
        ],
        'displayedClaimedCount' => 1,
    ])->render();

    expect($html)->toContain('Rekap Sudah Klaim')
        ->and($html)->toContain('Sudah Dirilis')
        ->and($html)->toContain('Diamond')
        ->and($html)->toContain('Member Alpha');
});

it('defines the recap action only in the manage lifetime cash reward page source', function (): void {
    $lifetimePageSource = file_get_contents(app_path('Filament/Resources/CustomerBonusLifetimeCashRewards/Pages/ManageCustomerBonusLifetimeCashRewards.php'));
    $bonusRewardPageSource = file_get_contents(app_path('Filament/Resources/CustomerBonusRewards/Pages/ManageCustomerBonusRewards.php'));

    expect($lifetimePageSource)->toBeString()
        ->and($lifetimePageSource)->toContain("Action::make('view_rekapitulasi')")
        ->and($lifetimePageSource)->toContain('->action(static fn (): null => null)')
        ->and($lifetimePageSource)->toContain('getLifetimeCashRewardRecapViewData')
        ->and($lifetimePageSource)->toContain('getClaimedLifetimeCashRewardRecapRecords')
        ->and($lifetimePageSource)->toContain("->where('status', 1)")
        ->and($lifetimePageSource)->toContain("->modalHeading('Rekapitulasi Lifetime Cash Reward')")
        ->and($bonusRewardPageSource)->not->toContain("Action::make('view_rekapitulasi')");
});

function createLifetimeCashReward(array $attributes = []): CustomerBonusLifetimeCashReward
{
    $createdAt = $attributes['created_at'] ?? now();
    $updatedAt = $attributes['updated_at'] ?? $createdAt;

    unset($attributes['created_at'], $attributes['updated_at']);

    $reward = new CustomerBonusLifetimeCashReward(array_merge([
        'member_id' => null,
        'reward_name' => 'Default Reward',
        'reward' => 0,
        'bv' => 0,
        'amount' => 0,
        'status' => 0,
        'description' => null,
    ], $attributes));

    $reward->created_at = $createdAt;
    $reward->updated_at = $updatedAt;
    $reward->save();

    return $reward;
}

function invokeProtectedMethod(object $instance, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($instance, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($instance, $arguments);
}
