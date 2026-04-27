<?php

namespace App\Filament\Resources\ReportTaxDailies\Schemas;

use App\Models\ReportTaxDaily;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;

class ReportTaxDailyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Wajib Pajak Tanpa NPWP')
                ->description('Wajib pajak ini belum memiliki NPWP. Tarif PPh21 lebih tinggi 20% dari tarif normal akan dikenakan sesuai ketentuan Pasal 21 UU PPh.')
                ->warning()
                ->visible(fn (ReportTaxDaily $record): bool => blank($record->npwp)),

            Callout::make('Nilai PPh21 Negatif')
                ->description('Nilai PPh21 pada record ini bernilai negatif. Hal ini dapat mengindikasikan adanya koreksi pajak atau data yang perlu diverifikasi.')
                ->danger()
                ->visible(fn (ReportTaxDaily $record): bool => (float) $record->pph21 < 0),

            TextEntry::make('tanggal')
                ->label('Tanggal')
                ->date(),

            TextEntry::make('tahun_pajak')
                ->label('Tahun Pajak')
                ->placeholder('-'),

            TextEntry::make('username')
                ->label('Username')
                ->placeholder('-'),

            TextEntry::make('name')
                ->label('Nama Lengkap')
                ->placeholder('-'),

            TextEntry::make('email')
                ->label('Email')
                ->placeholder('-'),

            TextEntry::make('no_telepon')
                ->label('No. Telepon')
                ->placeholder('-'),

            TextEntry::make('nik')
                ->label('NIK')
                ->placeholder('-'),

            TextEntry::make('npwp')
                ->label('NPWP')
                ->placeholder('-'),

            TextEntry::make('alamat')
                ->label('Alamat')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('jumlah_bruto')
                ->label('Jumlah Bruto')
                ->money('IDR'),

            TextEntry::make('tarif')
                ->label('Tarif')
                ->formatStateUsing(fn (mixed $state): string => $state !== null ? $state . '%' : '-'),

            TextEntry::make('pph21')
                ->label('PPh21')
                ->money('IDR'),
        ]);
    }
}
