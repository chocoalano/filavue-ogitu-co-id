<?php

namespace App\Filament\Resources\ReportTaxDailies\Widgets;

use App\Models\ReportTaxDaily;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ReportTaxDailyDistribusiChart extends ChartWidget
{
    protected ?string $heading = 'Perbandingan Jumlah Bruto & PPh21';

    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $totals = ReportTaxDaily::query()
            ->selectRaw('SUM(jumlah_bruto) as total_bruto, ABS(SUM(pph21)) as total_pph21')
            ->first();

        $totalBruto = (float) data_get($totals, 'total_bruto', 0);
        $totalPph21 = (float) data_get($totals, 'total_pph21', 0);

        return [
            'datasets' => [
                [
                    'label' => 'Nominal',
                    'data' => [$totalBruto, $totalPph21],
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.85)',
                        'rgba(244, 63, 94, 0.85)',
                    ],
                    'borderColor' => [
                        '#6366f1',
                        '#f43f5e',
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => ['Jumlah Bruto', 'PPh21'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#3f3f46',
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 20,
                        },
                    },
                },
                cutout: '70%',
            }
        JS);
    }
}
