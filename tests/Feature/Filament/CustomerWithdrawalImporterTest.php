<?php

use App\Filament\Resources\CustomerWithdrawals\Imports\CustomerWithdrawalImporter;
use App\Models\CustomerWalletTransaction;
use Filament\Actions\Imports\Models\Import;
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

    DB::table('customers')->insert([
        'id' => 1001,
        'name' => 'Customer Import',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('imports customer withdrawal row with normalized values', function (): void {
    $importer = new CustomerWithdrawalImporter(
        import: new Import,
        columnMap: [
            'customer_id' => 'Customer ID',
            'transaction_ref' => 'Ref',
            'amount' => 'Nominal',
            'balance_before' => 'Saldo Sebelum',
            'status' => 'Status',
            'payment_method' => 'Metode',
            'notes' => 'Catatan',
            'is_system' => 'Sistem',
        ],
        options: [],
    );

    $importer([
        'Customer ID' => '1001',
        'Ref' => ' WD-IMPORT-001 ',
        'Nominal' => '10000',
        'Saldo Sebelum' => '50000',
        'Status' => 'COMPLETED',
        'Metode' => ' bank_transfer ',
        'Catatan' => ' import awal ',
        'Sistem' => 'ya',
    ]);

    $record = CustomerWalletTransaction::query()->firstWhere('transaction_ref', 'WD-IMPORT-001');

    expect($record)->not->toBeNull()
        ->and($record?->type)->toBe('withdrawal')
        ->and((float) ($record?->amount ?? 0))->toBe(10000.0)
        ->and((float) ($record?->balance_before ?? 0))->toBe(50000.0)
        ->and((float) ($record?->balance_after ?? 0))->toBe(40000.0)
        ->and($record?->status)->toBe('completed')
        ->and($record?->payment_method)->toBe('bank_transfer')
        ->and($record?->notes)->toBe('import awal')
        ->and((bool) ($record?->is_system ?? false))->toBeTrue();
});

it('upserts customer withdrawal row by transaction ref', function (): void {
    $importer = new CustomerWithdrawalImporter(
        import: new Import,
        columnMap: [
            'customer_id' => 'customer_id',
            'transaction_ref' => 'transaction_ref',
            'amount' => 'amount',
            'balance_before' => 'balance_before',
            'balance_after' => 'balance_after',
            'status' => 'status',
        ],
        options: [],
    );

    $importer([
        'customer_id' => 1001,
        'transaction_ref' => 'WD-UPSERT-001',
        'amount' => 10000,
        'balance_before' => 50000,
        'balance_after' => 40000,
        'status' => 'pending',
    ]);

    $importer([
        'customer_id' => 1001,
        'transaction_ref' => ' WD-UPSERT-001 ',
        'amount' => 12000,
        'balance_before' => 50000,
        'balance_after' => 38000,
        'status' => 'completed',
    ]);

    $record = CustomerWalletTransaction::query()->firstWhere('transaction_ref', 'WD-UPSERT-001');

    expect(CustomerWalletTransaction::query()->count())->toBe(1)
        ->and($record)->not->toBeNull()
        ->and((float) ($record?->amount ?? 0))->toBe(12000.0)
        ->and($record?->status)->toBe('completed');
});
