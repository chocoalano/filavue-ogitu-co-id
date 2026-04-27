<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportAnalyticOrderSummaryTableWidget extends ReportAnalyticTableWidget
{
    public function table(Table $table): Table
    {
        $summary = $this->summary()['order_summary'];

        return $this->configureSummaryTable($table)
            ->heading('Summary Order All')
            ->description(sprintf(
                '%s item terjual dari order valid dengan omzet %s.',
                number_format((int) $summary['total_qty'], 0, ',', '.'),
                $this->formatAmountLabel($summary['total_amount']),
            ))
            ->columns([
                TextColumn::make('number')
                    ->label('#')
                    ->alignCenter()
                    ->state(fn (array $record): string => blank($record['number'] ?? null) ? '' : (string) $record['number'])
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->placeholder('')
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('name')
                    ->label('Item Name')
                    ->wrap()
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter()
                    ->formatStateUsing(fn (mixed $state): string => blank($state) ? '' : number_format((int) $state, 0, ',', '.'))
                    ->color(fn (array $record): ?string => $this->summaryRowColor($record))
                    ->weight(fn (array $record) => $this->summaryRowWeight($record)),

                TextColumn::make('amount')
                    ->label('Amount (Rp)')
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
        $summary = $this->summary()['order_summary'];

        $records = collect($summary['rows'])
            ->filter(fn (array $row): bool => filled($row['sku'] ?? null) || filled($row['name'] ?? null) || filled($row['qty'] ?? null) || filled($row['amount'] ?? null))
            ->values()
            ->map(fn (array $row, int $index): array => [
                'id' => 'detail-'.$index,
                'row_type' => 'detail',
                'number' => $row['number'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'qty' => $row['qty'],
                'amount' => $row['amount'],
            ]);

        $records->push([
            'id' => 'total',
            'row_type' => 'total',
            'number' => '',
            'sku' => '',
            'name' => 'Total',
            'qty' => $summary['total_qty'],
            'amount' => $summary['total_amount'],
        ]);

        return $records->all();
    }
}
