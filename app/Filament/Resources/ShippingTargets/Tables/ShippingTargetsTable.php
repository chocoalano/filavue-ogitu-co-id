<?php

namespace App\Filament\Resources\ShippingTargets\Tables;

use App\Filament\Resources\ShippingTargets\Exports\ShippingTargetExporter;
use App\Models\ShippingTarget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShippingTargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('three_lc_code')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('three_lc_code')
                    ->label('3LC Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country')
                    ->label('Negara')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('province_id')
                    ->label('Province ID')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city_id')
                    ->label('City ID')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Kota / Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('district')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('district_lion')
                    ->label('Kecamatan (Lion)')
                    ->searchable()
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
                SelectFilter::make('country')
                    ->label('Negara')
                    ->options(fn (): array => ShippingTarget::query()
                        ->whereNotNull('country')
                        ->where('country', '!=', '')
                        ->orderBy('country')
                        ->distinct()
                        ->pluck('country', 'country')
                        ->all())
                    ->placeholder('Semua negara')
                    ->searchable(),

                SelectFilter::make('province')
                    ->label('Provinsi')
                    ->options(fn (): array => ShippingTarget::query()
                        ->whereNotNull('province')
                        ->where('province', '!=', '')
                        ->orderBy('province')
                        ->distinct()
                        ->pluck('province', 'province')
                        ->all())
                    ->placeholder('Semua provinsi')
                    ->searchable(),

                SelectFilter::make('city')
                    ->label('Kota / Kabupaten')
                    ->options(fn (): array => ShippingTarget::query()
                        ->whereNotNull('city')
                        ->where('city', '!=', '')
                        ->orderBy('city')
                        ->distinct()
                        ->pluck('city', 'city')
                        ->all())
                    ->placeholder('Semua kota / kabupaten')
                    ->searchable(),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['country']->columnSpan(3),
                $filters['province']->columnSpan(3),
                $filters['city']->columnSpan(3),
                $filters['created_between']->columnSpan(3),
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
                        ->exporter(ShippingTargetExporter::class),
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
