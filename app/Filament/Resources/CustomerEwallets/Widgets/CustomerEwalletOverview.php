<?php

namespace App\Filament\Resources\CustomerEwallets\Widgets;

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerEwalletOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight E-Wallet';

    protected ?string $description = 'Ringkasan saldo, bonus, dan aktivitas transaksi wallet.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Member Wallet Aktif', $this->formatNumber($data['walletMemberCount']))
                ->description('Total member: ' . $this->formatNumber($data['totalMembers']))
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->chart($data['membersChart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Total Saldo E-Wallet', $this->formatCurrencyIdr($data['totalWalletBalance']))
                ->description('Rata-rata/member: ' . $this->formatCurrencyIdr($data['averageWalletBalance']))
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Bonus Pending', $this->formatCurrencyIdr($data['totalPendingBonus']))
                ->description('Bonus processed: ' . $this->formatCurrencyIdr($data['totalProcessedBonus']))
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-calculator')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Transaksi 7 Hari', $this->formatNumber($data['transactions7Days']))
                ->description('Nominal completed: ' . $this->formatCurrencyIdr($data['completedAmount7Days']))
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->chart($data['transactionsChart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalMembers: int, walletMemberCount: int, totalWalletBalance: float, averageWalletBalance: float, totalPendingBonus: float, totalProcessedBonus: float, transactions7Days: int, completedAmount7Days: float, membersChart: array<int, float>, transactionsChart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $customersQuery = Customer::query();
        $transactionsQuery = CustomerWalletTransaction::query();
        $lastSevenDays = now()->subDays(6)->startOfDay();
        $walletMemberCount = (clone $customersQuery)->whereNotNull('ewallet_id')->count();

        return [
            'totalMembers' => (clone $customersQuery)->count(),
            'walletMemberCount' => $walletMemberCount,
            'totalWalletBalance' => (float) (clone $customersQuery)->sum('ewallet_saldo'),
            'averageWalletBalance' => $walletMemberCount > 0
                ? (float) ((clone $customersQuery)->whereNotNull('ewallet_id')->avg('ewallet_saldo') ?? 0)
                : 0.0,
            'totalPendingBonus' => (float) (clone $customersQuery)->sum('bonus_pending'),
            'totalProcessedBonus' => (float) (clone $customersQuery)->sum('bonus_processed'),
            'transactions7Days' => (clone $transactionsQuery)->where('created_at', '>=', $lastSevenDays)->count(),
            'completedAmount7Days' => (float) (clone $transactionsQuery)
                ->where('created_at', '>=', $lastSevenDays)
                ->where('status', 'completed')
                ->sum('amount'),
            'membersChart' => $this->getLast14DaysActiveWalletMembersTrend(),
            'transactionsChart' => $this->getLast7DaysTransactionTrend(),
        ];
    }

    /**
     * @return array<int, float>
     */
    protected function getLast14DaysActiveWalletMembersTrend(): array
    {
        $startDate = now()->subDays(13)->startOfDay();

        /** @var Collection<string, int> $dailyActiveMembers */
        $dailyActiveMembers = CustomerWalletTransaction::query()
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT customer_id) as total')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn (mixed $total): int => (int) $total);

        $rawValues = collect(range(0, 13))
            ->map(
                fn (int $dayOffset): float => (float) $dailyActiveMembers->get(
                    now()->subDays(13 - $dayOffset)->toDateString(),
                    0,
                ),
            )->values();

        return $rawValues
            ->map(function (float $value, int $index) use ($rawValues): float {
                $windowStart = max(0, $index - 2);
                $windowLength = $index - $windowStart + 1;

                return round((float) $rawValues->slice($windowStart, $windowLength)->avg(), 2);
            })
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysTransactionTrend(): array
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
