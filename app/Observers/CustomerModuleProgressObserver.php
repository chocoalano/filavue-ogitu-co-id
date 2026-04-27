<?php

namespace App\Observers;

use App\Models\Content;
use App\Models\CustomerContentProgress;
use App\Models\CustomerModuleProgress;

/**
 * Observer untuk CustomerModuleProgress.
 *
 * Setiap kali progres per-modul disimpan atau dihapus,
 * observer ini otomatis menghitung ulang persentase progres
 * kursus (CustomerContentProgress) untuk customer yang bersangkutan.
 */
class CustomerModuleProgressObserver
{
    public function saved(CustomerModuleProgress $moduleProgress): void
    {
        $this->syncCourseProgress($moduleProgress);
    }

    public function deleted(CustomerModuleProgress $moduleProgress): void
    {
        $this->syncCourseProgress($moduleProgress);
    }

    /**
     * Hitung ulang progres kursus berdasarkan modul yang sudah diselesaikan.
     * progress = completed_modules / total_published_modules (0.0000 – 1.0000)
     */
    private function syncCourseProgress(CustomerModuleProgress $moduleProgress): void
    {
        $content = Content::query()->find($moduleProgress->content_id);

        if ($content === null || $content->category_id === null) {
            return;
        }

        $categoryId = (int) $content->category_id;

        $totalModules = Content::query()
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->count();

        if ($totalModules === 0) {
            return;
        }

        $completedModules = CustomerModuleProgress::query()
            ->whereHas('content', fn ($q) => $q->where('category_id', $categoryId)->where('status', 'published'))
            ->where('customer_id', $moduleProgress->customer_id)
            ->where('is_completed', true)
            ->count();

        $calculatedProgress = round($completedModules / $totalModules, 4);

        CustomerContentProgress::query()->updateOrCreate(
            [
                'customer_id' => $moduleProgress->customer_id,
                'content_category_id' => $categoryId,
            ],
            [
                'progress' => $calculatedProgress,
                'content_id' => $moduleProgress->content_id,
                'position_sec' => $moduleProgress->position_sec,
                'last_watched_at' => now(),
            ]
        );
    }
}
