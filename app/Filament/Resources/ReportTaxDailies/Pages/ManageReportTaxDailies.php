<?php

namespace App\Filament\Resources\ReportTaxDailies\Pages;

use App\Filament\Resources\ReportTaxDailies\ReportTaxDailyResource;
use App\Filament\Resources\ReportTaxDailies\Widgets\ReportTaxDailyBrutoChart;
use App\Filament\Resources\ReportTaxDailies\Widgets\ReportTaxDailyCallouts;
use App\Filament\Resources\ReportTaxDailies\Widgets\ReportTaxDailyDistribusiChart;
use App\Filament\Resources\ReportTaxDailies\Widgets\ReportTaxDailyStatsOverview;
use Filament\Resources\Pages\ManageRecords;

class ManageReportTaxDailies extends ManageRecords
{
    protected static string $resource = ReportTaxDailyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportTaxDailyCallouts::class,
            ReportTaxDailyStatsOverview::class,
            ReportTaxDailyBrutoChart::class,
            ReportTaxDailyDistribusiChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
