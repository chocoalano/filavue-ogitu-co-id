<?php

namespace App\Filament\Resources\CustomerWalletTransactions\Widgets;

use App\Models\CustomerWalletTransaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerWalletTransactionOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Transaksi E-Wallet';

    protected ?string $description = 'Ringkasan aktivitas mutasi saldo wallet customer.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Transaksi', $this->formatNumber($data['totalCount']))
                ->description('Selesai: ' . $this->formatNumber($data['completedCount']) . ' | Pending: ' . $this->formatNumber($data['pendingCount']))
                ->descriptionIcon('heroicon-m-arrow-path', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Nominal Selesai', $this->formatCurrencyIdr($data['completedAmount']))
                ->description('Rata-rata per transaksi: ' . $this->formatCurrencyIdr($data['completedAverage']))
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Transaksi Pending', $this->formatNumber($data['pendingCount']))
                ->description('Nilai tertahan: ' . $this->formatCurrencyIdr($data['pendingAmount']))
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Topup 30 Hari', $this->formatNumber($data['topup30Count']))
                ->description('Nominal: ' . $this->formatCurrencyIdr($data['topup30Amount']))
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-arrow-trending-up')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalCount: int, completedCount: int, pendingCount: int, completedAmount: float, completedAverage: float, pendingAmount: float, topup30Count: int, topup30Amount: float, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $query = CustomerWalletTransaction::query();
        $last30Days = now()->subDays(29)->startOfDay();

        $completedCount = (clone $query)->where('status', 'completed')->count();

        return [
            'totalCount' => (clone $query)->count(),
            'completedCount' => $completedCount,
            'pendingCount' => (clone $query)->where('status', 'pending')->count(),
            'completedAmount' => (float) (clone $query)->where('status', 'completed')->sum('amount'),
            'completedAverage' => $completedCount > 0
                ? (float) ((clone $query)->where('status', 'completed')->avg('amount') ?? 0)
                : 0.0,
            'pendingAmount' => (float) (clone $query)->where('status', 'pending')->sum('amount'),
            'topup30Count' => (clone $query)->where('type', 'topup')->where('created_at', '>=', $last30Days)->count(),
            'topup30Amount' => (float) (clone $query)->where('type', 'topup')->where('status', 'completed')->where('created_at', '>=', $last30Days)->sum('amount'),
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
        $dailyCounts = CustomerWalletTransaction::query()
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
}
