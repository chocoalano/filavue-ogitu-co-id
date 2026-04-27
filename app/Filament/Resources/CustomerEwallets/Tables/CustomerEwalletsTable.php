<?php

namespace App\Filament\Resources\CustomerEwallets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerEwalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('ewallet_saldo', 'desc')
            ->columns([
                TextColumn::make('username')
                    ->label('Username Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ref_code')
                    ->label('Kode Ref')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('ewallet_id')
                    ->label('ID E-Wallet')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('ewallet_saldo')
                    ->label('Saldo E-Wallet')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Saldo')
                            ->money('IDR')
                    ),

                TextColumn::make('bonus_pending')
                    ->label('Bonus Pending')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Pending')
                            ->money('IDR')
                    ),

                TextColumn::make('bonus_processed')
                    ->label('Bonus Diproses')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(
                        Sum::make()
                            ->label('Total Diproses')
                            ->money('IDR')
                    ),

                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('bank_account')
                    ->label('No. Rekening')
                    ->placeholder('-')
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                    ->color(fn (mixed $state): string => match ((int) $state) {
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('has_wallet')
                    ->label('Status Wallet')
                    ->options([
                        'yes' => 'Memiliki E-Wallet',
                        'no' => 'Belum Memiliki E-Wallet',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'yes' => $query->whereNotNull('ewallet_id'),
                        'no' => $query->whereNull('ewallet_id'),
                        default => $query,
                    })
                    ->placeholder('Semua member'),

                SelectFilter::make('status')
                    ->label('Status Customer')
                    ->options(self::statusOptions())
                    ->placeholder('Semua status'),

                Filter::make('saldo_range')
                    ->label('Rentang Saldo E-Wallet')
                    ->schema([
                        TextInput::make('min')
                            ->label('Minimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('0'),
                        TextInput::make('max')
                            ->label('Maksimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('ewallet_saldo', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('ewallet_saldo', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Saldo ≥ Rp'.number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Saldo ≤ Rp'.number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                self::betweenDateFilter('joined_between', 'Periode Bergabung', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['has_wallet']->columnSpan(3),
                $filters['status']->columnSpan(3),
                $filters['saldo_range']->columnSpan(3),
                $filters['joined_between']->columnSpan(3),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([]),
            ]);
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')
                    ->label('Dari Tanggal')
                    ->seconds(false)
                    ->placeholder('Awal periode'),
                DateTimePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->seconds(false)
                    ->placeholder('Akhir periode'),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '>=', $data['from']))
                ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '<=', $data['until']))
            )
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make($label.' dari '.$data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label.' sampai '.$data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function statusOptions(): array
    {
        return [
            1 => 'Prospek',
            2 => 'Pasif',
            3 => 'Aktif',
        ];
    }
}
