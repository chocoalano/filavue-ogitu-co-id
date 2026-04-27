<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

abstract class ReportAnalyticTableWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function configureSummaryTable(Table $table): Table
    {
        return $table
            ->records(fn (): array => $this->getSummaryRecords())
            ->paginated(false)
            ->striped()
            ->recordAction(null)
            ->recordClasses(fn (array $record): array => match ($record['row_type'] ?? 'detail') {
                'total', 'payout' => [
                    'border-s-2 border-primary-500/70 bg-primary-50/70 dark:border-primary-400/60 dark:bg-primary-500/10',
                ],
                default => [],
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(): array
    {
        return ReportAnalyticWorkbookData::build();
    }

    protected function summaryRowWeight(array $record): FontWeight|string|null
    {
        return in_array($record['row_type'] ?? 'detail', ['total', 'payout'], true)
            ? FontWeight::Bold
            : null;
    }

    protected function summaryRowColor(array $record): ?string
    {
        return in_array($record['row_type'] ?? 'detail', ['total', 'payout'], true)
            ? 'primary'
            : null;
    }

    protected function formatAmount(float|int|string|null $amount): string
    {
        if (($amount === null) || ($amount === '')) {
            return '';
        }

        return ReportAnalyticWorkbookData::formatAmount((float) $amount);
    }

    protected function formatPercent(float|int|string|null $percent): string
    {
        if (($percent === null) || ($percent === '')) {
            return '';
        }

        return ReportAnalyticWorkbookData::formatPercent((float) $percent);
    }

    protected function formatAmountLabel(float|int|string|null $amount): string
    {
        return 'Rp '.$this->formatAmount($amount);
    }

    /**
     * @return list<array<string, mixed>>
     */
    abstract protected function getSummaryRecords(): array;
}
