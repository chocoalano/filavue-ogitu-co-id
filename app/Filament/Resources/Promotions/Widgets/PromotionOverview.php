<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Promotion;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class PromotionOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Promosi';

    protected ?string $description = 'Ringkasan status aktif, jadwal, dan cakupan produk promosi.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $promotionQuery = Promotion::query();
        $now = now();

        $totalPromotions = (clone $promotionQuery)->count();
        $activeFlagPromotions = (clone $promotionQuery)->where('is_active', true)->count();
        $inactivePromotions = max($totalPromotions - $activeFlagPromotions, 0);

        $runningPromotions = (clone $promotionQuery)
            ->where('is_active', true)
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->count();

        $upcomingPromotions = (clone $promotionQuery)
            ->where('is_active', true)
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->count();

        $expiredPromotions = (clone $promotionQuery)
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->count();

        $promotionsWithProducts = (clone $promotionQuery)->whereHas('products')->count();
        $productCoverageLinks = (clone $promotionQuery)->withCount('products')->get()->sum('products_count');

        $chart = $this->getLast7DaysPromotionTrend();

        return [
            Stat::make('Total Promosi', $this->formatNumber($totalPromotions))
                ->description('Aktif: ' . $this->formatNumber($activeFlagPromotions) . ' | Nonaktif: ' . $this->formatNumber($inactivePromotions))
                ->descriptionIcon('heroicon-m-megaphone', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-megaphone')
                ->chart($chart)
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Sedang Berjalan', $this->formatNumber($runningPromotions))
                ->description('Akan datang: ' . $this->formatNumber($upcomingPromotions))
                ->descriptionIcon('heroicon-m-bolt', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-bolt')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Sudah Berakhir', $this->formatNumber($expiredPromotions))
                ->description('Cek & arsipkan promo yang sudah tidak relevan.')
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Cakupan Produk', $this->formatNumber($productCoverageLinks))
                ->description('Promo dengan produk: ' . $this->formatNumber($promotionsWithProducts))
                ->descriptionIcon('heroicon-m-cube', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-cube')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysPromotionTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = Promotion::query()
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
