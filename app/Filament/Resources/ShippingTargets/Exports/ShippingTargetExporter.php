<?php

namespace App\Filament\Resources\ShippingTargets\Exports;

use App\Models\ShippingTarget;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat as ExportFormatContract;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ShippingTargetExporter extends Exporter
{
    protected static ?string $model = ShippingTarget::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('three_lc_code')
                ->label('3LC Code'),

            ExportColumn::make('country')
                ->label('Negara'),

            ExportColumn::make('province_id')
                ->label('Province ID'),

            ExportColumn::make('province')
                ->label('Provinsi'),

            ExportColumn::make('city_id')
                ->label('City ID'),

            ExportColumn::make('city')
                ->label('Kota / Kabupaten'),

            ExportColumn::make('district')
                ->label('Kecamatan'),

            ExportColumn::make('district_lion')
                ->label('Kecamatan (Lion)'),

            ExportColumn::make('created_at')
                ->label('Dibuat')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('updated_at')
                ->label('Diperbarui')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),
        ];
    }

    /**
     * @return array<int, ExportFormatContract>
     */
    public function getFormats(): array
    {
        return [
            ExportFormat::Csv,
            ExportFormat::Xlsx,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export target pengiriman selesai. ' . Number::format($export->successful_rows) . ' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal diexport.';
        }

        return $body;
    }

    private static function formatDateTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return filled($value) ? (string) $value : '-';
    }
}
