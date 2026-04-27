<?php

namespace App\Filament\Resources\CommodityCodes\Tables;

use App\Filament\Resources\CommodityCodes\Exports\CommodityCodeExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommodityCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Komoditas')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('dangerous_good')
                    ->label('Barang Berbahaya')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_quarantine')
                    ->label('Wajib Karantina')
                    ->boolean()
                    ->sortable(),

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
                TernaryFilter::make('dangerous_good')
                    ->label('Barang Berbahaya')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->placeholder('Semua'),

                TernaryFilter::make('is_quarantine')
                    ->label('Wajib Karantina')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->placeholder('Semua'),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['dangerous_good']->columnSpan(3),
                $filters['is_quarantine']->columnSpan(3),
                $filters['created_between']->columnSpan(6),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Terpilih')
                        ->exporter(CommodityCodeExporter::class),
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
}
