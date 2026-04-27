<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportAnalyticBonusPlanBTableWidget extends ReportAnalyticTableWidget
{
    public function table(Table $table): Table
    {
        $plan = $this->summary()['bonus_summary']['planB'];

        return $this->configureSummaryTable($table)
            ->heading('Summary Bonus Plan B')
            ->description(sprintf(
                'Retail Plan dengan total bonus %s dan payout %s.',
                $this->formatAmountLabel($plan['bonus_total']),
                $this->formatPercent($plan['payout_total']),
            ))
            ->columns([
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('amount')
                    ->label('Amount (Rp.)')
                    ->alignEnd()
                    ->formatStateUsing(fn (mixed $state): string => $this->formatAmount($state))
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('bonus_percent')
                    ->label('Bonus (%)')
                    ->alignEnd()
                    ->formatStateUsing(fn (mixed $state): string => $this->formatPercent($state))
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('payout_percent')
                    ->label('Payout (%)')
                    ->alignEnd()
                    ->formatStateUsing(fn (mixed $state): string => $this->formatPercent($state))
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),
            ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function getSummaryRecords(): array
    {
        $plan = $this->summary()['bonus_summary']['planB'];

        $records = collect($plan['rows'])
            ->values()
            ->map(fn (array $row, int $index): array => [
                'id' => 'detail-'.$index,
                'row_type' => 'detail',
                'description' => $row['description'],
                'amount' => $row['amount'],
                'bonus_percent' => $row['bonus_percent'],
                'payout_percent' => $row['payout_percent'],
            ]);

        $records->push([
            'id' => 'total',
            'row_type' => 'total',
            'description' => 'Total Bonus',
            'amount' => $plan['bonus_total'],
            'bonus_percent' => $plan['bonus_total'] > 0 ? 100 : 0,
            'payout_percent' => null,
        ]);

        $records->push([
            'id' => 'payout',
            'row_type' => 'payout',
            'description' => 'Payout Bonus (Total Bonus / Total Omzet * 100)',
            'amount' => null,
            'bonus_percent' => null,
            'payout_percent' => $plan['payout_total'],
        ]);

        return $records->all();
    }
}
