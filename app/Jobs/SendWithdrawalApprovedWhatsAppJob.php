<?php

namespace App\Jobs;

use App\Models\CustomerWalletTransaction;
use App\Services\QontactService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendWithdrawalApprovedWhatsAppJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $transactionId,
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(QontactService $qontactService): void
    {
        $transaction = CustomerWalletTransaction::query()
            ->with('customer')
            ->whereKey($this->transactionId)
            ->first();

        if (! $transaction) {
            Log::error('Withdrawal approved WhatsApp failed: transaction not found.', [
                'transaction_id' => $this->transactionId,
            ]);

            throw new RuntimeException('Data transaksi withdrawal tidak ditemukan.');
        }

        if (($transaction->type !== 'withdrawal') || ($transaction->status !== 'completed')) {
            Log::error('Withdrawal approved WhatsApp failed: invalid transaction state.', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
            ]);

            throw new RuntimeException('Status transaksi tidak valid untuk kirim notifikasi WhatsApp.');
        }

        $customerName = (string) ($transaction->customer?->name ?? '');
        $customerPhone = (string) ($transaction->customer?->phone ?? '');

        if ($customerName === '' || $customerPhone === '') {
            Log::error('Withdrawal approved WhatsApp failed: customer contact incomplete.', [
                'transaction_id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
            ]);

            throw new RuntimeException('Data kontak customer tidak lengkap untuk kirim notifikasi WhatsApp.');
        }

        Log::info('Withdrawal approved WhatsApp job started.', [
            'transaction_id' => $transaction->id,
            'customer_id' => $transaction->customer_id,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'amount' => $transaction->amount,
        ]);

        $sent = $qontactService->sendWithdrawalApprovedNotification($transaction);

        if (! $sent) {
            Log::error('Withdrawal approved WhatsApp failed: provider send returned false.', [
                'transaction_id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'customer_phone' => $customerPhone,
            ]);

            throw new RuntimeException('Pengiriman notifikasi WhatsApp gagal.');
        }

        Log::info('Withdrawal approved WhatsApp job completed successfully.', [
            'transaction_id' => $transaction->id,
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Withdrawal approved WhatsApp job failed.', [
            'transaction_id' => $this->transactionId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
