<?php

use App\Jobs\SendWithdrawalApprovedWhatsAppJob;
use App\Models\CustomerWalletTransaction;
use App\Services\QontactService;
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
        $table->string('phone')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->string('status')->nullable();
        $table->timestamps();
    });
});

it('sends whatsapp notification for completed withdrawal', function (): void {
    seedCompletedWithdrawalTransaction(9101, 8101, 10000, '081234567890');

    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldReceive('sendWithdrawalApprovedNotification')
        ->once()
        ->with(Mockery::on(function ($transaction): bool {
            return $transaction instanceof CustomerWalletTransaction
                && (int) $transaction->id === 9101;
        }))
        ->andReturnTrue();

    $job = new SendWithdrawalApprovedWhatsAppJob(9101);
    $job->handle($qontact);

    expect(true)->toBeTrue();
});

it('throws when whatsapp provider returns false', function (): void {
    seedCompletedWithdrawalTransaction(9102, 8102, 10000, '081234567890');

    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldReceive('sendWithdrawalApprovedNotification')
        ->once()
        ->andReturnFalse();

    $job = new SendWithdrawalApprovedWhatsAppJob(9102);

    expect(fn (): mixed => $job->handle($qontact))
        ->toThrow(RuntimeException::class, 'Pengiriman notifikasi WhatsApp gagal.');
});

it('throws when customer contact is incomplete', function (): void {
    seedCompletedWithdrawalTransaction(9103, 8103, 10000, null);

    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldNotReceive('sendWithdrawalApprovedNotification');

    $job = new SendWithdrawalApprovedWhatsAppJob(9103);

    expect(fn (): mixed => $job->handle($qontact))
        ->toThrow(RuntimeException::class, 'Data kontak customer tidak lengkap untuk kirim notifikasi WhatsApp.');
});

it('throws when transaction status is not completed', function (): void {
    DB::table('customers')->insert([
        'id' => 8104,
        'name' => 'Customer 8104',
        'phone' => '081234567890',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_wallet_transactions')->insert([
        'id' => 9104,
        'customer_id' => 8104,
        'type' => 'withdrawal',
        'amount' => 10000,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $qontact = Mockery::mock(QontactService::class);
    $qontact->shouldNotReceive('sendWithdrawalApprovedNotification');

    $job = new SendWithdrawalApprovedWhatsAppJob(9104);

    expect(fn (): mixed => $job->handle($qontact))
        ->toThrow(RuntimeException::class, 'Status transaksi tidak valid untuk kirim notifikasi WhatsApp.');
});

function seedCompletedWithdrawalTransaction(int $transactionId, int $customerId, float $amount, ?string $phone): void
{
    DB::table('customers')->insert([
        'id' => $customerId,
        'name' => 'Customer '.$customerId,
        'phone' => $phone,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_wallet_transactions')->insert([
        'id' => $transactionId,
        'customer_id' => $customerId,
        'type' => 'withdrawal',
        'amount' => $amount,
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
