<?php

namespace App\Filament\Resources\Carts\Tables;

use App\Models\Cart;
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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartsTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    private static function currencyOptions(): array
    {
        return Cart::query()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency', 'currency')
            ->all();
    }

    /**
     * Summary multi-currency: "IDR 1.234.000 | USD 120.500"
     */
    private static function summarizeMoneyByCurrency(Builder $query, string $amountColumn): string
    {
        $rows = (clone $query)
            ->reorder()
            ->selectRaw("currency, SUM({$amountColumn}) as total")
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        if ($rows->isEmpty()) {
            return '—';
        }

        return $rows
            ->map(function ($row) {
                $currency = $row->currency ?: '—';
                $total = (float) ($row->total ?? 0);
                $formatted = number_format($total, 0, ',', '.');

                return "{$currency} {$formatted}";
            })
            ->implode(' | ');
    }

    /**
     * Summary uang yang aman:
     * - Single currency => Sum biasa
     * - Multi currency => breakdown per currency
     */
    private static function moneySummaries(string $column, string $label = 'Total'): array
    {
        return [
            Sum::make()
                ->label($label)
                ->numeric(decimalPlaces: 0)
                ->visible(fn (Builder $query): bool => (clone $query)->distinct()->count('currency') <= 1),

            Summarizer::make("{$column}_by_currency")
                ->label("{$label} per currency")
                ->visible(fn (Builder $query): bool => (clone $query)->distinct()->count('currency') > 1)
                ->using(fn (Builder $query): string => self::summarizeMoneyByCurrency($query, $column)),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('customer:id,name,username')
                ->withCount('items')
                ->withSum('items', 'qty')
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
                    ->placeholder('Guest')
                    ->description(fn ($record): ?string => filled($record->customer?->username) ? '@'.$record->customer->username : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('session_id')
                    ->label('Session')
                    ->placeholder('-')
                    ->searchable()
                    ->limit(18)
                    ->tooltip(fn ($record) => $record->session_id)
                    ->copyable()
                    ->summarize(Count::make()->label('Carts')->numeric()),

                TextColumn::make('currency')
                    ->label('Mata Uang')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

                TextColumn::make('items_sum_qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

                TextColumn::make('subtotal_amount')
                    ->label('Subtotal')
                    ->money(fn ($record) => $record->currency ?? 'IDR')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(self::moneySummaries('subtotal_amount')),

                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money(fn ($record) => $record->currency ?? 'IDR')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(self::moneySummaries('discount_amount')),

                TextColumn::make('shipping_amount')
                    ->label('Ongkir')
                    ->money(fn ($record) => $record->currency ?? 'IDR')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(self::moneySummaries('shipping_amount')),

                TextColumn::make('tax_amount')
                    ->label('Pajak')
                    ->money(fn ($record) => $record->currency ?? 'IDR')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(self::moneySummaries('tax_amount')),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money(fn ($record) => $record->currency ?? 'IDR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->summarize(self::moneySummaries('grand_total')),

                TextColumn::make('applied_promos')
                    ->label('Promo')
                    ->badge()
                    ->formatStateUsing(static function ($state): string {
                        $count = is_array($state) ? count($state) : 0;

                        return $count > 0 ? "{$count} promo" : '—';
                    })
                    ->color(static function ($state): string {
                        $count = is_array($state) ? count($state) : 0;

                        return $count > 0 ? 'success' : 'gray';
                    })
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
                // === customer / session / currency ===
                TernaryFilter::make('has_customer')
                    ->label('Tipe Keranjang')
                    ->placeholder('Semua')
                    ->trueLabel('Customer')
                    ->falseLabel('Guest')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('customer_id'),
                        false: fn (Builder $q) => $q->whereNull('customer_id'),
                        blank: fn (Builder $q) => $q,
                    ),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua customer'),

                SelectFilter::make('currency')
                    ->label('Mata Uang')
                    ->searchable()
                    ->placeholder('Semua mata uang')
                    ->options(fn (): array => self::currencyOptions()),

                Filter::make('session_like')
                    ->label('Session Contains')
                    ->schema([
                        TextInput::make('q')
                            ->label('Session ID')
                            ->placeholder('Ketik sebagian session id...')
                            ->maxLength(255),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['q'] ?? null),
                            fn (Builder $q) => $q->where('session_id', 'like', '%'.trim((string) $data['q']).'%')
                        )
                    )
                    ->indicateUsing(function (array $data): array {
                        $q = trim((string) ($data['q'] ?? ''));

                        return $q !== ''
                            ? [Indicator::make("Session: {$q}")->removeField('q')]
                            : [];
                    }),

                // === items / promo ===
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
                    ->label('Rentang Items')
                    ->schema([
                        TextInput::make('min')->label('Min')->numeric()->placeholder('0'),
                        TextInput::make('max')->label('Max')->numeric()->placeholder('10'),
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
                            $indicators[] = Indicator::make('Min Items: '.$data['min'])->removeField('min');
                        }
                        if (($data['max'] ?? '') !== '') {
                            $indicators[] = Indicator::make('Max Items: '.$data['max'])->removeField('max');
                        }

                        return $indicators;
                    }),

                TernaryFilter::make('has_promos')
                    ->label('Ada Promo')
                    ->placeholder('Semua')
                    ->trueLabel('Ada promo')
                    ->falseLabel('Tanpa promo')
                    ->queries(
                        true: fn (Builder $q) => $q->whereJsonLength('applied_promos', '>', 0),
                        false: fn (Builder $q) => $q->where(function (Builder $qq) {
                            $qq->whereNull('applied_promos')
                                ->orWhereJsonLength('applied_promos', '=', 0);
                        }),
                        blank: fn (Builder $q) => $q,
                    ),

                Filter::make('promo_code')
                    ->label('Promo Code')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Promo')
                            ->placeholder('Contoh: NEWUSER / RAMADAN')
                            ->maxLength(100),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['code'] ?? null),
                            fn (Builder $q) => $q->whereJsonContains('applied_promos', trim((string) $data['code']))
                        )
                    )
                    ->indicateUsing(function (array $data): array {
                        $code = trim((string) ($data['code'] ?? ''));

                        return $code !== ''
                            ? [Indicator::make("Promo: {$code}")->removeField('code')]
                            : [];
                    }),

                // === ranges for ALL amount columns ===
                self::rangeMoneyFilter('subtotal_range', 'Rentang Subtotal', 'subtotal_amount'),
                self::rangeMoneyFilter('discount_range', 'Rentang Diskon', 'discount_amount'),
                self::rangeMoneyFilter('shipping_range', 'Rentang Ongkir', 'shipping_amount'),
                self::rangeMoneyFilter('tax_range', 'Rentang Pajak', 'tax_amount'),
                self::rangeMoneyFilter('grand_total_range', 'Rentang Grand Total', 'grand_total'),

                // === time ===
                self::betweenDateFilter('created_between', 'Tanggal Dibuat', 'created_at'),
                self::betweenDateFilter('updated_between', 'Tanggal Diperbarui', 'updated_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Identitas')
                    ->description('Customer, session, dan mata uang.')
                    ->columns(6)
                    ->schema([
                        $filters['has_customer']->columnSpan(2),
                        $filters['customer_id']->columnSpan(2),
                        $filters['currency']->columnSpan(2),

                        $filters['session_like']->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Items & Promo')
                    ->description('Filter isi keranjang dan promo.')
                    ->columns(6)
                    ->schema([
                        $filters['has_items']->columnSpan(2),
                        $filters['items_count_range']->columnSpan(2),
                        $filters['has_promos']->columnSpan(2),

                        $filters['promo_code']->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Nilai')
                    ->description('Filter semua nilai amount (subtotal, diskon, ongkir, pajak, total).')
                    ->columns(6)
                    ->schema([
                        $filters['subtotal_range']->columnSpan(3),
                        $filters['discount_range']->columnSpan(3),

                        $filters['shipping_range']->columnSpan(3),
                        $filters['tax_range']->columnSpan(3),

                        $filters['grand_total_range']->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Waktu')
                    ->description('Filter tanggal pembuatan & pembaruan.')
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

    private static function rangeMoneyFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                TextInput::make('min')->label('Min')->numeric()->placeholder('0'),
                TextInput::make('max')->label('Max')->numeric()->placeholder('1000000'),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data) use ($column): Builder {
                $min = ($data['min'] ?? '') !== '' ? (float) $data['min'] : null;
                $max = ($data['max'] ?? '') !== '' ? (float) $data['max'] : null;

                return $query
                    ->when($min !== null, fn (Builder $q) => $q->where($column, '>=', $min))
                    ->when($max !== null, fn (Builder $q) => $q->where($column, '<=', $max));
            })
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (($data['min'] ?? '') !== '') {
                    $indicators[] = Indicator::make("{$label} Min: {$data['min']}")->removeField('min');
                }
                if (($data['max'] ?? '') !== '') {
                    $indicators[] = Indicator::make("{$label} Max: {$data['max']}")->removeField('max');
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
                    ->when(filled($data['from'] ?? null), fn (Builder $q) => $q->where($column, '>=', $data['from']))
                    ->when(filled($data['until'] ?? null), fn (Builder $q) => $q->where($column, '<=', $data['until']));
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
