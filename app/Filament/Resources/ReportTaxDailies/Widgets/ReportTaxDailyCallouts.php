<?php

namespace App\Filament\Resources\ReportTaxDailies\Widgets;

use App\Models\ReportTaxDaily;
use Filament\Widgets\Widget;

class ReportTaxDailyCallouts extends Widget
{
    protected string $view = 'filament.resources.report-tax-dailies.widgets.report-tax-daily-callouts';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public int $total = 0;

    public float $totalPph21 = 0;

    public int $totalTanpaNpwp = 0;

    public function mount(): void
    {
        $this->total = ReportTaxDaily::count();
        $this->totalPph21 = (float) ReportTaxDaily::sum('pph21');
        $this->totalTanpaNpwp = ReportTaxDaily::where(function ($query): void {
            $query->whereNull('npwp')->orWhere('npwp', '');
        })->count();
    }
}
