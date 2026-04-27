<?php

namespace App\Filament\Resources\Rewards\Widgets;

use App\Models\Reward;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class RewardOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Reward';

    protected ?string $description = 'Snapshot performa reward periode dan permanen.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Reward Master', $this->formatNumber($data['totalRecords']))
                ->description('Total nilai reward: ' . $this->formatCurrencyIdr($data['totalValue']))
                ->descriptionIcon('heroicon-m-sparkles', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-star')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Reward Aktif', $this->formatNumber($data['activeCount']))
                ->description(
                    $this->formatPercent($data['activePercentage']) .
                    ' aktif dari total reward'
                )
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-sparkles')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Reward Periode Berjalan', $this->formatNumber($data['runningPeriodCount']))
                ->description(
                    'Upcoming: ' . $this->formatNumber($data['upcomingPeriodCount']) .
                    ' | Permanen: ' . $this->formatNumber($data['lifetimeCount'])
                )
                ->descriptionIcon('heroicon-m-calendar-days', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-calendar')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Rata-rata Nilai Reward', $this->formatCurrencyIdr($data['averageValue']))
                ->description('Reward tertinggi: ' . $this->formatCurrencyIdr($data['highestValue']))
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chart-bar-square')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalRecords: int, totalValue: float, activeCount: int, activePercentage: float, runningPeriodCount: int, upcomingPeriodCount: int, lifetimeCount: int, averageValue: float, highestValue: float, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $rewardQuery = Reward::query();
        $today = today();
        $totalRecords = (clone $rewardQuery)->count();
        $activeCount = (clone $rewardQuery)->where('status', 1)->count();

        return [
            'totalRecords' => $totalRecords,
            'totalValue' => (float) (clone $rewardQuery)->sum('value'),
            'activeCount' => $activeCount,
            'activePercentage' => $totalRecords > 0 ? ($activeCount / $totalRecords) * 100 : 0.0,
            'runningPeriodCount' => (clone $rewardQuery)
                ->where('type', 0)
                ->where('status', 1)
                ->whereNotNull('start')
                ->whereNotNull('end')
                ->whereDate('start', '<=', $today)
                ->whereDate('end', '>=', $today)
                ->count(),
            'upcomingPeriodCount' => (clone $rewardQuery)
                ->where('type', 0)
                ->where('status', 1)
                ->whereNotNull('start')
                ->whereDate('start', '>', $today)
                ->count(),
            'lifetimeCount' => (clone $rewardQuery)->where('type', 1)->count(),
            'averageValue' => (float) (clone $rewardQuery)->avg('value'),
            'highestValue' => (float) (clone $rewardQuery)->max('value'),
            'chart' => $this->getLast7DaysTrend(),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = Reward::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereNotNull('created_at')
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

    protected function formatCurrencyIdr(float|int $number): string
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }

    protected function formatPercent(float $number): string
    {
        return number_format($number, 1, ',', '.') . '%';
    }
}
