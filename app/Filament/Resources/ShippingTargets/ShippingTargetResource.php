<?php

namespace App\Filament\Resources\ShippingTargets;

use App\Filament\Resources\ShippingTargets\Pages\ManageShippingTargets;
use App\Filament\Resources\ShippingTargets\Schemas\ShippingTargetForm;
use App\Filament\Resources\ShippingTargets\Schemas\ShippingTargetInfolist;
use App\Filament\Resources\ShippingTargets\Tables\ShippingTargetsTable;
use App\Models\ShippingTarget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ShippingTargetResource extends Resource
{
    protected static ?string $model = ShippingTarget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'three_lc_code';
    protected static ?string $navigationLabel = 'Attribute Pengiriman';
    protected static ?string $modelLabel = 'Attribute Pengiriman';
    protected static ?string $pluralModelLabel = 'Attribute Pengiriman';
    protected static string | UnitEnum | null $navigationGroup = 'Pesanan';

    public static function form(Schema $schema): Schema
    {
        return ShippingTargetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShippingTargetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingTargetsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShippingTargets::route('/'),
        ];
    }
}
