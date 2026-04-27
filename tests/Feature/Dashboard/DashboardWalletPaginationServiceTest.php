<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery as M;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('app.timezone', 'Asia/Jakarta');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Carbon::setTestNow(CarbonImmutable::parse('2026-03-03 18:10:00', 'Asia/Jakarta'));

    Schema::dropIfExists('customer_wallet_transactions');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->integer('status')->default(3);
        $table->decimal('ewallet_saldo', 16, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->decimal('balance_before', 16, 2)->default(0);
        $table->decimal('balance_after', 16, 2)->default(0);
        $table->string('status')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('transaction_ref')->nullable();
        $table->string('midtrans_transaction_id')->nullable();
        $table->text('notes')->nullable();
        $table->dateTime('completed_at')->nullable();
        $table->boolean('is_system')->default(false);
        $table->string('midtrans_signature_key')->nullable();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('returns wallet transactions payload with valid summary, window, and pagination format', function (): void {
    DB::table('customers')->insert([
        'id' => 24,
        'name' => 'Wallet Member',
        'email' => 'wallet24@example.test',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'ewallet_saldo' => 2993250,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_wallet_transactions')->insert([
        [
            'id' => 1207,
            'customer_id' => 24,
            'type' => 'topup',
            'amount' => 50000,
            'balance_before' => 2993250,
            'balance_after' => 2993250,
            'status' => 'pending',
            'payment_method' => 'midtrans',
            'transaction_ref' => 'TOPUP-24-20260303180112-F24707',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'completed_at' => null,
        ],
        [
            'id' => 1206,
            'customer_id' => 24,
            'type' => 'topup',
            'amount' => 640000,
            'balance_before' => 2353250,
            'balance_after' => 2993250,
            'status' => 'completed',
            'payment_method' => 'midtrans',
            'transaction_ref' => 'TOPUP-24-20260301120000-AAAAAA',
            'created_at' => CarbonImmutable::now()->subDays(2),
            'updated_at' => CarbonImmutable::now()->subDays(2),
            'completed_at' => CarbonImmutable::now()->subDays(2),
        ],
        [
            'id' => 1205,
            'customer_id' => 24,
            'type' => 'withdrawal',
            'amount' => 150000,
            'balance_before' => 3143250,
            'balance_after' => 2993250,
            'status' => 'completed',
            'payment_method' => 'bank_transfer',
            'transaction_ref' => 'WD-24-20260301090000-BBBBBB',
            'created_at' => CarbonImmutable::now()->subDays(1),
            'updated_at' => CarbonImmutable::now()->subDays(1),
            'completed_at' => CarbonImmutable::now()->subDays(1),
        ],
        [
            'id' => 1204,
            'customer_id' => 24,
            'type' => 'bonus',
            'amount' => 75000,
            'balance_before' => 3068250,
            'balance_after' => 3143250,
            'status' => 'challenge',
            'payment_method' => null,
            'transaction_ref' => 'BONUS-24-20260301070000-CCCCCC',
            'created_at' => CarbonImmutable::now()->subDays(1),
            'updated_at' => CarbonImmutable::now()->subDays(1),
            'completed_at' => null,
        ],
        [
            'id' => 1100,
            'customer_id' => 24,
            'type' => 'topup',
            'amount' => 999999,
            'balance_before' => 0,
            'balance_after' => 0,
            'status' => 'completed',
            'payment_method' => 'midtrans',
            'transaction_ref' => 'TOPUP-24-20260101000000-OLD000',
            'created_at' => CarbonImmutable::now()->subDays(40),
            'updated_at' => CarbonImmutable::now()->subDays(40),
            'completed_at' => CarbonImmutable::now()->subDays(40),
        ],
    ]);

    $service = new DashboardService(
        app(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $customer = Customer::query()->findOrFail(24);

    $payload = $service->getWalletTransactionsPagination($customer, 1, 15, [
        'type' => 'topup',
        'status' => 'pending',
        'sort' => 'newest',
    ]);

    expect($payload['summary']['balance_available'])->toBe(2993250.0)
        ->and($payload['summary']['topup_30d'])->toBe(640000.0)
        ->and($payload['summary']['withdrawal_30d'])->toBe(150000.0)
        ->and($payload['summary']['netflow_30d'])->toBe(490000.0)
        ->and($payload['summary']['pending_count'])->toBe(2)
        ->and($payload['window']['days'])->toBe(30)
        ->and($payload['window']['timezone'])->toBe('Asia/Jakarta')
        ->and($payload['window']['from'])->toBe('2026-02-02T00:00:00+07:00')
        ->and($payload['window']['to'])->toBe('2026-03-03T23:59:59+07:00')
        ->and($payload['current_page'])->toBe(1)
        ->and($payload['per_page'])->toBe(15)
        ->and($payload['total'])->toBe(1)
        ->and($payload['data'])->toHaveCount(1)
        ->and($payload['data'][0])->toMatchArray([
            'id' => 1207,
            'type' => 'topup',
            'type_label' => 'Top Up Saldo',
            'direction' => 'credit',
            'status' => 'pending',
            'status_label' => 'Menunggu',
            'amount' => 50000.0,
            'balance_before' => 2993250.0,
            'balance_after' => 2993250.0,
            'payment_method' => 'midtrans',
            'transaction_ref' => 'TOPUP-24-20260303180112-F24707',
            'description' => 'Top Up Saldo • Ref: TOPUP-24-20260303180112-F24707 • MIDTRANS',
        ]);

    expect(array_key_exists('notes', $payload['data'][0]))->toBeFalse()
        ->and(array_key_exists('midtrans_transaction_id', $payload['data'][0]))->toBeFalse()
        ->and(array_key_exists('is_system', $payload['data'][0]))->toBeFalse()
        ->and(array_key_exists('to', $payload['data'][0]))->toBeFalse();
});
