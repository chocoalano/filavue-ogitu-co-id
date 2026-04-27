<?php

use App\Filament\Resources\CustomerWithdrawals\Exports\CustomerWithdrawalExporter;
use App\Models\CustomerWalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_wallet_transactions');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('ref_code')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->decimal('balance_before', 16, 2)->default(0);
        $table->decimal('balance_after', 16, 2)->default(0);
        $table->string('status')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('transaction_ref')->nullable();
        $table->text('notes')->nullable();
        $table->dateTime('completed_at')->nullable();
        $table->boolean('is_system')->default(false);
        $table->timestamps();
    });

    DB::table('customer_wallet_transactions')->insert([
        [
            'customer_id' => null,
            'type' => 'withdrawal',
            'amount' => 10000,
            'balance_before' => 50000,
            'balance_after' => 40000,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'transaction_ref' => 'WD-1',
            'notes' => null,
            'completed_at' => null,
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'customer_id' => null,
            'type' => 'topup',
            'amount' => 20000,
            'balance_before' => 40000,
            'balance_after' => 60000,
            'status' => 'completed',
            'payment_method' => 'midtrans',
            'transaction_ref' => 'TOPUP-1',
            'notes' => null,
            'completed_at' => now(),
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('exports only withdrawal records through exporter query modifier', function (): void {
    $query = CustomerWithdrawalExporter::modifyQuery(CustomerWalletTransaction::query());

    expect((int) $query->count())->toBe(1)
        ->and((string) $query->firstOrFail()->type)->toBe('withdrawal');
});

it('exposes expected export columns for customer withdrawals', function (): void {
    $columnNames = collect(CustomerWithdrawalExporter::getColumns())
        ->map(fn ($column): string => $column->getName())
        ->all();

    expect($columnNames)->toContain(
        'customer.name',
        'amount',
        'admin_fee',
        'total_potongan',
        'status',
        'transaction_ref',
    );
});

it('extracts admin fee amount from withdrawal notes format', function (): void {
    $adminFee = invokePrivateStatic(
        CustomerWithdrawalExporter::class,
        'extractSubmissionAdminFee',
        ['Bank: BCA (1234567890)'.PHP_EOL.'Biaya admin: Rp 6.500'.PHP_EOL.'Estimasi diterima: Rp 43.500'],
    );

    expect($adminFee)->toBe(6500.0);
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
