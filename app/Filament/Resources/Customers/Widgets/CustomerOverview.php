<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use App\Models\CustomerWhatsAppConfirmation;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Data Member';

    protected ?string $description = 'Ringkasan statistik member terdaftar.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Member', $this->formatNumber($data['totalCount']))
                ->description('Aktif: '.$this->formatNumber($data['activeCount']).' | Pasif/Prospek: '.$this->formatNumber($data['inactiveCount']))
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Member Aktif', $this->formatNumber($data['activeCount']))
                ->description('Status aktif (terverifikasi admin)')
                ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-circle')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('WA Terkonfirmasi', $this->formatNumber($data['waConfirmedCount']))
                ->description('Dari '.$this->formatNumber($data['totalCount']).' member terdaftar')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Member Baru 30 Hari', $this->formatNumber($data['newCount']))
                ->description('Pendaftaran 30 hari terakhir')
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-arrow-trending-up')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalCount: int, activeCount: int, inactiveCount: int, waConfirmedCount: int, newCount: int, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $totalCount = Customer::query()->count();
        $activeCount = Customer::query()->where('status', 3)->count();

        $waConfirmedCount = CustomerWhatsAppConfirmation::query()
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();

        return [
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => max(0, $totalCount - $activeCount),
            'waConfirmedCount' => $waConfirmedCount,
            'newCount' => Customer::query()->where('created_at', '>=', now()->subDays(29)->startOfDay())->count(),
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
        $dailyCounts = Customer::query()
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
}
