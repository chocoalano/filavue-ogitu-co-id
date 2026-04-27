<?php

namespace App\Filament\Resources\CustomerNetworkMatrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerNetworkMatricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'member:id,name,username,ref_code,email',
                'sponsor:id,name,username,ref_code,email',
            ]))
            ->columns([
                TextColumn::make('member.username')
                    ->label('Member username Penerima')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.name')
                    ->label('Member')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.ref_code')
                    ->label('Kode Member')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('sponsor.name')
                    ->label('Sponsor')
                    ->placeholder('-')
                    ->description(fn ($record): ?string => filled($record->sponsor?->username) ? '@'.$record->sponsor->username : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sponsor.ref_code')
                    ->label('Kode Sponsor')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('level')
                    ->label('Level')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(60)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('member_id')
                    ->label('Member')
                    ->relationship('member', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua member'),

                SelectFilter::make('sponsor_id')
                    ->label('Sponsor')
                    ->relationship('sponsor', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua sponsor'),

                TernaryFilter::make('has_sponsor')
                    ->label('Ketersediaan Sponsor')
                    ->placeholder('Semua')
                    ->trueLabel('Ada sponsor')
                    ->falseLabel('Tanpa sponsor')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('sponsor_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('sponsor_id'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Filter::make('level_range')
                    ->label('Rentang Level')
                    ->schema([
                        TextInput::make('min')
                            ->label('Min Level')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1'),
                        TextInput::make('max')
                            ->label('Max Level')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('level', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('level', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Level dari '.$data['min'])->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Level sampai '.$data['max'])->removeField('max');
                        }

                        return $indicators;
                    }),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['member_id']->columnSpan(3),
                $filters['sponsor_id']->columnSpan(3),
                $filters['has_sponsor']->columnSpan(2),
                $filters['level_range']->columnSpan(4),
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
}
