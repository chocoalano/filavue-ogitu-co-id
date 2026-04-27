<?php

namespace App\Jobs;

use App\Services\Shipping\ShippingTargetImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessShippingTargetImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1800;

    public function __construct(
        public string $storedFilePath,
        public ?int $initiatorUserId = null,
        public ?string $originalFileName = null,
    ) {
        $this->onConnection('database');
        $this->onQueue('default');
    }

    public function handle(ShippingTargetImportService $importService): void
    {
        if (! Storage::disk('local')->exists($this->storedFilePath)) {
            Log::warning('Shipping target import file not found.', [
                'stored_file_path' => $this->storedFilePath,
                'initiator_user_id' => $this->initiatorUserId,
            ]);

            return;
        }

        $absoluteFilePath = Storage::disk('local')->path($this->storedFilePath);

        try {
            $result = $importService->importFromFile($absoluteFilePath, $this->originalFileName);

            Log::info('Shipping target import completed.', [
                'stored_file_path' => $this->storedFilePath,
                'initiator_user_id' => $this->initiatorUserId,
                'processed_rows' => (int) ($result['processed_rows'] ?? 0),
                'failed_rows' => (int) ($result['failed_rows'] ?? 0),
            ]);
        } finally {
            Storage::disk('local')->delete($this->storedFilePath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Shipping target import job failed.', [
            'stored_file_path' => $this->storedFilePath,
            'initiator_user_id' => $this->initiatorUserId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
