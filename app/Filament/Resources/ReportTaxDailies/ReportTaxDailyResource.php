<?php

namespace App\Filament\Resources\ReportTaxDailies;

use App\Filament\Resources\ReportTaxDailies\Pages\ManageReportTaxDailies;
use App\Filament\Resources\ReportTaxDailies\Schemas\ReportTaxDailyForm;
use App\Filament\Resources\ReportTaxDailies\Schemas\ReportTaxDailyInfolist;
use App\Filament\Resources\ReportTaxDailies\Tables\ReportTaxDailiesTable;
use App\Models\ReportTaxDaily;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReportTaxDailyResource extends Resource
{
    protected static ?string $model = ReportTaxDaily::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Laporan Pajak Harian';
    protected static ?string $modelLabel = 'Laporan Pajak Harian';
    protected static ?string $pluralModelLabel = 'Laporan Pajak Harian';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    public static function form(Schema $schema): Schema
    {
        return ReportTaxDailyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportTaxDailyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTaxDailiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReportTaxDailies::route('/'),
        ];
    }
}
