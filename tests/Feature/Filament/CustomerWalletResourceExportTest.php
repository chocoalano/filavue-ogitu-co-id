<?php

use App\Filament\Resources\CustomerEwallets\Pages\ManageCustomerEwallets;
use App\Filament\Resources\CustomerTopups\Pages\ManageCustomerTopups;
use App\Models\Customer;
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
        $table->string('ref_code')->nullable();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('ewallet_id')->nullable();
        $table->decimal('ewallet_saldo', 16, 2)->default(0);
        $table->decimal('bonus_pending', 16, 2)->default(0);
        $table->decimal('bonus_processed', 16, 2)->default(0);
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
        $table->unsignedTinyInteger('status')->default(1);
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('type');
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

it('applies ewallet export filters by wallet ownership, status, and saldo customer', function (): void {
    DB::table('customers')->insert([
        [
            'id' => 1,
            'ref_code' => 'REF-001',
            'username' => 'alpha',
            'name' => 'Alpha',
            'email' => 'alpha@example.test',
            'password' => 'hashed',
            'ewallet_id' => 'EW-001',
            'ewallet_saldo' => 250000,
            'bonus_pending' => 10000,
            'bonus_processed' => 20000,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'ref_code' => 'REF-002',
            'username' => 'beta',
            'name' => 'Beta',
            'email' => 'beta@example.test',
            'password' => 'hashed',
            'ewallet_id' => 'EW-002',
            'ewallet_saldo' => 5000,
            'bonus_pending' => 0,
            'bonus_processed' => 0,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 3,
            'ref_code' => 'REF-003',
            'username' => 'gamma',
            'name' => 'Gamma',
            'email' => 'gamma@example.test',
            'password' => 'hashed',
            'ewallet_id' => null,
            'ewallet_saldo' => 0,
            'bonus_pending' => 0,
            'bonus_processed' => 0,
            'status' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $query = invokeProtectedStaticMethod(
        ManageCustomerEwallets::class,
        'applyExportFilters',
        [
            Customer::query(),
            [
                'has_wallet' => 'yes',
                'status' => 3,
                'min_wallet_balance' => 100000,
            ],
        ],
    );

    expect($query->pluck('username')->all())->toBe(['alpha']);
});

it('applies topup export filters by type, status, and saldo customer', function (): void {
    DB::table('customers')->insert([
        [
            'id' => 10,
            'ref_code' => 'REF-010',
            'username' => 'topup-a',
            'name' => 'Topup A',
            'email' => 'topup-a@example.test',
            'password' => 'hashed',
            'ewallet_id' => 'EW-010',
            'ewallet_saldo' => 350000,
            'bonus_pending' => 0,
            'bonus_processed' => 0,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 20,
            'ref_code' => 'REF-020',
            'username' => 'topup-b',
            'name' => 'Topup B',
            'email' => 'topup-b@example.test',
            'password' => 'hashed',
            'ewallet_id' => 'EW-020',
            'ewallet_saldo' => 15000,
            'bonus_pending' => 0,
            'bonus_processed' => 0,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    createWalletTransaction([
        'customer_id' => 10,
        'type' => 'topup',
        'amount' => 100000,
        'status' => 'completed',
        'transaction_ref' => 'TOPUP-001',
    ]);

    createWalletTransaction([
        'customer_id' => 20,
        'type' => 'topup',
        'amount' => 50000,
        'status' => 'completed',
        'transaction_ref' => 'TOPUP-002',
    ]);

    createWalletTransaction([
        'customer_id' => 10,
        'type' => 'withdrawal',
        'amount' => 25000,
        'status' => 'completed',
        'transaction_ref' => 'WD-001',
    ]);

    $query = invokeProtectedStaticMethod(
        ManageCustomerTopups::class,
        'applyExportFilters',
        [
            CustomerWalletTransaction::query(),
            [
                'status' => 'completed',
                'min_wallet_balance' => 100000,
            ],
        ],
    );

    expect($query->pluck('transaction_ref')->all())->toBe(['TOPUP-001']);
});

it('defines excel export actions and saldo customer filter hints in both resources', function (): void {
    $ewalletPageSource = file_get_contents(app_path('Filament/Resources/CustomerEwallets/Pages/ManageCustomerEwallets.php'));
    $ewalletExporterSource = file_get_contents(app_path('Filament/Resources/CustomerEwallets/Exports/CustomerEwalletExporter.php'));
    $topupPageSource = file_get_contents(app_path('Filament/Resources/CustomerTopups/Pages/ManageCustomerTopups.php'));
    $topupExporterSource = file_get_contents(app_path('Filament/Resources/CustomerTopups/Exports/CustomerTopupExporter.php'));

    expect($ewalletPageSource)->toBeString()
        ->and($ewalletPageSource)->toContain('ExportAction::make()')
        ->and($ewalletPageSource)->toContain("->label('Download Excel')")
        ->and($ewalletPageSource)->toContain('CustomerEwalletExporter::class')
        ->and($ewalletPageSource)->toContain('applyExportFilters')
        ->and($ewalletExporterSource)->toBeString()
        ->and($ewalletExporterSource)->toContain("TextInput::make('min_wallet_balance')")
        ->and($ewalletExporterSource)->toContain("TextInput::make('max_wallet_balance')")
        ->and($ewalletExporterSource)->toContain('Gunakan filter saldo customer jika export penuh terasa berat.')
        ->and($ewalletExporterSource)->toContain('ExportFormat::Xlsx')
        ->and($topupPageSource)->toBeString()
        ->and($topupPageSource)->toContain('ExportAction::make()')
        ->and($topupPageSource)->toContain("->label('Download Excel')")
        ->and($topupPageSource)->toContain('CustomerTopupExporter::class')
        ->and($topupPageSource)->toContain('applyExportFilters')
        ->and($topupPageSource)->toContain("->where('type', 'topup')")
        ->and($topupExporterSource)->toBeString()
        ->and($topupExporterSource)->toContain("TextInput::make('min_wallet_balance')")
        ->and($topupExporterSource)->toContain("TextInput::make('max_wallet_balance')")
        ->and($topupExporterSource)->toContain('Gunakan filter saldo customer jika export penuh terasa berat.')
        ->and($topupExporterSource)->toContain('ExportFormat::Xlsx');
});

function createWalletTransaction(array $attributes = []): CustomerWalletTransaction
{
    $createdAt = $attributes['created_at'] ?? now();
    $updatedAt = $attributes['updated_at'] ?? $createdAt;

    unset($attributes['created_at'], $attributes['updated_at']);

    $transaction = new CustomerWalletTransaction(array_merge([
        'customer_id' => 1,
        'type' => 'topup',
        'amount' => 0,
        'balance_before' => 0,
        'balance_after' => 0,
        'status' => 'pending',
        'payment_method' => null,
        'transaction_ref' => null,
        'midtrans_transaction_id' => null,
        'notes' => null,
        'completed_at' => null,
        'is_system' => false,
        'midtrans_signature_key' => null,
    ], $attributes));

    $transaction->created_at = $createdAt;
    $transaction->updated_at = $updatedAt;
    $transaction->save();

    return $transaction;
}

function invokeProtectedStaticMethod(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
