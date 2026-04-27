<?php

use App\Models\Customer;
use App\Services\Dashboard\DashboardLeaderboardService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_bonuses');
    Schema::dropIfExists('customers');
    Schema::dropIfExists('customer_package');

    Schema::create('customer_package', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
    });

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('level')->nullable();
        $table->unsignedBigInteger('package_id')->nullable();
        $table->integer('status')->default(1);
        $table->string('email')->nullable();
        $table->string('password')->nullable();
    });

    Schema::create('customer_bonuses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id')->nullable();
        $table->decimal('index_value', 16, 2)->default(0);
        $table->date('date')->nullable();
    });
});

it('falls back to all-time leaderboard when selected period has no points', function (): void {
    DB::table('customers')->insert([
        ['id' => 1, 'name' => 'Budi Santoso', 'level' => 'Gold Member', 'status' => 3],
        ['id' => 2, 'name' => 'Andi Wijaya', 'level' => 'Diamond', 'status' => 3],
        ['id' => 3, 'name' => 'Citra Lestari', 'level' => 'Platinum', 'status' => 3],
    ]);

    DB::table('customer_bonuses')->insert([
        [
            'member_id' => 2,
            'index_value' => 125000,
            'date' => CarbonImmutable::now()->subMonths(2)->toDateString(),
        ],
        [
            'member_id' => 3,
            'index_value' => 112500,
            'date' => CarbonImmutable::now()->subMonths(2)->subDay()->toDateString(),
        ],
    ]);

    $authenticatedCustomer = Customer::query()->findOrFail(1);

    $payload = app(DashboardLeaderboardService::class)->getLeaderboardData(
        $authenticatedCustomer,
        'daily',
        1,
    );

    expect($payload['selected_tab'])->toBe(1)
        ->and($payload['leaderboard'])->toHaveCount(3)
        ->and($payload['leaderboard'][0]['id'])->toBe(2)
        ->and($payload['leaderboard'][0]['points'])->toBe(125000)
        ->and($payload['leaderboard'][1]['id'])->toBe(3)
        ->and($payload['leaderboard'][1]['points'])->toBe(112500)
        ->and($payload['leaderboard'][2]['id'])->toBe(1)
        ->and($payload['leaderboard'][2]['points'])->toBe(0)
        ->and($payload['my_rank']['rank'])->toBe(3)
        ->and($payload['my_rank']['points'])->toBe(0)
        ->and($payload['my_rank']['trend'])->toBe('neutral');
});

it('returns all members even when all periods have zero points', function (): void {
    DB::table('customers')->insert([
        ['id' => 11, 'name' => 'Member Satu', 'level' => 'Silver', 'status' => 3],
        ['id' => 12, 'name' => 'Member Dua', 'level' => 'Gold', 'status' => 3],
    ]);

    $authenticatedCustomer = Customer::query()->findOrFail(12);

    $payload = app(DashboardLeaderboardService::class)->getLeaderboardData(
        $authenticatedCustomer,
        'daily',
        1,
    );

    expect($payload['leaderboard'])->toHaveCount(2)
        ->and($payload['leaderboard'][0]['id'])->toBe(11)
        ->and($payload['leaderboard'][0]['points'])->toBe(0)
        ->and($payload['leaderboard'][1]['id'])->toBe(12)
        ->and($payload['leaderboard'][1]['points'])->toBe(0)
        ->and($payload['my_rank']['rank'])->toBe(2)
        ->and($payload['my_rank']['trend'])->toBe('neutral');
});
