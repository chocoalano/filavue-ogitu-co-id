<?php

namespace App\Filament\Resources\CustomerEwallets\Exports;

use App\Models\Customer;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat as ExportFormatContract;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CustomerEwalletExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('username')
                ->label('Username Customer'),

            ExportColumn::make('name')
                ->label('Nama Customer'),

            ExportColumn::make('ref_code')
                ->label('Kode Referral'),

            ExportColumn::make('ewallet_id')
                ->label('ID E-Wallet'),

            ExportColumn::make('ewallet_saldo')
                ->label('Saldo E-Wallet'),

            ExportColumn::make('bonus_pending')
                ->label('Bonus Pending'),

            ExportColumn::make('bonus_processed')
                ->label('Bonus Diproses'),

            ExportColumn::make('bank_name')
                ->label('Bank'),

            ExportColumn::make('bank_account')
                ->label('No. Rekening'),

            ExportColumn::make('status')
                ->label('Status Customer')
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-'),

            ExportColumn::make('created_at')
                ->label('Bergabung')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('updated_at')
                ->label('Diperbarui')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('has_wallet')
                ->label('Status Wallet')
                ->options([
                    'yes' => 'Memiliki E-Wallet',
                    'no' => 'Belum Memiliki E-Wallet',
                ])
                ->placeholder('Semua member'),

            Select::make('status')
                ->label('Status Customer')
                ->options(self::statusOptions())
                ->placeholder('Semua status'),

            TextInput::make('min_wallet_balance')
                ->label('Saldo Customer Minimum')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->placeholder('0')
                ->helperText('Gunakan filter saldo customer jika export penuh terasa berat.'),

            TextInput::make('max_wallet_balance')
                ->label('Saldo Customer Maksimum')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->placeholder('Tanpa batas'),

            DateTimePicker::make('date_from')
                ->label('Dari Tanggal Bergabung')
                ->seconds(false)
                ->placeholder('Awal periode'),

            DateTimePicker::make('date_until')
                ->label('Sampai Tanggal Bergabung')
                ->seconds(false)
                ->placeholder('Akhir periode'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    /**
     * @return array<int, ExportFormatContract>
     */
    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data saldo e-wallet customer selesai. '.Number::format($export->successful_rows).' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diexport.';
        }

        return $body;
    }

    /**
     * @return array<int, string>
     */
    private static function statusOptions(): array
    {
        return [
            1 => 'Prospek',
            2 => 'Pasif',
            3 => 'Aktif',
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
