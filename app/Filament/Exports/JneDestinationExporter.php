<?php

namespace App\Filament\Exports;

use App\Models\JneDestination;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class JneDestinationExporter extends Exporter
{
    protected static ?string $model = JneDestination::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('country_name'),
            ExportColumn::make('province_name'),
            ExportColumn::make('city_name'),
            ExportColumn::make('district_name'),
            ExportColumn::make('subdistrict_name'),
            ExportColumn::make('zip_code'),
            ExportColumn::make('tariff_code'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your jne destination export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
