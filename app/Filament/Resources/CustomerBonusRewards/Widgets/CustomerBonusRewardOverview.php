<?php

namespace App\Filament\Resources\CustomerBonusRewards\Widgets;

use App\Models\CustomerBonusReward;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CustomerBonusRewardOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Bonus Reward';

    protected ?string $description = 'Ringkasan distribusi dan status bonus reward member.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = Cache::remember(
            CustomerBonusReward::CACHE_KEY_OVERVIEW,
            now()->addMinutes(10),
            fn (): array => $this->computeStats(),
        );

        return [
            Stat::make('Total Bonus Reward', $this->formatNumber($data['totalRecords']))
                ->description('Akumulasi nominal: ' . $this->formatCurrencyIdr($data['totalAmount']))
                ->descriptionIcon('heroicon-m-trophy', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-trophy')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Status Menunggu', $this->formatNumber($data['pendingCount']))
                ->description('Nilai menunggu: ' . $this->formatCurrencyIdr($data['pendingAmount']))
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Status Dirilis', $this->formatNumber($data['releasedCount']))
                ->description('Nilai dirilis: ' . $this->formatCurrencyIdr($data['releasedAmount']))
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Rata-rata Reward', $this->formatCurrencyIdr($data['averageAmount']))
                ->description('Total BV: ' . $this->formatNumber($data['totalBv'], 2))
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chart-bar')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalRecords: int, totalAmount: float, pendingCount: int, pendingAmount: float, releasedCount: int, releasedAmount: float, averageAmount: float, totalBv: float, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $bonusQuery = CustomerBonusReward::query();

        return [
            'totalRecords' => (clone $bonusQuery)->count(),
            'totalAmount' => (float) (clone $bonusQuery)->sum('amount'),
            'pendingCount' => (clone $bonusQuery)->where('status', 0)->count(),
            'pendingAmount' => (float) (clone $bonusQuery)->where('status', 0)->sum('amount'),
            'releasedCount' => (clone $bonusQuery)->where('status', 1)->count(),
            'releasedAmount' => (float) (clone $bonusQuery)->where('status', 1)->sum('amount'),
            'averageAmount' => (float) (clone $bonusQuery)->avg('amount'),
            'totalBv' => (float) (clone $bonusQuery)->sum('bv'),
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
        $dailyCounts = CustomerBonusReward::query()
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

    protected function formatCurrencyIdr(float|int $number): string
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}
