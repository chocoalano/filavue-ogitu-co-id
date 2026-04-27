<?php

namespace App\Filament\Resources\CommodityCodes\Exports;

use App\Models\CommodityCode;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat as ExportFormatContract;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CommodityCodeExporter extends Exporter
{
    protected static ?string $model = CommodityCode::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Kode'),

            ExportColumn::make('name')
                ->label('Nama Komoditas'),

            ExportColumn::make('dangerous_good')
                ->label('Barang Berbahaya')
                ->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak'),

            ExportColumn::make('is_quarantine')
                ->label('Wajib Karantina')
                ->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak'),

            ExportColumn::make('created_at')
                ->label('Dibuat')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('updated_at')
                ->label('Diperbarui')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export kode komoditas selesai. ' . Number::format($export->successful_rows) . ' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal diexport.';
        }

        return $body;
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

    private static function formatDateTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return filled($value) ? (string) $value : '-';
    }
}
