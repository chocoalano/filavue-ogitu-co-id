<?php

namespace App\Filament\Resources\ReportSummaries\Widgets;

use App\Models\ReportTaxSummary;
use Filament\Widgets\Widget;

class ReportSummaryCallouts extends Widget
{
    protected string $view = 'filament.resources.report-summaries.widgets.report-summary-callouts';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public int $totalTahun = 0;

    public float $totalPph21 = 0;

    public int $totalTransaksi = 0;

    public function mount(): void
    {
        $this->totalTahun = ReportTaxSummary::query()
            ->select('tahun_pajak')
            ->whereNotNull('tahun_pajak')
            ->distinct()
            ->count();

        $this->totalPph21 = (float) ReportTaxSummary::sum('pph21');
        $this->totalTransaksi = ReportTaxSummary::count();
    }
}
