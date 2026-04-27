<?php

namespace App\Filament\Resources\ReportSummaries\Widgets;

use App\Models\ReportTaxSummary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportSummaryStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalTahun = ReportTaxSummary::query()
            ->select('tahun_pajak')
            ->whereNotNull('tahun_pajak')
            ->distinct()
            ->count();

        $totalTransaksi = ReportTaxSummary::count();
        $totalBruto = (float) ReportTaxSummary::sum('jumlah_bruto');
        $totalPph21 = (float) ReportTaxSummary::sum('pph21');

        $yearlyBruto = ReportTaxSummary::query()
            ->selectRaw('tahun_pajak, SUM(jumlah_bruto) as total')
            ->whereNotNull('tahun_pajak')
            ->groupBy('tahun_pajak')
            ->orderBy('tahun_pajak')
            ->pluck('total', 'tahun_pajak')
            ->values()
            ->map(fn ($v) => (float) $v)
            ->toArray();

        return [
            Stat::make('Total Tahun Pajak', number_format($totalTahun, 0, ',', '.'))
                ->description('Tahun pajak terdaftar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--north']),

            Stat::make('Total Transaksi', number_format($totalTransaksi, 0, ',', '.'))
                ->description('Akumulasi seluruh transaksi')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart($yearlyBruto)
                ->color('info')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--graphite']),

            Stat::make('Total Jumlah Bruto', 'Rp ' . number_format($totalBruto, 0, ',', '.'))
                ->description('Akumulasi seluruh bruto bonus')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--alloy']),

            Stat::make('Total PPh21', 'Rp ' . number_format(abs($totalPph21), 0, ',', '.'))
                ->description($totalPph21 < 0 ? 'Nilai negatif — perlu ditinjau' : 'Akumulasi potongan pajak')
                ->descriptionIcon($totalPph21 < 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-arrow-trending-down')
                ->color($totalPph21 < 0 ? 'danger' : 'warning')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--chrome']),
        ];
    }
}
