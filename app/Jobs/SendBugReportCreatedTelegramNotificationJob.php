<?php

namespace App\Jobs;

use App\Models\BugReport;
use App\Services\Telegram\BugReportTelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendBugReportCreatedTelegramNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $bugReportId,
    ) {
        $this->onQueue('default');
        $this->afterCommit();
    }

    public function handle(BugReportTelegramNotificationService $telegramService): void
    {
        $bugReport = BugReport::query()
            ->with([
                'assignee:id,name,email',
                'reporterUser:id,name,email',
                'reporterCustomer:id,name,email',
            ])
            ->withCount('attachments')
            ->find($this->bugReportId);

        if (! $bugReport) {
            Log::error('Bug report Telegram notification failed: record not found.', [
                'bug_report_id' => $this->bugReportId,
            ]);

            throw new RuntimeException('Laporan bug tidak ditemukan untuk dikirim ke Telegram.');
        }

        if (! $telegramService->isConfigured()) {
            Log::warning('Bug report Telegram notification skipped: configuration incomplete.', [
                'bug_report_id' => $bugReport->id,
                'reason' => $telegramService->configurationErrorMessage(),
            ]);

            return;
        }

        $telegramService->sendCreatedNotification($bugReport);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Bug report Telegram notification job failed.', [
            'bug_report_id' => $this->bugReportId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
