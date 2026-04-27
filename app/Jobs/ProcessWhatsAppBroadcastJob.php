<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsAppBroadcastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppBroadcastJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1200;

    public function __construct(
        public int $broadcastId,
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppBroadcastService $broadcastService): void
    {
        $broadcastService->process($this->broadcastId);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('WhatsApp broadcast job failed.', [
            'broadcast_id' => $this->broadcastId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
