<?php

namespace App\Filament\Resources\ReportTaxDailies\Widgets;

use App\Models\ReportTaxDaily;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ReportTaxDailyBrutoChart extends ChartWidget
{
    protected ?string $heading = 'Tren Bruto & PPh21 per Bulan';

    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        $data = ReportTaxDaily::query()
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jumlah_bruto) as total_bruto, ABS(SUM(pph21)) as total_pph21')
            ->whereYear('tanggal', now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $brutoData = [];
        $pph21Data = [];

        for ($m = 1; $m <= 12; $m++) {
            $row = $data->get($m);
            $brutoData[] = $row ? (float) $row->total_bruto : 0;
            $pph21Data[] = $row ? (float) $row->total_pph21 : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Bruto',
                    'data' => $brutoData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => '#6366f1',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'PPh21',
                    'data' => $pph21Data,
                    'backgroundColor' => 'rgba(244, 63, 94, 0.8)',
                    'borderColor' => '#f43f5e',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                scales: {
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#52525b',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)',
                        },
                        border: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.12)',
                        },
                    },
                    y: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#52525b',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)',
                        },
                        border: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.12)',
                        },
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#3f3f46',
                            boxWidth: 12,
                            padding: 16,
                        },
                    },
                },
            }
        JS);
    }
}
