<?php

namespace App\Filament\Resources\ReportAnalytics;

use App\Filament\Resources\ReportAnalytics\Pages\ManageReportAnalytics;
use App\Filament\Resources\ReportAnalytics\Schemas\ReportAnalyticForm;
use App\Filament\Resources\ReportAnalytics\Schemas\ReportAnalyticInfolist;
use App\Filament\Resources\ReportAnalytics\Tables\ReportAnalyticsTable;
use App\Models\ReportAnalytic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReportAnalyticResource extends Resource
{
    protected static ?string $model = ReportAnalytic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'fullname';
    protected static ?string $navigationLabel = 'Laporan Analitik';
    protected static ?string $modelLabel = 'Laporan Analitik';
    protected static ?string $pluralModelLabel = 'Laporan Analitik';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    public static function form(Schema $schema): Schema
    {
        return ReportAnalyticForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportAnalyticInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportAnalyticsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReportAnalytics::route('/'),
        ];
    }
}
