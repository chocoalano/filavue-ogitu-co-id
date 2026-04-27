<?php

namespace App\Filament\Resources\ReportSummaries\Widgets;

use App\Models\ReportTaxSummary;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ReportSummaryBrutoChart extends ChartWidget
{
    protected ?string $heading = 'Total Bruto & PPh21 per Tahun';

    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = ReportTaxSummary::query()
            ->selectRaw('tahun_pajak, SUM(jumlah_bruto) AS total_bruto, ABS(SUM(pph21)) AS total_pph21')
            ->whereNotNull('tahun_pajak')
            ->groupBy('tahun_pajak')
            ->orderBy('tahun_pajak')
            ->get()
            ->keyBy('tahun_pajak');

        $labels = $data->keys()->map(fn ($y) => (string) $y)->toArray();
        $brutoData = $data->map(fn ($row) => (float) $row->total_bruto)->values()->toArray();
        $pph21Data = $data->map(fn ($row) => (float) $row->total_pph21)->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Bruto',
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
            'labels' => $labels,
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
