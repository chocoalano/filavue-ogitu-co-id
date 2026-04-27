<?php

namespace App\Filament\Resources\CustomerStockists;

use App\Filament\Resources\CustomerStockists\Pages\CreateCustomerStockist;
use App\Filament\Resources\CustomerStockists\Pages\EditCustomerStockist;
use App\Filament\Resources\CustomerStockists\Pages\ListCustomerStockists;
use App\Filament\Resources\CustomerStockists\Pages\ViewCustomerStockist;
use App\Filament\Resources\CustomerStockists\Schemas\CustomerStockistForm;
use App\Filament\Resources\CustomerStockists\Schemas\CustomerStockistInfolist;
use App\Filament\Resources\CustomerStockists\Tables\CustomerStockistsTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerStockistResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Pelanggan Stockist';
    protected static ?string $modelLabel = 'Pelanggan Stockist';
    protected static ?string $pluralModelLabel = 'Pelanggan Stockist';
    protected static string | UnitEnum | null $navigationGroup = 'Kelola';

    public static function form(Schema $schema): Schema
    {
        return CustomerStockistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerStockistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerStockistsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_stockist', true);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerStockists::route('/'),
            'create' => CreateCustomerStockist::route('/create'),
            'view' => ViewCustomerStockist::route('/{record}'),
            'edit' => EditCustomerStockist::route('/{record}/edit'),
        ];
    }
}
