<?php

namespace App\Filament\Resources\Rewards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Reward')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama Reward')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reward')
                    ->label('Hadiah')
                    ->placeholder('-')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('value')
                    ->label('Nilai Reward')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Nilai')
                            ->money('IDR')
                    ),

                TextColumn::make('bv')
                    ->label('BV')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total BV')
                            ->numeric(decimalPlaces: 2)
                    ),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): string => self::typeOptions()[(int) $state] ?? '-')
                    ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                    ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'danger'),

                TextColumn::make('start')
                    ->label('Periode Mulai')
                    ->date()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('end')
                    ->label('Periode Selesai')
                    ->date()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Reward')
                    ->options(self::typeOptions())
                    ->placeholder('Semua tipe'),

                SelectFilter::make('status')
                    ->label('Status Reward')
                    ->options(self::statusOptions())
                    ->placeholder('Semua status'),

                Filter::make('value_range')
                    ->label('Rentang Nilai Reward')
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
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('value', '>=', $data['min']))
                            ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('value', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Nilai dari Rp' . number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Nilai sampai Rp' . number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                Filter::make('bv_range')
                    ->label('Rentang BV')
                    ->schema([
                        TextInput::make('min')
                            ->label('Min BV')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0'),
                        TextInput::make('max')
                            ->label('Max BV')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('bv', '>=', $data['min']))
                            ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('bv', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('BV dari ' . number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('BV sampai ' . number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                self::betweenDateFilter('start_between', 'Periode Mulai', 'start'),
                self::betweenDateFilter('end_between', 'Periode Selesai', 'end'),
                self::betweenDateFilter('created_between', 'Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['type']->columnSpan(2),
                $filters['status']->columnSpan(2),
                $filters['value_range']->columnSpan(4),
                $filters['bv_range']->columnSpan(4),
                $filters['start_between']->columnSpan(4),
                $filters['end_between']->columnSpan(4),
                $filters['created_between']->columnSpan(4),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DatePicker::make('from')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->placeholder('Awal periode'),
                DatePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->placeholder('Akhir periode'),
            ])
            ->columns(2)
            ->query(
                fn (Builder $query, array $data): Builder => $query
                    ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->whereDate($column, '>=', $data['from']))
                    ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->whereDate($column, '<=', $data['until']))
            )
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make($label . ' dari ' . $data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label . ' sampai ' . $data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function typeOptions(): array
    {
        return [
            0 => 'Periode',
            1 => 'Permanen',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Tidak Aktif',
            1 => 'Aktif',
        ];
    }
}
