<?php

namespace App\Filament\Resources\CustomerBonusCashbacks;

use App\Filament\Resources\CustomerBonusCashbacks\Pages\ManageCustomerBonusCashbacks;
use App\Filament\Resources\CustomerBonusCashbacks\Schemas\CustomerBonusCashbackForm;
use App\Filament\Resources\CustomerBonusCashbacks\Schemas\CustomerBonusCashbackInfolist;
use App\Filament\Resources\CustomerBonusCashbacks\Tables\CustomerBonusCashbacksTable;
use App\Models\CustomerBonusCashback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusCashbackResource extends Resource
{
    protected static ?string $model = CustomerBonusCashback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusCashback';
    protected static ?string $navigationLabel = 'Bonus Cashback';
    protected static ?string $modelLabel = 'Bonus Cashback';
    protected static ?string $pluralModelLabel = 'Bonus Cashback';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusCashbackForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusCashbackInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusCashbacksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusCashbacks::route('/'),
        ];
    }
}
