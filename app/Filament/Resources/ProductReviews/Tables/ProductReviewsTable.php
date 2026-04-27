<?php

namespace App\Filament\Resources\ProductReviews\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Average;
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

class ProductReviewsTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    private static function formatNumber(int|float $number, int $precision = 0): string
    {
        return number_format($number, $precision, ',', '.');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'customer:id,name,username,email',
                'product:id,name,sku',
                'orderItem:id,order_id,name,sku,qty',
                'orderItem.order:id,order_no',
            ]))
            ->defaultSort('created_at', 'desc')
            ->columns([
                self::hiddenByDefault(
                    TextColumn::make('id')
                        ->label('ID')
                        ->numeric()
                        ->sortable()
                ),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->description(fn ($record): ?string => filled($record->customer?->username) ? '@'.$record->customer->username : null)
                    ->searchable(),

                self::hiddenByDefault(
                    TextColumn::make('customer.email')
                        ->label('Email Customer')
                        ->placeholder('-')
                        ->searchable()
                ),

                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('orderItem.order.order_no')
                    ->label('No. Order')
                    ->placeholder('-')
                    ->searchable(),

                self::hiddenByDefault(
                    TextColumn::make('orderItem.name')
                        ->label('Nama Item Pesanan')
                        ->placeholder('-')
                        ->searchable()
                ),

                self::hiddenByDefault(
                    TextColumn::make('orderItem.sku')
                        ->label('SKU Item Pesanan')
                        ->placeholder('-')
                        ->searchable()
                ),

                TextColumn::make('rating')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => filled($state) ? $state.'/5' : '-')
                    ->color(fn (?int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->summarize([
                        Average::make()
                            ->label('Rata-rata')
                            ->numeric(decimalPlaces: 2),

                        Summarizer::make('disetujui')
                            ->label('Disetujui')
                            ->using(fn (QueryBuilder $query): string => self::formatNumber((clone $query)->where('is_approved', true)->count())),
                    ]),

                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->title)
                    ->summarize(
                        Count::make()
                            ->label('Total Review')
                            ->numeric()
                    )
                    ->searchable(),

                TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified Purchase')
                    ->boolean(),

                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                self::hiddenByDefault(
                    TextColumn::make('updated_at')
                        ->label('Diperbarui')
                        ->dateTime()
                        ->sortable()
                ),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua customer'),

                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua produk'),

                TernaryFilter::make('has_order_item')
                    ->label('Terkait Item Pesanan')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->whereNotNull('order_item_id'),
                        false: fn (Builder $q): Builder => $q->whereNull('order_item_id'),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                self::ratingRangeFilter(),

                TernaryFilter::make('is_approved')
                    ->label('Status Approval')
                    ->placeholder('Semua')
                    ->trueLabel('Approved')
                    ->falseLabel('Belum approved')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->where('is_approved', true),
                        false: fn (Builder $q): Builder => $q->where('is_approved', false),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                TernaryFilter::make('is_verified_purchase')
                    ->label('Verified Purchase')
                    ->placeholder('Semua')
                    ->trueLabel('Verified')
                    ->falseLabel('Non-verified')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->where('is_verified_purchase', true),
                        false: fn (Builder $q): Builder => $q->where('is_verified_purchase', false),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                Filter::make('keyword')
                    ->label('Kata Kunci')
                    ->schema([
                        TextInput::make('q')
                            ->label('Cari')
                            ->placeholder('Judul, komentar, nama customer, nama produk')
                            ->maxLength(255),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        if ($keyword === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($keyword): void {
                            $q->where('title', 'like', '%'.$keyword.'%')
                                ->orWhere('comment', 'like', '%'.$keyword.'%')
                                ->orWhereHas('customer', fn (Builder $customer): Builder => $customer->where('name', 'like', '%'.$keyword.'%'))
                                ->orWhereHas('product', fn (Builder $product): Builder => $product->where('name', 'like', '%'.$keyword.'%'));
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        return $keyword !== ''
                            ? [Indicator::make("Kata kunci: {$keyword}")->removeField('q')]
                            : [];
                    }),

                self::betweenDateFilter('created_between', 'Tanggal Dibuat', 'created_at'),
                self::betweenDateFilter('updated_between', 'Tanggal Diperbarui', 'updated_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Relasi')
                    ->description('Filter berdasarkan customer, produk, dan keterkaitan item pesanan.')
                    ->columns(6)
                    ->schema([
                        $filters['customer_id']->columnSpan(2),
                        $filters['product_id']->columnSpan(2),
                        $filters['has_order_item']->columnSpan(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Rating & Moderasi')
                    ->description('Filter kualitas review dan status verifikasi/approval.')
                    ->columns(6)
                    ->schema([
                        $filters['rating_range']->columnSpan(2),
                        $filters['is_approved']->columnSpan(2),
                        $filters['is_verified_purchase']->columnSpan(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Pencarian & Waktu')
                    ->description('Cari berdasarkan kata kunci dan rentang waktu data.')
                    ->columns(6)
                    ->schema([
                        $filters['keyword']->columnSpanFull(),

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

    private static function ratingRangeFilter(): Filter
    {
        return Filter::make('rating_range')
            ->label('Rentang Rating')
            ->schema([
                TextInput::make('min')
                    ->label('Min')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->placeholder('1'),
                TextInput::make('max')
                    ->label('Max')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->placeholder('5'),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data): Builder {
                $min = ($data['min'] ?? '') !== '' ? (int) $data['min'] : null;
                $max = ($data['max'] ?? '') !== '' ? (int) $data['max'] : null;

                return $query
                    ->when($min !== null, fn (Builder $q): Builder => $q->where('rating', '>=', $min))
                    ->when($max !== null, fn (Builder $q): Builder => $q->where('rating', '<=', $max));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if (($data['min'] ?? '') !== '') {
                    $indicators[] = Indicator::make('Rating min: '.$data['min'])->removeField('min');
                }

                if (($data['max'] ?? '') !== '') {
                    $indicators[] = Indicator::make('Rating max: '.$data['max'])->removeField('max');
                }

                return $indicators;
            });
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')->label('Dari')->seconds(false),
                DateTimePicker::make('until')->label('Sampai')->seconds(false),
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
