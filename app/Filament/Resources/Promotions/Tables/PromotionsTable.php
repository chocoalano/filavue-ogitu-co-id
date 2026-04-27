<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use App\Support\Media\PublicMediaUrl;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromotionsTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    private static function showOnOptions(): array
    {
        $known = [
            'homepage' => 'homepage',
            'product' => 'product',
            'cart' => 'cart',
            'checkout' => 'checkout',
            'all' => 'all',
        ];

        $fromData = Promotion::query()
            ->whereNotNull('show_on')
            ->where('show_on', '!=', '')
            ->distinct()
            ->orderBy('show_on')
            ->pluck('show_on', 'show_on')
            ->all();

        return array_merge($known, $fromData);
    }

    private static function resolveWindowStatus(object $record): string
    {
        if (! $record->is_active) {
            return 'Nonaktif';
        }

        if (! $record->start_at || ! $record->end_at) {
            return 'Tanpa Jadwal';
        }

        $now = now();

        if ($record->start_at > $now) {
            return 'Akan Datang';
        }

        if ($record->end_at < $now) {
            return 'Berakhir';
        }

        return 'Berjalan';
    }

    private static function statusColor(string $status): string
    {
        return match ($status) {
            'Berjalan' => 'success',
            'Akan Datang' => 'warning',
            'Berakhir' => 'danger',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('products'))
            ->defaultSort('priority')
            ->columns([
                self::hiddenByDefault(
                    TextColumn::make('id')
                        ->label('ID')
                        ->numeric()
                        ->sortable()
                ),

                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->copyable()
                    ->summarize(
                        Count::make()
                            ->label('Total Promo')
                            ->numeric()
                    )
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Promo')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->name)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'flash_sale' => 'danger',
                        'bundle' => 'info',
                        default => 'warning',
                    })
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('window_status')
                    ->label('Status Waktu')
                    ->state(fn ($record): string => self::resolveWindowStatus($record))
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state)),

                TextColumn::make('products_count')
                    ->label('Jumlah Produk')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('show_on')
                    ->label('Tampil Di')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                self::hiddenByDefault(
                    ImageColumn::make('image')
                        ->label('Gambar')
                        ->state(function (object $record): ?string {
                            $imageUrl = PublicMediaUrl::resolve($record->image);

                            if (! filled($imageUrl)) {
                                return null;
                            }

                            if (
                                str_starts_with($imageUrl, 'http://')
                                || str_starts_with($imageUrl, 'https://')
                                || str_starts_with($imageUrl, 'data:')
                            ) {
                                return $imageUrl;
                            }

                            return url($imageUrl);
                        })
                ),

                TextColumn::make('start_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('end_at')
                    ->label('Berakhir')
                    ->dateTime()
                    ->sortable(),

                self::hiddenByDefault(
                    TextColumn::make('landing_slug')
                        ->label('Landing Slug')
                        ->placeholder('-')
                        ->searchable()
                ),

                self::hiddenByDefault(
                    TextColumn::make('max_redemption')
                        ->label('Max Redemption')
                        ->numeric()
                        ->sortable()
                        ->placeholder('-')
                ),

                self::hiddenByDefault(
                    TextColumn::make('per_user_limit')
                        ->label('Per User Limit')
                        ->numeric()
                        ->sortable()
                        ->placeholder('-')
                ),

                self::hiddenByDefault(
                    TextColumn::make('page')
                        ->label('Halaman Target')
                        ->placeholder('-')
                        ->searchable()
                ),

                self::hiddenByDefault(
                    TextColumn::make('created_at')
                        ->label('Dibuat')
                        ->dateTime()
                        ->sortable()
                ),

                self::hiddenByDefault(
                    TextColumn::make('updated_at')
                        ->label('Diperbarui')
                        ->dateTime()
                        ->sortable()
                ),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Promo')
                    ->options([
                        'bundle' => 'Bundle',
                        'flash_sale' => 'Flash Sale',
                        'discount' => 'Discount',
                    ])
                    ->placeholder('Semua tipe'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->where('is_active', true),
                        false: fn (Builder $q): Builder => $q->where('is_active', false),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                SelectFilter::make('show_on')
                    ->label('Area Tampil')
                    ->options(fn (): array => self::showOnOptions())
                    ->searchable()
                    ->placeholder('Semua area'),

                TernaryFilter::make('has_products')
                    ->label('Terkait Produk')
                    ->placeholder('Semua')
                    ->trueLabel('Ada produk')
                    ->falseLabel('Tanpa produk')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->whereHas('products'),
                        false: fn (Builder $q): Builder => $q->whereDoesntHave('products'),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                Filter::make('keyword')
                    ->label('Cari Kode/Nama')
                    ->schema([
                        TextInput::make('q')
                            ->label('Kata Kunci')
                            ->placeholder('Kode promo, nama promo, landing slug')
                            ->maxLength(255),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        if ($keyword === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($keyword): void {
                            $q->where('code', 'like', '%'.$keyword.'%')
                                ->orWhere('name', 'like', '%'.$keyword.'%')
                                ->orWhere('landing_slug', 'like', '%'.$keyword.'%');
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        return $keyword !== ''
                            ? [Indicator::make("Kata kunci: {$keyword}")->removeField('q')]
                            : [];
                    }),

                Filter::make('priority_range')
                    ->label('Rentang Prioritas')
                    ->schema([
                        TextInput::make('min')->label('Min')->numeric()->placeholder('0'),
                        TextInput::make('max')->label('Max')->numeric()->placeholder('100'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $min = ($data['min'] ?? '') !== '' ? (int) $data['min'] : null;
                        $max = ($data['max'] ?? '') !== '' ? (int) $data['max'] : null;

                        return $query
                            ->when($min !== null, fn (Builder $q): Builder => $q->where('priority', '>=', $min))
                            ->when($max !== null, fn (Builder $q): Builder => $q->where('priority', '<=', $max));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (($data['min'] ?? '') !== '') {
                            $indicators[] = Indicator::make('Prioritas min: '.$data['min'])->removeField('min');
                        }

                        if (($data['max'] ?? '') !== '') {
                            $indicators[] = Indicator::make('Prioritas max: '.$data['max'])->removeField('max');
                        }

                        return $indicators;
                    }),

                Filter::make('window_status')
                    ->label('Status Waktu')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'running' => 'Berjalan',
                                'upcoming' => 'Akan Datang',
                                'ended' => 'Berakhir',
                                'nonactive' => 'Nonaktif',
                                'unscheduled' => 'Tanpa Jadwal',
                            ])
                            ->placeholder('Semua status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['status'] ?? null;
                        $now = now();

                        return match ($status) {
                            'running' => $query
                                ->where('is_active', true)
                                ->whereNotNull('start_at')
                                ->whereNotNull('end_at')
                                ->where('start_at', '<=', $now)
                                ->where('end_at', '>=', $now),
                            'upcoming' => $query
                                ->where('is_active', true)
                                ->whereNotNull('start_at')
                                ->where('start_at', '>', $now),
                            'ended' => $query
                                ->whereNotNull('end_at')
                                ->where('end_at', '<', $now),
                            'nonactive' => $query->where('is_active', false),
                            'unscheduled' => $query->where(function (Builder $q): void {
                                $q->whereNull('start_at')
                                    ->orWhereNull('end_at');
                            }),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): array {
                        $status = $data['status'] ?? null;

                        if (! filled($status)) {
                            return [];
                        }

                        $label = match ($status) {
                            'running' => 'Berjalan',
                            'upcoming' => 'Akan Datang',
                            'ended' => 'Berakhir',
                            'nonactive' => 'Nonaktif',
                            'unscheduled' => 'Tanpa Jadwal',
                            default => null,
                        };

                        return $label
                            ? [Indicator::make("Status: {$label}")->removeField('status')]
                            : [];
                    }),

                self::promotionPeriodFilter(),
                self::betweenDateFilter('created_between', 'Tanggal Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Status & Tipe')
                    ->description('Filter status aktif, tipe promo, dan area penayangan.')
                    ->columns(4)
                    ->schema([
                        $filters['type'],
                        $filters['is_active'],
                        $filters['show_on'],
                        $filters['window_status'],
                    ])
                    ->columnSpanFull(),

                Section::make('Produk & Prioritas')
                    ->description('Filter keterkaitan produk, prioritas, dan pencarian teks.')
                    ->columns(6)
                    ->schema([
                        $filters['has_products']->columnSpan(2),
                        $filters['priority_range']->columnSpan(2),
                        $filters['keyword']->columnSpan(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Waktu')
                    ->description('Filter periode mulai, berakhir, dan tanggal data dibuat.')
                    ->columns(6)
                    ->schema([
                        $filters['promotion_period']->columnSpan(4),
                        $filters['created_between']->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function promotionPeriodFilter(): Filter
    {
        return Filter::make('promotion_period')
            ->label('Periode Promo')
            ->schema([
                DateTimePicker::make('from')->label('Dari')->seconds(false),
                DateTimePicker::make('until')->label('Sampai')->seconds(false),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data): Builder {
                $from = $data['from'] ?? null;
                $until = $data['until'] ?? null;

                if (blank($from) && blank($until)) {
                    return $query;
                }

                return $query
                    ->whereNotNull('start_at')
                    ->whereNotNull('end_at')
                    ->when(
                        filled($from),
                        fn (Builder $q): Builder => $q->where('end_at', '>=', $from),
                    )
                    ->when(
                        filled($until),
                        fn (Builder $q): Builder => $q->where('start_at', '<=', $until),
                    );
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make('Periode promo mulai dari '.$data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make('Periode promo sampai '.$data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')->label("Dari {$label}")->seconds(false),
                DateTimePicker::make('until')->label("Sampai {$label}")->seconds(false),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data) use ($column): Builder {
                return $query
                    ->when(filled($data['from'] ?? null), fn (Builder $q): Builder => $q->where($column, '>=', $data['from']))
                    ->when(filled($data['until'] ?? null), fn (Builder $q): Builder => $q->where($column, '<=', $data['until']));
            })
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make("{$label} ≥ {$data['from']}")->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make("{$label} ≤ {$data['until']}")->removeField('until');
                }

                return $indicators;
            });
    }
}
