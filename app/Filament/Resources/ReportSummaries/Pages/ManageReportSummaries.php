<?php

namespace App\Filament\Resources\ReportSummaries\Pages;

use App\Filament\Resources\ReportSummaries\ReportSummaryResource;
use App\Filament\Resources\ReportSummaries\Widgets\ReportSummaryBrutoChart;
use App\Filament\Resources\ReportSummaries\Widgets\ReportSummaryCallouts;
use App\Filament\Resources\ReportSummaries\Widgets\ReportSummaryDistribusiChart;
use App\Filament\Resources\ReportSummaries\Widgets\ReportSummaryStatsOverview;
use Filament\Resources\Pages\ManageRecords;

class ManageReportSummaries extends ManageRecords
{
    protected static string $resource = ReportSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportSummaryCallouts::class,
            ReportSummaryStatsOverview::class,
            ReportSummaryBrutoChart::class,
            ReportSummaryDistribusiChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
