<?php

namespace App\Repositories\WhatsApp;

use App\Models\Customer;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentWhatsAppBroadcastRepository implements WhatsAppBroadcastRepositoryInterface
{
    public function findById(int $broadcastId): ?WhatsAppBroadcast
    {
        return WhatsAppBroadcast::query()->find($broadcastId);
    }

    public function markProcessing(WhatsAppBroadcast $broadcast): void
    {
        $broadcast->update([
            'status' => 'processing',
            'last_error' => null,
        ]);
    }

    public function markFailed(WhatsAppBroadcast $broadcast, string $errorMessage): void
    {
        $broadcast->update([
            'status' => 'failed',
            'last_error' => $this->truncateMessage($errorMessage),
            'sent_at' => now(),
        ]);
    }

    public function getCustomersWithPhone(): Collection
    {
        return Customer::query()
            ->select(['id', 'name', 'phone'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->get();
    }

    public function countRecipients(int $broadcastId): int
    {
        return WhatsAppBroadcastRecipient::query()
            ->where('broadcast_id', $broadcastId)
            ->count();
    }

    public function replaceRecipients(int $broadcastId, array $recipients): void
    {
        WhatsAppBroadcastRecipient::query()
            ->where('broadcast_id', $broadcastId)
            ->delete();

        if ($recipients === []) {
            return;
        }

        $timestamp = now();
        $rows = [];

        foreach ($recipients as $recipient) {
            $rows[] = [
                'broadcast_id' => $broadcastId,
                'customer_id' => $recipient['customer_id'],
                'customer_name' => $recipient['customer_name'],
                'phone' => $recipient['phone'],
                'normalized_phone' => $recipient['normalized_phone'],
                'status' => 'queued',
                'response_message' => null,
                'sent_at' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunkRows) {
            WhatsAppBroadcastRecipient::query()->insert($chunkRows);
        }
    }

    public function getPendingRecipients(int $broadcastId): Collection
    {
        return WhatsAppBroadcastRecipient::query()
            ->where('broadcast_id', $broadcastId)
            ->whereIn('status', ['queued', 'processing'])
            ->orderBy('id')
            ->get();
    }

    public function markRecipientProcessing(int $recipientId): void
    {
        WhatsAppBroadcastRecipient::query()
            ->whereKey($recipientId)
            ->update([
                'status' => 'processing',
                'updated_at' => now(),
            ]);
    }

    public function markRecipientSent(int $recipientId, ?string $responseMessage = null): void
    {
        WhatsAppBroadcastRecipient::query()
            ->whereKey($recipientId)
            ->update([
                'status' => 'sent',
                'response_message' => $this->truncateMessage($responseMessage),
                'sent_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function markRecipientFailed(int $recipientId, string $errorMessage): void
    {
        WhatsAppBroadcastRecipient::query()
            ->whereKey($recipientId)
            ->update([
                'status' => 'failed',
                'response_message' => $this->truncateMessage($errorMessage),
                'sent_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function summarizeRecipientStats(int $broadcastId): array
    {
        $baseQuery = WhatsAppBroadcastRecipient::query()
            ->where('broadcast_id', $broadcastId);

        $totalRecipients = (clone $baseQuery)->count();
        $successRecipients = (clone $baseQuery)->where('status', 'sent')->count();
        $failedRecipients = (clone $baseQuery)->where('status', 'failed')->count();

        return [
            'total' => (int) $totalRecipients,
            'success' => (int) $successRecipients,
            'failed' => (int) $failedRecipients,
            'pending' => max(0, (int) $totalRecipients - (int) $successRecipients - (int) $failedRecipients),
        ];
    }

    public function markCompleted(
        WhatsAppBroadcast $broadcast,
        int $totalRecipients,
        int $successRecipients,
        int $failedRecipients,
        ?string $lastError = null,
    ): void {
        $status = 'sent';

        if ($totalRecipients === 0 || ($successRecipients === 0 && $failedRecipients > 0)) {
            $status = 'failed';
        } elseif ($failedRecipients > 0) {
            $status = 'partial';
        }

        $broadcast->update([
            'status' => $status,
            'total_recipients' => $totalRecipients,
            'success_recipients' => $successRecipients,
            'failed_recipients' => $failedRecipients,
            'last_error' => $this->truncateMessage($lastError),
            'sent_at' => now(),
        ]);
    }

    private function truncateMessage(?string $message, int $maxLength = 1000): ?string
    {
        if ($message === null) {
            return null;
        }

        $trimmed = trim($message);
        if ($trimmed === '') {
            return null;
        }

        return mb_substr($trimmed, 0, $maxLength);
    }
}
