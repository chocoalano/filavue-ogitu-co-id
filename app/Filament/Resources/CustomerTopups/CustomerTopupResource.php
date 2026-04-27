<?php

namespace App\Filament\Resources\CustomerTopups;

use App\Filament\Resources\CustomerTopups\Pages\ManageCustomerTopups;
use App\Filament\Resources\CustomerTopups\Schemas\CustomerTopupForm;
use App\Filament\Resources\CustomerTopups\Schemas\CustomerTopupInfolist;
use App\Filament\Resources\CustomerTopups\Tables\CustomerTopupsTable;
use App\Models\CustomerWalletTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerTopupResource extends Resource
{
    protected static ?string $model = CustomerWalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $recordTitleAttribute = 'transaction_ref';
    protected static ?string $navigationLabel = 'Topup E-Wallet Customer';
    protected static ?string $modelLabel = 'Topup E-Wallet';
    protected static ?string $pluralModelLabel = 'Topup E-Wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Ewallet & Keuangan';

    public static function form(Schema $schema): Schema
    {
        return CustomerTopupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerTopupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTopupsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'topup');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerTopups::route('/'),
        ];
    }
}
