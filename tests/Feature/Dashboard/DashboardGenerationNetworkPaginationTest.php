<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery as M;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_network_matrixes');
    Schema::dropIfExists('customer_package');
    Schema::dropIfExists('customers');

    Schema::create('customer_package', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
    });

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('package_id')->nullable();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('password')->nullable();
        $table->string('level')->nullable();
        $table->decimal('omzet_group', 16, 2)->default(0);
        $table->integer('status')->default(1);
        $table->timestamps();
    });

    Schema::create('customer_network_matrixes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id')->nullable();
        $table->unsignedBigInteger('sponsor_id')->nullable();
        $table->unsignedInteger('level')->nullable();
        $table->string('description')->nullable();
        $table->timestamps();
    });
});

it('loads generation network data with generation filter and pagination metadata', function (): void {
    DB::table('customer_package')->insert([
        ['id' => 1, 'name' => 'Silver'],
        ['id' => 2, 'name' => 'Gold'],
    ]);

    DB::table('customers')->insert([
        'id' => 1,
        'package_id' => 2,
        'username' => 'sponsor-utama',
        'name' => 'Sponsor Utama',
        'email' => 'sponsor@example.test',
        'phone' => '081200000001',
        'password' => bcrypt('secret123'),
        'level' => 'Director',
        'omzet_group' => 9000000,
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customers')->insert([
        'id' => 2,
        'package_id' => 1,
        'username' => 'sponsor-lain',
        'name' => 'Sponsor Lain',
        'email' => 'sponsor-lain@example.test',
        'phone' => '081200000002',
        'password' => bcrypt('secret123'),
        'level' => 'Associate',
        'omzet_group' => 100000,
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    for ($index = 0; $index < 16; $index++) {
        $memberId = 100 + $index;

        DB::table('customers')->insert([
            'id' => $memberId,
            'package_id' => $index % 2 === 0 ? 1 : 2,
            'username' => 'gen2-'.$index,
            'name' => 'Member Gen2 '.$index,
            'email' => 'gen2-'.$index.'@example.test',
            'phone' => '08121000'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'password' => bcrypt('secret123'),
            'level' => $index % 2 === 0 ? 'Associate' : 'Executive',
            'omzet_group' => 100000 + ($index * 1000),
            'status' => $index % 3 === 0 ? 3 : 2,
            'created_at' => now()->subMinutes($index + 1),
            'updated_at' => now()->subMinutes($index + 1),
        ]);

        DB::table('customer_network_matrixes')->insert([
            'member_id' => $memberId,
            'sponsor_id' => 1,
            'level' => 2,
            'description' => 'Generasi 2',
            'created_at' => now()->subMinutes($index + 1),
            'updated_at' => now()->subMinutes($index + 1),
        ]);
    }

    DB::table('customers')->insert([
        'id' => 300,
        'package_id' => 1,
        'username' => 'gen1',
        'name' => 'Member Gen1',
        'email' => 'gen1@example.test',
        'phone' => '081233330001',
        'password' => bcrypt('secret123'),
        'level' => 'Associate',
        'omzet_group' => 150000,
        'status' => 3,
        'created_at' => now()->subHours(2),
        'updated_at' => now()->subHours(2),
    ]);

    DB::table('customer_network_matrixes')->insert([
        'member_id' => 300,
        'sponsor_id' => 1,
        'level' => 1,
        'description' => 'Generasi 1',
        'created_at' => now()->subHours(2),
        'updated_at' => now()->subHours(2),
    ]);

    DB::table('customers')->insert([
        'id' => 301,
        'package_id' => 2,
        'username' => 'gen3',
        'name' => 'Member Gen3',
        'email' => 'gen3@example.test',
        'phone' => '081233330002',
        'password' => bcrypt('secret123'),
        'level' => 'Gold',
        'omzet_group' => 200000,
        'status' => 1,
        'created_at' => now()->subHours(3),
        'updated_at' => now()->subHours(3),
    ]);

    DB::table('customer_network_matrixes')->insert([
        'member_id' => 301,
        'sponsor_id' => 1,
        'level' => 3,
        'description' => 'Generasi 3',
        'created_at' => now()->subHours(3),
        'updated_at' => now()->subHours(3),
    ]);

    DB::table('customers')->insert([
        'id' => 400,
        'package_id' => 1,
        'username' => 'bukan-saya',
        'name' => 'Bukan Jaringan Saya',
        'email' => 'other@example.test',
        'phone' => '081244440001',
        'password' => bcrypt('secret123'),
        'level' => 'Associate',
        'omzet_group' => 50000,
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_network_matrixes')->insert([
        'member_id' => 400,
        'sponsor_id' => 2,
        'level' => 2,
        'description' => 'Generasi 2 sponsor lain',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new DashboardService(
        app(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $customer = Customer::query()->findOrFail(1);

    $method = new ReflectionMethod(DashboardService::class, 'loadGenerationNetworkData');
    $method->setAccessible(true);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke($service, $customer, 2, ['level' => 2]);

    expect($payload['current_page'])->toBe(2)
        ->and($payload['per_page'])->toBe(15)
        ->and($payload['total'])->toBe(16)
        ->and($payload['last_page'])->toBe(2)
        ->and($payload['from'])->toBe(16)
        ->and($payload['to'])->toBe(16)
        ->and($payload['filters'])->toBe(['level' => 2])
        ->and($payload['available_generations'])->toBe([
            ['value' => 1, 'label' => 'Generasi 1'],
            ['value' => 2, 'label' => 'Generasi 2'],
            ['value' => 3, 'label' => 'Generasi 3'],
        ])
        ->and($payload['data'])->toHaveCount(1)
        ->and($payload['data'][0])->toMatchArray([
            'username' => 'gen2-15',
            'generation' => 2,
            'package_name' => 'Gold',
        ]);
});
