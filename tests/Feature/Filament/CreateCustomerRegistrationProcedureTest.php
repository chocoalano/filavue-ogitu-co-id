<?php

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Models\Customer;
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
    Schema::dropIfExists('customer_networks');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('upline_id')->nullable();
        $table->string('position')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_networks', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id');
        $table->timestamps();
    });

    Schema::create('customer_network_matrixes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id');
        $table->timestamps();
    });
});

it('skips registration procedure when customer network already exists', function (): void {
    DB::table('customers')->insert([
        'id' => 1001,
        'upline_id' => 500,
        'position' => 'left',
        'name' => 'Existing Network Customer',
        'email' => 'existing.network@example.test',
        'password' => bcrypt('secret123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_networks')->insert([
        'member_id' => 1001,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $customer = Customer::query()->findOrFail(1001);

    $shouldExecute = invokePrivateStatic(
        CreateCustomer::class,
        'shouldExecuteRegistrationProcedure',
        [$customer]
    );

    expect($shouldExecute)->toBeFalse();
});

it('allows registration procedure for eligible newly created customer', function (): void {
    DB::table('customers')->insert([
        'id' => 1002,
        'upline_id' => 500,
        'position' => 'right',
        'name' => 'Eligible Customer',
        'email' => 'eligible.customer@example.test',
        'password' => bcrypt('secret123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $customer = Customer::query()->findOrFail(1002);

    $shouldExecute = invokePrivateStatic(
        CreateCustomer::class,
        'shouldExecuteRegistrationProcedure',
        [$customer]
    );

    expect($shouldExecute)->toBeTrue();
});

it('calls sp_registration using prepared statement syntax', function (): void {
    $statement = M::mock();
    $statement->shouldReceive('execute')
        ->once()
        ->with([2026])
        ->andReturnTrue();
    $statement->shouldReceive('nextRowset')
        ->once()
        ->andReturnFalse();

    $pdo = M::mock();
    $pdo->shouldReceive('prepare')
        ->once()
        ->with('CALL sp_registration(?)')
        ->andReturn($statement);

    $connection = M::mock();
    $connection->shouldReceive('getPdo')
        ->once()
        ->andReturn($pdo);

    DB::shouldReceive('connection')
        ->once()
        ->andReturn($connection);

    invokePrivateStatic(CreateCustomer::class, 'callRegistrationProcedure', [2026]);
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
