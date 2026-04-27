<?php

namespace App\Filament\Resources\CustomerBonusSponsors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusSponsorsTable
{
    public static function configure(Table $table): Table
    {
        $table->recordTitleAttribute('CustomerBonusSponsor');
        $table->defaultSort('created_at', 'desc');
        $table->modifyQueryUsing(fn (Builder $query): Builder => self::modifyQuery($query));
        $table->columns(self::columns());
        $table->filters(self::filters(), layout: FiltersLayout::AboveContentCollapsible);
        $table->filtersFormColumns(12);
        $table->filtersFormSchema(fn (array $filters): array => self::filtersFormSchema($filters));
        $table->recordActions(self::recordActions());
        $table->toolbarActions(self::toolbarActions());

        return $table;
    }

    private static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'member:id,name,username,ref_code,email',
            'fromMember:id,name,username,ref_code,email',
        ]);
    }

    private static function columns(): array
    {
        return [
            TextColumn::make('member.username')
                ->label('Member username Penerima')
                ->placeholder('-')
                ->searchable()
                ->sortable(),
            TextColumn::make('member.name')
                ->label('Member Penerima')
                ->placeholder('-')
                ->searchable()
                ->sortable(),

            TextColumn::make('member.ref_code')
                ->label('Ref Penerima')
                ->placeholder('-')
                ->toggleable()
                ->searchable(),

            TextColumn::make('fromMember.name')
                ->label('Sumber Bonus')
                ->placeholder('-')
                ->description(fn ($record): ?string => filled($record->fromMember?->username) ? '@'.$record->fromMember->username : null)
                ->searchable()
                ->sortable(),

            TextColumn::make('fromMember.ref_code')
                ->label('Ref Sumber')
                ->placeholder('-')
                ->toggleable()
                ->searchable(),

            TextColumn::make('amount')
                ->label('Nominal Bonus')
                ->money('IDR')
                ->sortable()
                ->summarize(
                    Sum::make()
                        ->label('Total Bonus')
                        ->money('IDR')
                ),

            TextColumn::make('index_value')
                ->label('Nilai Index')
                ->numeric(decimalPlaces: 2)
                ->sortable()
                ->summarize(
                    Average::make()
                        ->label('Rata-rata Index')
                        ->numeric(decimalPlaces: 2)
                ),

            TextColumn::make('status')
                ->label('Status Bonus')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'warning'),

            TextColumn::make('description')
                ->label('Keterangan')
                ->placeholder('-')
                ->limit(60)
                ->searchable(),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('member_id')
                ->label('Member Penerima')
                ->relationship('member', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Semua member'),

            SelectFilter::make('from_member_id')
                ->label('Sumber Bonus')
                ->relationship('fromMember', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Semua sumber'),

            SelectFilter::make('status')
                ->label('Status Bonus')
                ->options(self::statusOptions())
                ->placeholder('Semua status'),

            TernaryFilter::make('has_description')
                ->label('Ada Keterangan')
                ->placeholder('Semua')
                ->trueLabel('Ada keterangan')
                ->falseLabel('Tanpa keterangan')
                ->queries(
                    true: fn (Builder $query): Builder => $query->whereNotNull('description')->where('description', '!=', ''),
                    false: fn (Builder $query): Builder => $query->where(function (Builder $builder): void {
                        $builder->whereNull('description')
                            ->orWhere('description', '=', '');
                    }),
                    blank: fn (Builder $query): Builder => $query,
                ),

            Filter::make('amount_range')
                ->label('Rentang Nominal Bonus')
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
                    ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '>=', $data['min']))
                    ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '<=', $data['max']))
                )
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if (filled($data['min'] ?? null)) {
                        $indicators[] = Indicator::make('Bonus dari Rp'.number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                    }

                    if (filled($data['max'] ?? null)) {
                        $indicators[] = Indicator::make('Bonus sampai Rp'.number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                    }

                    return $indicators;
                }),

            Filter::make('index_range')
                ->label('Rentang Nilai Index')
                ->schema([
                    TextInput::make('min')
                        ->label('Min Index')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('0'),
                    TextInput::make('max')
                        ->label('Max Index')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('∞'),
                ])
                ->columns(2)
                ->query(fn (Builder $query, array $data): Builder => $query
                    ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('index_value', '>=', $data['min']))
                    ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('index_value', '<=', $data['max']))
                )
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if (filled($data['min'] ?? null)) {
                        $indicators[] = Indicator::make('Index dari '.$data['min'])->removeField('min');
                    }

                    if (filled($data['max'] ?? null)) {
                        $indicators[] = Indicator::make('Index sampai '.$data['max'])->removeField('max');
                    }

                    return $indicators;
                }),

            Filter::make('keyword')
                ->label('Kata Kunci')
                ->schema([
                    TextInput::make('q')
                        ->label('Cari Data')
                        ->maxLength(100)
                        ->placeholder('Nama member, ref code, atau keterangan bonus'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $keyword = trim((string) ($data['q'] ?? ''));

                    if ($keyword === '') {
                        return $query;
                    }

                    return $query->where(function (Builder $builder) use ($keyword): void {
                        $builder->where('description', 'like', '%'.$keyword.'%')
                            ->orWhereHas('member', function (Builder $member) use ($keyword): Builder {
                                return $member->where('name', 'like', '%'.$keyword.'%')
                                    ->orWhere('ref_code', 'like', '%'.$keyword.'%')
                                    ->orWhere('email', 'like', '%'.$keyword.'%');
                            })
                            ->orWhereHas('fromMember', function (Builder $fromMember) use ($keyword): Builder {
                                return $fromMember->where('name', 'like', '%'.$keyword.'%')
                                    ->orWhere('ref_code', 'like', '%'.$keyword.'%')
                                    ->orWhere('email', 'like', '%'.$keyword.'%');
                            });
                    });
                })
                ->indicateUsing(function (array $data): array {
                    $keyword = trim((string) ($data['q'] ?? ''));

                    return $keyword !== ''
                        ? [Indicator::make("Kata kunci: {$keyword}")->removeField('q')]
                        : [];
                }),

            self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
        ];
    }

    private static function filtersFormSchema(array $filters): array
    {
        return [
            $filters['member_id']->columnSpan(3),
            $filters['from_member_id']->columnSpan(3),
            $filters['status']->columnSpan(3),
            $filters['has_description']->columnSpan(3),
            $filters['amount_range']->columnSpan(4),
            $filters['index_range']->columnSpan(4),
            $filters['created_between']->columnSpan(4),
            $filters['keyword']->columnSpanFull(),
        ];
    }

    private static function recordActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
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
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
