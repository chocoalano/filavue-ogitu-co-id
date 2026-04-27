<?php

namespace App\Filament\Resources\Wishlists\Widgets;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class WishlistOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Wishlist';

    protected ?string $description = 'Ringkasan wishlist, item, dan customer aktif.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $wishlistQuery = Wishlist::query();

        $totalWishlists = (clone $wishlistQuery)->count();
        $wishlistsWithItems = (clone $wishlistQuery)->has('items')->count();
        $emptyWishlists = max($totalWishlists - $wishlistsWithItems, 0);

        $totalItems = WishlistItem::query()->count();
        $uniqueCustomers = (clone $wishlistQuery)
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        $averageItemsPerWishlist = $totalWishlists > 0
            ? $totalItems / $totalWishlists
            : 0;

        $averageItemsPerActiveWishlist = $wishlistsWithItems > 0
            ? $totalItems / $wishlistsWithItems
            : 0;

        $chart = $this->getLast7DaysWishlistTrend();

        return [
            Stat::make('Total Wishlist', $this->formatNumber($totalWishlists))
                ->description('Berisi item: ' . $this->formatNumber($wishlistsWithItems) . ' | Kosong: ' . $this->formatNumber($emptyWishlists))
                ->descriptionIcon('heroicon-m-bookmark', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-bookmark')
                ->chart($chart)
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Customer Unik', $this->formatNumber($uniqueCustomers))
                ->description('Wishlist per customer (rata-rata): ' . $this->formatNumber($uniqueCustomers > 0 ? $totalWishlists / $uniqueCustomers : 0, 2))
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Total Item Wishlist', $this->formatNumber($totalItems))
                ->description('Rata-rata per wishlist: ' . $this->formatNumber($averageItemsPerWishlist, 2))
                ->descriptionIcon('heroicon-m-squares-plus', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-squares-plus')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Kepadatan Wishlist Aktif', $this->formatNumber($averageItemsPerActiveWishlist, 2))
                ->description('Rata-rata item pada wishlist yang tidak kosong')
                ->descriptionIcon('heroicon-m-chart-bar-square', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chart-bar-square')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysWishlistTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = Wishlist::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn (mixed $total): int => (int) $total);

        return collect(range(0, 6))
            ->map(
                fn (int $dayOffset): int => $dailyCounts->get(
                    now()->subDays(6 - $dayOffset)->toDateString(),
                    0,
                ),
            )
            ->all();
    }

    protected function formatNumber(float|int $number, int $precision = 0): string
    {
        return number_format($number, $precision, ',', '.');
    }
}
