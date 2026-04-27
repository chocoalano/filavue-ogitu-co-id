<?php

namespace App\Filament\Resources\ReportSummaries\Tables;

use App\Models\ReportTaxSummary;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportTaxSummariesTable
{
    /** @var array<int, string> */
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun_pajak')
            ->modifyQueryUsing(function (Builder $query): Builder {
                $sub = DB::table('vw_customer_bonus_pph21')
                    ->selectRaw('
                        tahun_pajak,
                        MONTH(tanggal) AS bulan,
                        SUM(jumlah_bruto) AS total_bruto,
                        SUM(pph21) AS total_pph21,
                        COUNT(*) AS total_transaksi
                    ')
                    ->groupByRaw('tahun_pajak, MONTH(tanggal)');

                return $query
                    ->fromSub($sub, 'summary')
                    ->selectRaw('*, CONCAT(tahun_pajak, LPAD(bulan, 2, "0")) AS id');
            })
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('tahun_pajak')
                    ->label('Tahun Pajak')
                    ->badge()
                    ->sortable(),

                TextColumn::make('bulan')
                    ->label('Bulan')
                    ->formatStateUsing(fn (mixed $state): string => self::MONTHS[(int) $state] ?? '-')
                    ->sortable(),

                TextColumn::make('total_transaksi')
                    ->label('Total Transaksi')
                    ->formatStateUsing(fn (mixed $state): string => number_format((int) $state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('total_bruto')
                    ->label('Total Bruto')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_pph21')
                    ->label('Total PPh21')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tahun_pajak')
                    ->label('Tahun')
                    ->options(fn (): array => ReportTaxSummary::query()
                        ->select('tahun_pajak')
                        ->whereNotNull('tahun_pajak')
                        ->distinct()
                        ->orderByDesc('tahun_pajak')
                        ->pluck('tahun_pajak', 'tahun_pajak')
                        ->toArray())
                    ->placeholder('Semua tahun')
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['value'] ?? null), fn (Builder $builder): Builder => $builder->where('tahun_pajak', (int) $data['value']))
                    )
                    ->indicateUsing(fn (array $data): ?Indicator => filled($data['value'] ?? null)
                        ? Indicator::make('Tahun ' . $data['value'])->removeField('tahun_pajak')
                        : null
                    ),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
