<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'media' => fn ($mediaQuery) => $mediaQuery
                    ->select(['id', 'product_id', 'url', 'is_primary', 'sort_order'])
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order'),
            ]))
            ->columns([
                ImageColumn::make('image_preview')
                    ->label('Gambar')
                    ->state(fn (Product $record): array => $record->media
                        ->sortByDesc('is_primary')
                        ->map(fn ($m): ?string => self::resolveImageUrl($m->url))
                        ->filter()
                        ->values()
                        ->all()
                    )
                    ->imageHeight(40)
                    ->stacked()
                    ->limit(5)
                    ->limitedRemainingText()
                    ->circular(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('warranty_months')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('base_price')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weight_gram')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('length_mm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('width_mm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('height_mm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pv')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('b_sponsor')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('b_royalty')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('b_cashback')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Produk')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),

                TernaryFilter::make('stock_status')
                    ->label('Status Stok')
                    ->placeholder('Semua')
                    ->trueLabel('Tersedia')
                    ->falseLabel('Habis')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('stock', '>', 0),
                        false: fn (Builder $query): Builder => $query->where('stock', '<=', 0),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua kategori'),

                SelectFilter::make('promotions')
                    ->label('Promosi')
                    ->relationship('promotions', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua promosi'),

                SelectFilter::make('brand')
                    ->label('Brand')
                    ->placeholder('Semua brand')
                    ->searchable()
                    ->options(fn (): array => Product::query()
                        ->whereNotNull('brand')
                        ->where('brand', '!=', '')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand')
                        ->all()),

                SelectFilter::make('currency')
                    ->label('Mata Uang')
                    ->placeholder('Semua mata uang')
                    ->searchable()
                    ->options(fn (): array => Product::query()
                        ->whereNotNull('currency')
                        ->where('currency', '!=', '')
                        ->distinct()
                        ->orderBy('currency')
                        ->pluck('currency', 'currency')
                        ->all()),

                TernaryFilter::make('has_media')
                    ->label('Media Produk')
                    ->placeholder('Semua')
                    ->trueLabel('Ada Media')
                    ->falseLabel('Tanpa Media')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('media'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('media'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                TernaryFilter::make('has_primary_media')
                    ->label('Gambar Utama')
                    ->placeholder('Semua')
                    ->trueLabel('Ada')
                    ->falseLabel('Tidak Ada')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('primaryMedia'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('primaryMedia'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                TernaryFilter::make('has_reviews')
                    ->label('Review')
                    ->placeholder('Semua')
                    ->trueLabel('Ada Review')
                    ->falseLabel('Tanpa Review')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('reviews'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('reviews'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Filter::make('price_range')
                    ->label('Rentang Harga')
                    ->schema([
                        TextInput::make('from')
                            ->label('Harga Minimum')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0'),
                        TextInput::make('until')
                            ->label('Harga Maksimum')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('1000000'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query): Builder => $query->where('base_price', '>=', $data['from']))
                        ->when($data['until'] ?? null, fn (Builder $query): Builder => $query->where('base_price', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Harga min Rp '.number_format((float) $data['from'], 0, ',', '.'))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Harga max Rp '.number_format((float) $data['until'], 0, ',', '.'))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('stock_range')
                    ->label('Rentang Stok')
                    ->schema([
                        TextInput::make('from')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->placeholder('0'),
                        TextInput::make('until')
                            ->label('Stok Maksimum')
                            ->numeric()
                            ->placeholder('100'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query): Builder => $query->where('stock', '>=', $data['from']))
                        ->when($data['until'] ?? null, fn (Builder $query): Builder => $query->where('stock', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Stok min '.number_format((float) $data['from'], 0, ',', '.'))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Stok max '.number_format((float) $data['until'], 0, ',', '.'))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('warranty_range')
                    ->label('Rentang Garansi')
                    ->schema([
                        TextInput::make('from')
                            ->label('Garansi Minimum (bulan)')
                            ->numeric()
                            ->placeholder('0'),
                        TextInput::make('until')
                            ->label('Garansi Maksimum (bulan)')
                            ->numeric()
                            ->placeholder('24'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query): Builder => $query->where('warranty_months', '>=', $data['from']))
                        ->when($data['until'] ?? null, fn (Builder $query): Builder => $query->where('warranty_months', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Garansi min '.(int) $data['from'].' bulan')
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Garansi max '.(int) $data['until'].' bulan')
                                ->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'] ?? null, fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Dibuat dari '.Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Dibuat sampai '.Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
            ])->filtersFormWidth(Width::FiveExtraLarge)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Klasifikasi Produk')
                    ->description('Filter status dan klasifikasi utama produk.')
                    ->schema([
                        $filters['is_active']->columnSpan(1),
                        $filters['stock_status']->columnSpan(1),
                        $filters['brand']->columnSpan(2),
                        $filters['currency']->columnSpan(2),
                        $filters['categories']->columnSpan(3),
                        $filters['promotions']->columnSpan(3),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),

                Section::make('Kelengkapan Relasi')
                    ->description('Filter berdasarkan kelengkapan data media dan review.')
                    ->schema([
                        $filters['has_media']->columnSpan(2),
                        $filters['has_primary_media']->columnSpan(2),
                        $filters['has_reviews']->columnSpan(2),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),

                Section::make('Rentang Nilai')
                    ->description('Filter angka untuk harga, stok, dan masa garansi.')
                    ->schema([
                        $filters['price_range']->columnSpan(2),
                        $filters['stock_range']->columnSpan(2),
                        $filters['warranty_range']->columnSpan(2),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),

                Section::make('Periode')
                    ->description('Filter berdasarkan tanggal pembuatan produk.')
                    ->schema([
                        $filters['created_at']->columnSpan(3),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function resolveImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        return Storage::disk('public')->url($normalizedPath);
    }
}
