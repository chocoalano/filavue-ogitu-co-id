<?php

namespace App\Filament\Resources\ReportAnalytics\Pages;

use App\Filament\Resources\ReportAnalytics\ReportAnalyticResource;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticBonusPlanATableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticBonusPlanBTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticOmzetSummaryTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticOrderSummaryTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticStatsOverview;
use Filament\Resources\Pages\ManageRecords;

class ManageReportAnalytics extends ManageRecords
{
    protected static string $resource = ReportAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportAnalyticStatsOverview::class,
            ReportAnalyticOrderSummaryTableWidget::class,
            ReportAnalyticBonusPlanATableWidget::class,
            ReportAnalyticOmzetSummaryTableWidget::class,
            ReportAnalyticBonusPlanBTableWidget::class,
        ];
    }

    /**
     * Menentukan jumlah kolom grid di halaman ini.
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
        ];
    }
}
