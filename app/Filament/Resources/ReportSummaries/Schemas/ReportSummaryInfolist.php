<?php

namespace App\Filament\Resources\ReportSummaries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReportSummaryInfolist
{
    /** @var array<int, string> */
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('tahun_pajak')
                ->label('Tahun Pajak'),

            TextEntry::make('bulan')
                ->label('Bulan')
                ->formatStateUsing(fn (mixed $state): string => self::MONTHS[(int) $state] ?? '-'),

            TextEntry::make('total_transaksi')
                ->label('Total Transaksi')
                ->formatStateUsing(fn (mixed $state): string => number_format((int) $state, 0, ',', '.')),

            TextEntry::make('total_bruto')
                ->label('Total Jumlah Bruto')
                ->money('IDR'),

            TextEntry::make('total_pph21')
                ->label('Total PPh21')
                ->money('IDR'),
        ]);
    }
}
