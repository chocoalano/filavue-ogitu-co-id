<?php

namespace App\Support\Orders;

use App\Models\Order;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderTabCountsCache
{
    public const CACHE_KEY = 'filament:orders:tab-counts';

    private const CACHE_TTL_MINUTES = 15;

    /**
     * @var array<string, array<int, string>>
     */
    private const TAB_STATUSES = [
        'pending' => ['pending'],
        'paid' => ['paid', 'processing'],
        'shipped' => ['shipped'],
        'delivered' => ['delivered'],
        'cancelled' => ['cancelled', 'canceled'],
    ];

    /**
     * @return array<string, int>
     */
    public static function counts(): array
    {
        /** @var array<string, int> $counts */
        $counts = self::cache()->remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => self::queryCounts(),
        );

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    public static function refresh(): array
    {
        $counts = self::queryCounts();

        self::cache()->put(
            self::CACHE_KEY,
            $counts,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
        );

        return $counts;
    }

    /**
     * @return array<int, string>
     */
    public static function statusesForTab(?string $tab): array
    {
        if ($tab === null || $tab === '' || $tab === 'all') {
            return [];
        }

        return self::TAB_STATUSES[$tab] ?? [];
    }

    private static function cache(): Repository
    {
        $stores = config('cache.stores', []);
        $store = app()->runningUnitTests()
            ? (string) config('cache.default', 'array')
            : (array_key_exists('redis', $stores) ? 'redis' : (string) config('cache.default', 'database'));

        return Cache::store($store);
    }

    /**
     * @return array<string, int>
     */
    private static function queryCounts(): array
    {
        $normalizedStatusExpression = "LOWER(TRIM(COALESCE(status, '')))";

        $statusCounts = Order::query()
            ->selectRaw("{$normalizedStatusExpression} as normalized_status, COUNT(*) as aggregate")
            ->groupBy(DB::raw($normalizedStatusExpression))
            ->pluck('aggregate', 'normalized_status')
            ->map(fn (mixed $aggregate): int => (int) $aggregate)
            ->all();

        $counts = [
            'all' => array_sum($statusCounts),
        ];

        foreach (self::TAB_STATUSES as $tab => $statuses) {
            $counts[$tab] = array_sum(
                array_map(
                    fn (string $status): int => $statusCounts[$status] ?? 0,
                    $statuses,
                ),
            );
        }

        return $counts;
    }
}
