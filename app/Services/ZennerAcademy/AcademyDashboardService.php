<?php

namespace App\Services\ZennerAcademy;

use App\Models\ContentCategory;
use App\Models\Customer;
use App\Repositories\ZennerAcademy\Contracts\AcademyDashboardRepositoryInterface;

class AcademyDashboardService
{
    public function __construct(
        protected AcademyDashboardRepositoryInterface $dashboardRepository,
    ) {}

    /**
     * Data lengkap halaman dashboard Zenner Academy.
     *
     * @return array{
     *   hero: array{title: string, subtitle: string, unreadNotifications: int},
     *   continueWatching: array<string, mixed>|null,
     *   categories: list<array<string, mixed>>
     * }
     */
    public function getPageData(Customer $customer): array
    {
        return [
            'hero' => $this->buildHero($customer),
            'continueWatching' => $this->buildContinueWatching($customer),
            'categories' => $this->buildCategories(),
        ];
    }

    /**
     * Bagian hero: judul, subjudul, dan notifikasi belum dibaca.
     *
     * @return array{title: string, subtitle: string, unreadNotifications: int}
     */
    private function buildHero(Customer $customer): array
    {
        return [
            'title' => 'Pusat Edukasi',
            'subtitle' => 'Belajar lebih cepat dengan materi singkat & terstruktur.',
            'unreadNotifications' => $this->dashboardRepository->countUnreadNotifications($customer),
        ];
    }

    /**
     * Bagian "Lanjutkan Menonton" berdasarkan kursus terakhir yang diakses.
     * Mengembalikan null jika customer belum memiliki progres apapun.
     *
     * @return array<string, mixed>|null
     */
    private function buildContinueWatching(Customer $customer): ?array
    {
        $progress = $this->dashboardRepository->getLatestProgress($customer);

        if ($progress === null || $progress->course === null) {
            return null;
        }

        $course = $progress->course;
        $module = $progress->module;

        $courseSlug = $course->slug;
        $moduleSlug = $module?->slug ?? '';

        return [
            'courseId' => 'crs_'.$courseSlug,
            'courseTitle' => $course->name,
            'moduleId' => $module !== null ? 'mod_'.$module->id : null,
            'moduleTitle' => $module?->title,
            'progress' => (float) $progress->progress,
            'resume' => $module !== null ? [
                'contentType' => $module->content_type ?? 'video',
                'positionSec' => $progress->position_sec,
                'durationSec' => $module->duration_sec ?? 0,
            ] : null,
            'thumbnailUrl' => $course->thumbnail_url ?? $module?->thumbnail_url,
            'action' => [
                'label' => 'Lanjutkan',
                'deeplink' => "app://education/course/{$courseSlug}/module/{$moduleSlug}",
            ],
        ];
    }

    /**
     * Daftar kategori Zenner Academy yang tampil di UI.
     *
     * @return list<array{id: string, label: string, iconKey: string|null, accentHex: string|null, sort: int}>
     */
    private function buildCategories(): array
    {
        return $this->dashboardRepository
            ->getOrderedCategories()
            ->map(fn (ContentCategory $category): array => [
                'id' => 'cat_'.$category->slug,
                'label' => $category->name,
                'iconKey' => $category->icon_key,
                'accentHex' => $category->accent_hex,
                'sort' => $category->sort_order,
            ])
            ->values()
            ->all();
    }
}
