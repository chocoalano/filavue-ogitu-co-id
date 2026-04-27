<?php

namespace App\Filament\Resources\ReportSummaries;

use App\Filament\Resources\ReportSummaries\Pages\ManageReportSummaries;
use App\Filament\Resources\ReportSummaries\Schemas\ReportSummaryForm;
use App\Filament\Resources\ReportSummaries\Schemas\ReportSummaryInfolist;
use App\Filament\Resources\ReportSummaries\Tables\ReportTaxSummariesTable;
use App\Models\ReportTaxSummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReportSummaryResource extends Resource
{
    protected static ?string $model = ReportTaxSummary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'tahun_pajak';
    protected static ?string $navigationLabel = 'Laporan Summary Pajak';
    protected static ?string $modelLabel = 'Laporan Summary Pajak';
    protected static ?string $pluralModelLabel = 'Laporan Summary Pajak';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    public static function form(Schema $schema): Schema
    {
        return ReportSummaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportSummaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTaxSummariesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReportSummaries::route('/'),
        ];
    }
}
