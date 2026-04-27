<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use App\Models\ReportAnalytic;
use Filament\Widgets\Widget;

class ReportAnalyticCallouts extends Widget
{
    protected string $view = 'filament.resources.report-analytics.widgets.report-analytic-callouts';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public int $total = 0;

    public float $totalPph21 = 0;

    public int $totalTanpaNpwp = 0;

    public function mount(): void
    {
        $this->total = ReportAnalytic::count();
        $this->totalPph21 = (float) ReportAnalytic::sum('pph21');
        $this->totalTanpaNpwp = ReportAnalytic::where(function ($query): void {
            $query->whereNull('npwp')->orWhere('npwp', '');
        })->count();
    }
}
