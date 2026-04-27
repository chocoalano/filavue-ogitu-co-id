<?php

namespace App\Filament\Resources\CustomerTopups\Exports;

use App\Models\CustomerWalletTransaction;
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

class CustomerTopupExporter extends Exporter
{
    protected static ?string $model = CustomerWalletTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.username')
                ->label('Username Customer'),

            ExportColumn::make('customer.name')
                ->label('Nama Customer'),

            ExportColumn::make('customer.ref_code')
                ->label('Kode Referral'),

            ExportColumn::make('customer.ewallet_id')
                ->label('ID E-Wallet'),

            ExportColumn::make('customer.ewallet_saldo')
                ->label('Saldo Customer'),

            ExportColumn::make('amount')
                ->label('Nominal Topup'),

            ExportColumn::make('balance_before')
                ->label('Saldo Sebelum'),

            ExportColumn::make('balance_after')
                ->label('Saldo Sesudah'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(string) $state] ?? '-'),

            ExportColumn::make('payment_method')
                ->label('Metode Bayar'),

            ExportColumn::make('transaction_ref')
                ->label('Ref Transaksi'),

            ExportColumn::make('midtrans_transaction_id')
                ->label('ID Midtrans'),

            ExportColumn::make('is_system')
                ->label('Transaksi Sistem')
                ->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak'),

            ExportColumn::make('completed_at')
                ->label('Selesai')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('created_at')
                ->label('Dibuat')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('updated_at')
                ->label('Diperbarui')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('status')
                ->label('Status Topup')
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
                ->label('Dari Tanggal')
                ->seconds(false)
                ->placeholder('Awal periode'),

            DateTimePicker::make('date_until')
                ->label('Sampai Tanggal')
                ->seconds(false)
                ->placeholder('Akhir periode'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query
            ->where('type', 'topup')
            ->with([
                'customer:id,name,username,ref_code,ewallet_id,ewallet_saldo',
            ]);
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
        $body = 'Export data topup customer selesai. '.Number::format($export->successful_rows).' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diexport.';
        }

        return $body;
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
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
