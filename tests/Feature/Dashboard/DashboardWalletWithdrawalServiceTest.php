<?php

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery as M;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('app.timezone', 'Asia/Jakarta');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Carbon::setTestNow(CarbonImmutable::parse('2026-03-05 10:15:00', 'Asia/Jakarta'));

    Schema::dropIfExists('customer_wallet_transactions');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
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

it('rejects withdrawal when amount is below minimum request amount', function (): void {
    $service = buildDashboardWalletWithdrawalService();
    $customer = createWalletWithdrawalCustomer(5101, 500000);

    $caughtException = null;

    try {
        $service->submitWalletWithdrawal($customer, [
            'amount' => 9000,
            'password' => 'secret123',
        ]);
    } catch (ValidationException $exception) {
        $caughtException = $exception;
    }

    expect($caughtException)->not->toBeNull()
        ->and($caughtException?->errors())->toHaveKey('amount')
        ->and($caughtException?->errors()['amount'][0] ?? null)
        ->toContain('Nominal withdrawal minimal Rp 10.000');
});

it('rejects withdrawal amount that is not a multiple of five hundred', function (): void {
    $service = buildDashboardWalletWithdrawalService();
    $customer = createWalletWithdrawalCustomer(5102, 500000);

    $caughtException = null;

    try {
        $service->submitWalletWithdrawal($customer, [
            'amount' => 17050,
            'password' => 'secret123',
        ]);
    } catch (ValidationException $exception) {
        $caughtException = $exception;
    }

    expect($caughtException)->not->toBeNull()
        ->and($caughtException?->errors())->toHaveKey('amount')
        ->and($caughtException?->errors()['amount'][0] ?? null)
        ->toBe('Nominal withdrawal harus kelipatan Rp 500.');
});

it('accepts minimum withdrawal amount and applies admin fee in notes', function (): void {
    $service = buildDashboardWalletWithdrawalService();
    $customer = createWalletWithdrawalCustomer(5104, 500000);

    $result = $service->submitWalletWithdrawal($customer, [
        'amount' => 10000,
        'password' => 'secret123',
    ]);

    $freshCustomer = Customer::query()->findOrFail($customer->id);
    $transaction = CustomerWalletTransaction::query()
        ->where('customer_id', $customer->id)
        ->latest('id')
        ->first();

    expect($result['balance'])->toBe(490000.0)
        ->and((float) $freshCustomer->ewallet_saldo)->toBe(490000.0)
        ->and($transaction)->not->toBeNull()
        ->and((float) ($transaction?->amount ?? 0))->toBe(10000.0)
        ->and((string) ($transaction?->status ?? ''))->toBe('pending')
        ->and((string) ($transaction?->notes ?? ''))
        ->toContain('Biaya admin: Rp 6.500')
        ->toContain('Estimasi diterima: Rp 3.500');
});

it('creates pending withdrawal and deducts wallet balance when payload is valid', function (): void {
    $service = buildDashboardWalletWithdrawalService();
    $customer = createWalletWithdrawalCustomer(5103, 500000);

    $result = $service->submitWalletWithdrawal($customer, [
        'amount' => 50000,
        'password' => 'secret123',
        'notes' => 'Pencairan mingguan',
    ]);

    $freshCustomer = Customer::query()->findOrFail($customer->id);
    $transaction = CustomerWalletTransaction::query()
        ->where('customer_id', $customer->id)
        ->latest('id')
        ->first();

    expect($result['message'])->toBe('Permintaan withdrawal berhasil dikirim.')
        ->and($result['balance'])->toBe(450000.0)
        ->and((float) $freshCustomer->ewallet_saldo)->toBe(450000.0)
        ->and($transaction)->not->toBeNull()
        ->and((float) ($transaction?->amount ?? 0))->toBe(50000.0)
        ->and((string) ($transaction?->status ?? ''))->toBe('pending')
        ->and((string) ($transaction?->notes ?? ''))
        ->toContain('Biaya admin: Rp 6.500')
        ->toContain('Estimasi diterima: Rp 43.500')
        ->toContain('Pencairan mingguan');
});

function buildDashboardWalletWithdrawalService(): DashboardService
{
    return new DashboardService(
        app(DashboardRepositoryInterface::class),
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );
}

function createWalletWithdrawalCustomer(int $id, float $balance): Customer
{
    DB::table('customers')->insert([
        'id' => $id,
        'name' => 'Customer '.$id,
        'email' => "customer{$id}@example.test",
        'password' => bcrypt('secret123'),
        'bank_name' => 'Bank BCA',
        'bank_account' => '1234567890',
        'status' => 3,
        'ewallet_saldo' => $balance,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Customer::query()->findOrFail($id);
}
