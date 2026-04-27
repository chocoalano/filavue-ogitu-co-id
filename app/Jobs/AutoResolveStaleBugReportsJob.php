<?php

namespace App\Jobs;

use App\Enums\BugCommentType;
use App\Enums\BugStatus;
use App\Models\BugReport;
use App\Models\BugReportComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoResolveStaleBugReportsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    /**
     * Aturan auto-resolve:
     * 1. Laporan tanpa komentar sama sekali selama 1 minggu sejak dibuat → resolved
     * 2. Laporan dengan komentar, tapi komentar terakhir sudah 3 hari tanpa tanggapan baru → resolved
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $noCommentCutoff = Carbon::now()->subWeek();
        $lastCommentCutoff = Carbon::now()->subDays(3);

        $activeStatuses = collect(BugStatus::cases())
            ->filter(fn (BugStatus $s) => $s->isActive())
            ->map(fn (BugStatus $s) => $s->value)
            ->all();

        $stale = BugReport::query()
            ->whereIn('status', $activeStatuses)
            ->where(function ($query) use ($noCommentCutoff, $lastCommentCutoff): void {
                // Aturan 1: tidak punya komentar sama sekali dan dibuat > 1 minggu lalu
                $query->where(function ($q) use ($noCommentCutoff): void {
                    $q->whereDoesntHave('comments')
                        ->where('created_at', '<=', $noCommentCutoff);
                });

                // Aturan 2: punya komentar, tapi komentar terakhirnya > 3 hari lalu
                $query->orWhere(function ($q) use ($lastCommentCutoff): void {
                    $q->whereHas('comments', function ($sub) use ($lastCommentCutoff): void {
                        $sub->havingRaw('MAX(created_at) <= ?', [$lastCommentCutoff]);
                    });
                });
            })
            ->get();

        foreach ($stale as $bugReport) {
            /** @var BugReport $bugReport */
            $oldStatus = $bugReport->status->getLabel();
            $hasComments = $bugReport->comments()->exists();
            $reason = $hasComments
                ? 'tidak ada tanggapan baru selama 3 hari sejak komentar terakhir'
                : 'tidak ada komentar sama sekali selama 1 minggu sejak laporan dibuat';

            $bugReport->update([
                'status' => BugStatus::Resolved,
                'resolved_at' => Carbon::now(),
            ]);

            BugReportComment::create([
                'bug_report_id' => $bugReport->id,
                'user_id' => null,
                'type' => BugCommentType::StatusChange,
                'body' => "Status berubah dari **{$oldStatus}** menjadi **Resolved** secara otomatis karena {$reason}.",
                'old_value' => $bugReport->getOriginal('status'),
                'new_value' => BugStatus::Resolved->value,
            ]);

            Log::info('Bug report auto-resolved due to inactivity.', [
                'bug_report_id' => $bugReport->id,
                'title' => $bugReport->title,
                'old_status' => $oldStatus,
                'reason' => $reason,
            ]);
        }

        Log::info('AutoResolveStaleBugReportsJob completed.', [
            'resolved_count' => $stale->count(),
        ]);
    }
}
