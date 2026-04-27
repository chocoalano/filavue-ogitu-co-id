<?php

namespace App\Filament\Resources\CustomerStockists\Tables;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerStockistsTable
{
    private static function hiddenText(
        string $name,
        string $label,
        bool $searchable = true,
        bool $sortable = true,
        bool $dateTime = false,
    ): TextColumn {
        $column = TextColumn::make($name)
            ->label($label)
            ->toggleable(isToggledHiddenByDefault: true);

        if ($searchable) {
            $column->searchable();
        }

        if ($sortable) {
            $column->sortable();
        }

        if ($dateTime) {
            $column->dateTime();
        }

        return $column;
    }

    private static function distinctNonEmptyOptions(string $column): array
    {
        return Customer::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column, $column)
            ->all();
    }

    private static function makeRegionIndicators(array $data): array
    {
        $map = [
            'stockist_province_id' => 'ID Provinsi',
            'stockist_kabupaten_id' => 'ID Kabupaten',
        ];

        $indicators = [];

        foreach ($map as $field => $label) {
            $value = trim((string) ($data[$field] ?? ''));
            if ($value !== '') {
                $indicators[] = Indicator::make("{$label}: {$value}")
                    ->removeField($field);
            }
        }

        return $indicators;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')->label('Username')->searchable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Telepon')->searchable(),

                IconColumn::make('is_stockist')
                    ->label('Stockist')
                    ->boolean(),

                TextColumn::make('stockist_province_name')
                    ->label('Provinsi Stockist')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stockist_kabupaten_name')
                    ->label('Kabupaten Stockist')
                    ->searchable()
                    ->sortable(),

                self::hiddenText('stockist_province_id', 'ID Provinsi'),
                self::hiddenText('stockist_kabupaten_id', 'ID Kabupaten'),
                self::hiddenText('created_at', 'Dibuat', searchable: false, dateTime: true),
                self::hiddenText('updated_at', 'Diperbarui', searchable: false, dateTime: true),
            ])
            ->filters([
                TernaryFilter::make('is_stockist')
                    ->label('Status Stockist')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                SelectFilter::make('stockist_province_name')
                    ->label('Provinsi Stockist')
                    ->placeholder('Semua provinsi')
                    ->searchable()
                    ->options(fn (): array => self::distinctNonEmptyOptions('stockist_province_name')),

                SelectFilter::make('stockist_kabupaten_name')
                    ->label('Kabupaten Stockist')
                    ->placeholder('Semua kabupaten')
                    ->searchable()
                    ->options(fn (): array => self::distinctNonEmptyOptions('stockist_kabupaten_name')),

                Filter::make('stockist_region_code')
                    ->label('Kode Wilayah Stockist')
                    ->schema([
                        TextInput::make('stockist_province_id')
                            ->label('ID Provinsi')
                            ->placeholder('Contoh: 31'),
                        TextInput::make('stockist_kabupaten_id')
                            ->label('ID Kabupaten')
                            ->placeholder('Contoh: 3171'),
                    ])
                    ->columns(2)
                    ->query(static function (Builder $query, array $data): Builder {
                        $provinceId = trim((string) ($data['stockist_province_id'] ?? ''));
                        $kabupatenId = trim((string) ($data['stockist_kabupaten_id'] ?? ''));

                        return $query
                            ->when($provinceId !== '', fn (Builder $q) => $q->where('stockist_province_id', 'like', "%{$provinceId}%"))
                            ->when($kabupatenId !== '', fn (Builder $q) => $q->where('stockist_kabupaten_id', 'like', "%{$kabupatenId}%"));
                    })
                    ->indicateUsing(fn (array $data): array => self::makeRegionIndicators($data)),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Status')
                    ->description('Filter berdasarkan status customer stockist.')
                    ->schema([
                        $filters['is_stockist']->columnSpan(2),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),

                Section::make('Wilayah Stockist')
                    ->description('Filter berdasarkan provinsi, kabupaten, dan kode wilayah stockist.')
                    ->schema([
                        $filters['stockist_province_name']->columnSpan(2),
                        $filters['stockist_kabupaten_name']->columnSpan(2),
                        $filters['stockist_region_code']->columnSpan(2),
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
}
