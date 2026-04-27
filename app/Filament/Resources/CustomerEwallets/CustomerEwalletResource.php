<?php

namespace App\Filament\Resources\CustomerEwallets;

use App\Filament\Resources\CustomerEwallets\Pages\ManageCustomerEwallets;
use App\Filament\Resources\CustomerEwallets\Schemas\CustomerEwalletForm;
use App\Filament\Resources\CustomerEwallets\Schemas\CustomerEwalletInfolist;
use App\Filament\Resources\CustomerEwallets\Tables\CustomerEwalletsTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerEwalletResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Saldo E-Wallet Customer';
    protected static ?string $modelLabel = 'Kelola E-Wallet';
    protected static ?string $pluralModelLabel = 'Kelola E-Wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Ewallet & Keuangan';

    public static function form(Schema $schema): Schema
    {
        return CustomerEwalletForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerEwalletInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerEwalletsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerEwallets::route('/'),
        ];
    }
}
