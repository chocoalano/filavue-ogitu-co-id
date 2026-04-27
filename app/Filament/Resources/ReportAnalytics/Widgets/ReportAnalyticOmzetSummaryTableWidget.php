<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportAnalyticOmzetSummaryTableWidget extends ReportAnalyticTableWidget
{
    public function table(Table $table): Table
    {
        $summary = $this->summary()['omzet_summary'];

        return $this->configureSummaryTable($table)
            ->heading('Summary Omzet Company')
            ->description(sprintf(
                'Plan A %s dan Plan B %s, total omzet %s.',
                $this->formatAmountLabel($summary['plan_a_total']),
                $this->formatAmountLabel($summary['plan_b_total']),
                $this->formatAmountLabel($summary['total']),
            ))
            ->columns([
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('amount')
                    ->label('Omzet (Rp.)')
                    ->alignEnd()
                    ->formatStateUsing(fn (mixed $state): string => $this->formatAmount($state))
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),
            ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function getSummaryRecords(): array
    {
        $summary = $this->summary()['omzet_summary'];

        $records = collect($summary['rows'])
            ->values()
            ->map(fn (array $row, int $index): array => [
                'id' => 'detail-'.$index,
                'row_type' => 'detail',
                'description' => $row['description'],
                'amount' => $row['amount'],
            ]);

        $records->push([
            'id' => 'total',
            'row_type' => 'total',
            'description' => 'Total Omzet',
            'amount' => $summary['total'],
        ]);

        return $records->all();
    }
}
