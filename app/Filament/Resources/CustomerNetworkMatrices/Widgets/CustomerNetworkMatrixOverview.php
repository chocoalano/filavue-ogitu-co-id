<?php

namespace App\Filament\Resources\CustomerNetworkMatrices\Widgets;

use App\Models\CustomerNetworkMatrix;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerNetworkMatrixOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Sponsor/Matrix Jaringan';

    protected ?string $description = 'Ringkasan relasi sponsor dan distribusi level matrix.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Relasi Matrix', $this->formatNumber($data['totalRecords']))
                ->description('Relasi level 1: ' . $this->formatNumber($data['directCount']))
                ->descriptionIcon('heroicon-m-share', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-share')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Sponsor Unik', $this->formatNumber($data['uniqueSponsors']))
                ->description('Tanpa sponsor: ' . $this->formatNumber($data['withoutSponsorCount']))
                ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-user-group')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Member Unik', $this->formatNumber($data['uniqueMembers']))
                ->description('Rasio member/sponsor: ' . $this->formatNumber($data['memberSponsorRatio'], 2))
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Kedalaman Matrix', $this->formatNumber($data['maxLevel']))
                ->description('Rata-rata level: ' . $this->formatNumber($data['averageLevel'], 2))
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chart-bar')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalRecords: int, directCount: int, uniqueSponsors: int, withoutSponsorCount: int, uniqueMembers: int, memberSponsorRatio: float, averageLevel: float, maxLevel: int, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $query = CustomerNetworkMatrix::query();
        $uniqueSponsors = (clone $query)->whereNotNull('sponsor_id')->distinct()->count('sponsor_id');

        return [
            'totalRecords' => (clone $query)->count(),
            'directCount' => (clone $query)->where('level', 1)->count(),
            'uniqueSponsors' => $uniqueSponsors,
            'withoutSponsorCount' => (clone $query)->whereNull('sponsor_id')->count(),
            'uniqueMembers' => (clone $query)->whereNotNull('member_id')->distinct()->count('member_id'),
            'memberSponsorRatio' => $uniqueSponsors > 0
                ? (float) ((clone $query)->whereNotNull('member_id')->distinct()->count('member_id') / $uniqueSponsors)
                : 0.0,
            'averageLevel' => (float) ((clone $query)->avg('level') ?? 0),
            'maxLevel' => (int) ((clone $query)->max('level') ?? 0),
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
        $dailyCounts = CustomerNetworkMatrix::query()
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
