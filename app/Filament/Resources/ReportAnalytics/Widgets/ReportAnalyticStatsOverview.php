<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportAnalyticStatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Insight Laporan Analitik';

    protected ?string $description = 'Ringkasan order valid, omzet, bonus rilis, dan payout bonus perusahaan.';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $summary = ReportAnalyticWorkbookData::build();
        $orderSummary = $summary['order_summary'];
        $omzetSummary = $summary['omzet_summary'];
        $bonusSummary = $summary['bonus_summary'];
        $planABonus = (float) ($bonusSummary['planA']['bonus_total'] ?? 0);
        $planBBonus = (float) ($bonusSummary['planB']['bonus_total'] ?? 0);
        $totalBonus = $planABonus + $planBBonus;
        $combinedPayout = ReportAnalyticWorkbookData::percentage($totalBonus, (float) ($omzetSummary['total'] ?? 0));

        return [
            Stat::make('Qty Order Valid', number_format((int) ($orderSummary['total_qty'] ?? 0), 0, ',', '.'))
                ->description('Omzet order valid: '.$this->formatAmountLabel((float) ($orderSummary['total_amount'] ?? 0)))
                ->descriptionIcon('heroicon-m-shopping-bag', IconPosition::Before)
                ->icon('heroicon-o-shopping-bag')
                ->chart($this->normalizeChart($this->orderAmountChart($orderSummary['rows'] ?? [])))
                ->color('primary')
                ->extraAttributes(['class' => 'border-t-4 border-t-primary-600/80']),

            Stat::make('Total Omzet', $this->formatAmountLabel((float) ($omzetSummary['total'] ?? 0)))
                ->description(sprintf(
                    'Plan A %s | Plan B %s',
                    $this->formatAmountLabel((float) ($omzetSummary['plan_a_total'] ?? 0)),
                    $this->formatAmountLabel((float) ($omzetSummary['plan_b_total'] ?? 0)),
                ))
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->icon('heroicon-o-banknotes')
                ->chart($this->normalizeChart($this->omzetChart($omzetSummary['rows'] ?? [])))
                ->color('primary')
                ->extraAttributes(['class' => 'border-t-4 border-t-primary-600/80']),

            Stat::make('Total Bonus Rilis', $this->formatAmountLabel($totalBonus))
                ->description(sprintf(
                    'Plan A %s | Plan B %s',
                    $this->formatAmountLabel($planABonus),
                    $this->formatAmountLabel($planBBonus),
                ))
                ->descriptionIcon('heroicon-m-gift', IconPosition::Before)
                ->icon('heroicon-o-gift')
                ->chart($this->normalizeChart([$planABonus, $planBBonus]))
                ->color('primary')
                ->extraAttributes(['class' => 'border-t-4 border-t-primary-600/80']),

            Stat::make('Payout Bonus', $this->formatPercent($combinedPayout))
                ->description(sprintf(
                    'Plan A %s | Plan B %s',
                    $this->formatPercent((float) ($bonusSummary['planA']['payout_total'] ?? 0)),
                    $this->formatPercent((float) ($bonusSummary['planB']['payout_total'] ?? 0)),
                ))
                ->descriptionIcon('heroicon-m-calculator', IconPosition::Before)
                ->icon('heroicon-o-scale')
                ->chart($this->normalizeChart([
                    (float) ($bonusSummary['planA']['payout_total'] ?? 0),
                    (float) ($bonusSummary['planB']['payout_total'] ?? 0),
                    $combinedPayout,
                ]))
                ->color('primary')
                ->extraAttributes(['class' => 'border-t-4 border-t-primary-600/80']),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<int, float>
     */
    private function orderAmountChart(array $rows): array
    {
        return collect($rows)
            ->filter(fn (array $row): bool => filled($row['name'] ?? null) && ($row['name'] !== 'Total'))
            ->map(fn (array $row): float => (float) ($row['amount'] ?? 0))
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<int, float>
     */
    private function omzetChart(array $rows): array
    {
        return collect($rows)
            ->map(fn (array $row): float => (float) ($row['amount'] ?? 0))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, float|int>  $values
     * @return array<int, float>
     */
    private function normalizeChart(array $values): array
    {
        $chart = collect($values)
            ->map(fn (float|int $value): float => round((float) $value, 2))
            ->values();

        if ($chart->isEmpty()) {
            return [0.0, 0.0];
        }

        if ($chart->count() === 1) {
            return [0.0, (float) $chart->first()];
        }

        return $chart->all();
    }

    private function formatAmountLabel(float|int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function formatPercent(float|int $percent): string
    {
        return number_format($percent, 2, ',', '.').'%';
    }
}
