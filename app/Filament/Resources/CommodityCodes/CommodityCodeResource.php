<?php

namespace App\Filament\Resources\CommodityCodes;

use App\Filament\Resources\CommodityCodes\Pages\ManageCommodityCodes;
use App\Filament\Resources\CommodityCodes\Schemas\CommodityCodeForm;
use App\Filament\Resources\CommodityCodes\Schemas\CommodityCodeInfolist;
use App\Filament\Resources\CommodityCodes\Tables\CommodityCodesTable;
use App\Models\CommodityCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommodityCodeResource extends Resource
{
    protected static ?string $model = CommodityCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';
    protected static ?string $navigationLabel = 'Kode Komoditas Produk';
    protected static ?string $modelLabel = 'Kode Komoditas Produk';
    protected static ?string $pluralModelLabel = 'Kode Komoditas Produk';
    protected static string | UnitEnum | null $navigationGroup = 'Pesanan';

    public static function form(Schema $schema): Schema
    {
        return CommodityCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CommodityCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommodityCodesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommodityCodes::route('/'),
        ];
    }
}
