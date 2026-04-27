<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Nama Lengkap'),
            ExportColumn::make('username')
                ->label('Username'),
            ExportColumn::make('nik')
                ->label('NIK'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Telepon/WA'),
            ExportColumn::make('gender')
                ->label('Jenis Kelamin'),
            ExportColumn::make('sponsor.username')
                ->label('Sponsor'),
            ExportColumn::make('ref_code')
                ->label('Kode Referral'),
            ExportColumn::make('package.name')
                ->label('Paket'),
            ExportColumn::make('level')
                ->label('Level'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('bank_name')
                ->label('Nama Bank'),
            ExportColumn::make('bank_account')
                ->label('No. Rekening'),
            ExportColumn::make('ewallet_saldo')
                ->label('Saldo E-Wallet'),
            ExportColumn::make('bonus_pending')
                ->label('Bonus Pending'),
            ExportColumn::make('bonus_processed')
                ->label('Bonus Diproses'),
            ExportColumn::make('omzet')
                ->label('Omzet'),
            ExportColumn::make('omzet_group')
                ->label('Omzet Group'),
            ExportColumn::make('is_stockist')
                ->label('Stockist'),
            ExportColumn::make('stockist_province_name')
                ->label('Provinsi Stockist'),
            ExportColumn::make('stockist_kabupaten_name')
                ->label('Kab/Kota Stockist'),
            ExportColumn::make('email_verified_at')
                ->label('Verifikasi Email'),
            ExportColumn::make('created_at')
                ->label('Tanggal Daftar'),
            ExportColumn::make('updated_at')
                ->label('Terakhir Diperbarui'),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->placeholder('Pilih tanggal awal')
                        ->native(false)
                        ->helperText('Filter data pelanggan mulai tanggal ini.'),

                    DatePicker::make('date_to')
                        ->label('Sampai Tanggal')
                        ->placeholder('Pilih tanggal akhir')
                        ->native(false)
                        ->helperText('Filter data pelanggan hingga tanggal ini.'),
                ]),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data pelanggan selesai. '.Number::format($export->successful_rows).' '.str('baris')->plural($export->successful_rows).' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diekspor.';
        }

        return $body;
    }
}
