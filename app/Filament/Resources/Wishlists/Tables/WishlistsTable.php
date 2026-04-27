<?php

namespace App\Filament\Resources\Wishlists\Tables;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class WishlistsTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    private static function formatNumber(int|float $n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }

    /**
     * Total item wishlist pada hasil query terfilter.
     * (Akurat walau items_count adalah alias)
     */
    private static function countItemsForFilteredWishlists(Builder|QueryBuilder $query): int
    {
        return WishlistItem::query()
            ->whereIn('wishlist_id', (clone $query)->reorder()->select('id'))
            ->count();
    }

    private static function countDistinctCustomers(Builder|QueryBuilder $query): int
    {
        return (clone $query)
            ->reorder()
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('customer:id,name,username')
                ->withCount('items')
                ->withMax('items', 'created_at') // alias: items_max_created_at
            )
            ->defaultSort('updated_at', 'desc')
            ->columns([
                self::hiddenByDefault(
                    TextColumn::make('id')
                        ->label('ID')
                        ->numeric()
                        ->sortable()
                ),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('-')
                    ->description(fn ($record): ?string => filled($record->customer?->username) ? '@'.$record->customer->username : null)
                    ->searchable()
                    ->sortable(),

                self::hiddenByDefault(
                    TextColumn::make('customer_id')
                        ->label('Customer ID')
                        ->numeric()
                        ->sortable()
                ),

                TextColumn::make('name')
                    ->label('Nama Wishlist')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name)
                    ->summarize([
                        Count::make()
                            ->label('Total wishlist')
                            ->numeric(),

                        Summarizer::make('customer_unik')
                            ->label('Customer unik')
                            ->using(fn (QueryBuilder $query): string => self::formatNumber(self::countDistinctCustomers($query))),

                        Summarizer::make('total_item_wishlist')
                            ->label('Total item wishlist')
                            ->using(fn (QueryBuilder $query): string => self::formatNumber(self::countItemsForFilteredWishlists($query))),

                        Summarizer::make('rata_rata_item')
                            ->label('Rata-rata item / wishlist')
                            ->using(function (QueryBuilder $query): string {
                                $totalWishlists = (clone $query)->reorder()->count('id');
                                if ($totalWishlists <= 0) {
                                    return '0';
                                }

                                $totalItems = self::countItemsForFilteredWishlists($query);
                                $avg = $totalItems / $totalWishlists;

                                // tampilkan 2 desimal biar enak dibaca
                                return number_format($avg, 2, ',', '.');
                            }),
                    ]),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('items_max_created_at')
                    ->label('Item Terakhir Ditambahkan')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

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
                // ====== Customer & Wishlist ======
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua customer'),

                Filter::make('wishlist_name')
                    ->label('Nama Wishlist')
                    ->schema([
                        TextInput::make('q')
                            ->label('Kata kunci')
                            ->placeholder('Contoh: Favorit / Harus dibeli / Wishlist 2026')
                            ->maxLength(200),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['q'] ?? null),
                            fn (Builder $q) => $q->where('name', 'like', '%'.trim((string) $data['q']).'%')
                        )
                    )
                    ->indicateUsing(function (array $data): array {
                        $q = trim((string) ($data['q'] ?? ''));

                        return $q !== ''
                            ? [Indicator::make("Nama: {$q}")->removeField('q')]
                            : [];
                    }),

                // ====== Items ======
                TernaryFilter::make('has_items')
                    ->label('Berisi Item')
                    ->placeholder('Semua')
                    ->trueLabel('Ada item')
                    ->falseLabel('Kosong')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('items'),
                        false: fn (Builder $q) => $q->whereDoesntHave('items'),
                        blank: fn (Builder $q) => $q,
                    ),

                Filter::make('items_count_range')
                    ->label('Rentang Jumlah Item')
                    ->schema([
                        TextInput::make('min')
                            ->label('Min')
                            ->numeric()
                            ->placeholder('0'),
                        TextInput::make('max')
                            ->label('Max')
                            ->numeric()
                            ->placeholder('10'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $min = ($data['min'] ?? '') !== '' ? (int) $data['min'] : null;
                        $max = ($data['max'] ?? '') !== '' ? (int) $data['max'] : null;

                        return $query
                            ->when($min !== null, fn (Builder $q) => $q->has('items', '>=', $min))
                            ->when($max !== null, fn (Builder $q) => $q->has('items', '<=', $max));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (($data['min'] ?? '') !== '') {
                            $indicators[] = Indicator::make('Min item: '.$data['min'])->removeField('min');
                        }
                        if (($data['max'] ?? '') !== '') {
                            $indicators[] = Indicator::make('Max item: '.$data['max'])->removeField('max');
                        }

                        return $indicators;
                    }),

                Filter::make('product_keyword')
                    ->label('Produk di Dalam Wishlist')
                    ->schema([
                        TextInput::make('q')
                            ->label('Nama / SKU Produk')
                            ->placeholder('Contoh: matcha / ABC-123')
                            ->maxLength(200),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['q'] ?? null),
                            fn (Builder $q) => $q->whereHas('items', function (Builder $items) use ($data) {
                                $keyword = trim((string) $data['q']);

                                $items->where(function (Builder $w) use ($keyword) {
                                    $w->where('product_name', 'like', "%{$keyword}%")
                                        ->orWhere('product_sku', 'like', "%{$keyword}%");
                                });
                            })
                        )
                    )
                    ->indicateUsing(function (array $data): array {
                        $q = trim((string) ($data['q'] ?? ''));

                        return $q !== ''
                            ? [Indicator::make("Produk: {$q}")->removeField('q')]
                            : [];
                    }),

                // ====== Waktu ======
                Filter::make('created_between')
                    ->label('Tanggal Dibuat')
                    ->schema([
                        DateTimePicker::make('from')->label('(Dibuat) Dari')->seconds(false),
                        DateTimePicker::make('until')->label('(Dibuat) Sampai')->seconds(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $q) => $q->where('created_at', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $q) => $q->where('created_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Dibuat ≥ '.$data['from'])->removeField('from');
                        }
                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Dibuat ≤ '.$data['until'])->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('updated_between')
                    ->label('Tanggal Diperbarui')
                    ->schema([
                        DateTimePicker::make('from')->label('(Diperbarui) Dari')->seconds(false),
                        DateTimePicker::make('until')->label('(Diperbarui) Sampai')->seconds(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $q) => $q->where('updated_at', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $q) => $q->where('updated_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Diperbarui ≥ '.$data['from'])->removeField('from');
                        }
                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Diperbarui ≤ '.$data['until'])->removeField('until');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Customer & Wishlist')
                    ->description('Filter berdasarkan pemilik dan nama wishlist.')
                    ->columns(6)
                    ->schema([
                        $filters['customer_id']->columnSpan(3),
                        $filters['wishlist_name']->columnSpan(3),
                    ])
                    ->columnSpanFull(),

                Section::make('Item Wishlist')
                    ->description('Filter berdasarkan isi wishlist dan produk di dalamnya.')
                    ->columns(6)
                    ->schema([
                        $filters['has_items']->columnSpan(2),
                        $filters['items_count_range']->columnSpan(2),
                        $filters['product_keyword']->columnSpan(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Waktu')
                    ->description('Filter berdasarkan tanggal dibuat dan diperbarui.')
                    ->columns(6)
                    ->schema([
                        $filters['created_between']->columnSpan(3),
                        $filters['updated_between']->columnSpan(3),
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
}
