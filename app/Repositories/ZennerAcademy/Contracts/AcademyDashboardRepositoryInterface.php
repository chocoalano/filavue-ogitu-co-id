<?php

namespace App\Repositories\ZennerAcademy\Contracts;

use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerContentProgress;
use Illuminate\Support\Collection;

interface AcademyDashboardRepositoryInterface
{
    /**
     * Daftar kategori terurut berdasarkan sort_order untuk ditampilkan di dashboard.
     *
     * @return Collection<int, ContentCategory>
     */
    public function getOrderedCategories(): Collection;

    /**
     * Progres kursus terakhir yang aktif dari customer.
     * Diambil berdasarkan last_watched_at terbaru.
     */
    public function getLatestProgress(Customer $customer): ?CustomerContentProgress;

    /**
     * Jumlah notifikasi Laravel yang belum dibaca milik customer.
     */
    public function countUnreadNotifications(Customer $customer): int;
}
