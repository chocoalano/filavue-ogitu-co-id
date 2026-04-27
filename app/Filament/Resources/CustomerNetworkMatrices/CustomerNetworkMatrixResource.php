<?php

namespace App\Filament\Resources\CustomerNetworkMatrices;

use App\Filament\Resources\CustomerNetworkMatrices\Pages\ManageCustomerNetworkMatrices;
use App\Filament\Resources\CustomerNetworkMatrices\Schemas\CustomerNetworkMatrixForm;
use App\Filament\Resources\CustomerNetworkMatrices\Schemas\CustomerNetworkMatrixInfolist;
use App\Filament\Resources\CustomerNetworkMatrices\Tables\CustomerNetworkMatricesTable;
use App\Models\CustomerNetworkMatrix;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerNetworkMatrixResource extends Resource
{
    protected static ?string $model = CustomerNetworkMatrix::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'Sponsor/Matrix Jaringan';
    protected static ?string $modelLabel = 'Sponsor/Matrix Jaringan';
    protected static ?string $pluralModelLabel = 'Sponsor/Matrix Jaringan';
    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    public static function form(Schema $schema): Schema
    {
        return CustomerNetworkMatrixForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerNetworkMatrixInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerNetworkMatricesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerNetworkMatrices::route('/'),
        ];
    }
}
