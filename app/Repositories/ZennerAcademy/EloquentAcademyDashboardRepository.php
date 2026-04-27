<?php

namespace App\Repositories\ZennerAcademy;

use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerContentProgress;
use App\Repositories\ZennerAcademy\Contracts\AcademyDashboardRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAcademyDashboardRepository implements AcademyDashboardRepositoryInterface
{
    /** @return Collection<int, ContentCategory> */
    public function getOrderedCategories(): Collection
    {
        return ContentCategory::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon_key', 'accent_hex', 'sort_order']);
    }

    public function getLatestProgress(Customer $customer): ?CustomerContentProgress
    {
        return CustomerContentProgress::query()
            ->with([
                'course:id,name,slug,thumbnail_url',
                'module:id,category_id,title,slug,content_type,thumbnail_url,duration_sec',
            ])
            ->where('customer_id', $customer->id)
            ->whereNotNull('last_watched_at')
            ->orderByDesc('last_watched_at')
            ->first();
    }

    public function countUnreadNotifications(Customer $customer): int
    {
        return $customer->unreadNotifications()->count();
    }
}
