<?php

it('restores laporan summary pajak widgets to the original chart and overview layout', function (): void {
    $pageSource = file_get_contents(app_path('Filament/Resources/ReportSummaries/Pages/ManageReportSummaries.php'));

    expect($pageSource)->toBeString()
        ->and($pageSource)->toContain('ReportSummaryCallouts::class')
        ->and($pageSource)->toContain('ReportSummaryStatsOverview::class')
        ->and($pageSource)->toContain('ReportSummaryBrutoChart::class')
        ->and($pageSource)->toContain('ReportSummaryDistribusiChart::class')
        ->and($pageSource)->toContain('return 2;')
        ->and($pageSource)->not->toContain('ReportSummaryWorkbookWidget::class');
});
