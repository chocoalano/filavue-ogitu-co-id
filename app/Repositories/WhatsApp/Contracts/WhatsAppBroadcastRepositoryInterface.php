<?php

namespace App\Repositories\WhatsApp\Contracts;

use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use Illuminate\Support\Collection;

interface WhatsAppBroadcastRepositoryInterface
{
    public function findById(int $broadcastId): ?WhatsAppBroadcast;

    public function markProcessing(WhatsAppBroadcast $broadcast): void;

    public function markFailed(WhatsAppBroadcast $broadcast, string $errorMessage): void;

    /**
     * @return Collection<int, object{id:int,name:string,phone:string}>
     */
    public function getCustomersWithPhone(): Collection;

    public function countRecipients(int $broadcastId): int;

    /**
     * @param  list<array{
     *     customer_id:int,
     *     customer_name:string,
     *     phone:string,
     *     normalized_phone:string
     * }>  $recipients
     */
    public function replaceRecipients(int $broadcastId, array $recipients): void;

    /**
     * @return Collection<int, WhatsAppBroadcastRecipient>
     */
    public function getPendingRecipients(int $broadcastId): Collection;

    public function markRecipientProcessing(int $recipientId): void;

    public function markRecipientSent(int $recipientId, ?string $responseMessage = null): void;

    public function markRecipientFailed(int $recipientId, string $errorMessage): void;

    /**
     * @return array{total:int,success:int,failed:int,pending:int}
     */
    public function summarizeRecipientStats(int $broadcastId): array;

    public function markCompleted(
        WhatsAppBroadcast $broadcast,
        int $totalRecipients,
        int $successRecipients,
        int $failedRecipients,
        ?string $lastError = null,
    ): void;
}
