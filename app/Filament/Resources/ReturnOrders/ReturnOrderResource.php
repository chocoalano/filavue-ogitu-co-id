<?php

namespace App\Filament\Resources\ReturnOrders;

use App\Filament\Resources\ReturnOrders\Pages\ManageReturnOrders;
use App\Filament\Resources\ReturnOrders\Schemas\ReturnOrderForm;
use App\Filament\Resources\ReturnOrders\Schemas\ReturnOrderInfolist;
use App\Filament\Resources\ReturnOrders\Tables\ReturnOrdersTable;
use App\Models\ReturnOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReturnOrderResource extends Resource
{
    protected static ?string $model = ReturnOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Return Pesanan';
    protected static ?string $modelLabel = 'Return Pesanan';
    protected static ?string $pluralModelLabel = 'Return Pesanan';
    protected static string | UnitEnum | null $navigationGroup = 'Pesanan';

    public static function form(Schema $schema): Schema
    {
        return ReturnOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReturnOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReturnOrders::route('/'),
        ];
    }
}
