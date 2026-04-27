<?php

namespace App\Filament\Resources\CustomerWithdrawals;

use App\Filament\Resources\CustomerWithdrawals\Pages\ManageCustomerWithdrawals;
use App\Filament\Resources\CustomerWithdrawals\Schemas\CustomerWithdrawalForm;
use App\Filament\Resources\CustomerWithdrawals\Schemas\CustomerWithdrawalInfolist;
use App\Filament\Resources\CustomerWithdrawals\Tables\CustomerWithdrawalsTable;
use App\Models\CustomerWalletTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerWithdrawalResource extends Resource
{
    protected static ?string $model = CustomerWalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $recordTitleAttribute = 'transaction_ref';
    protected static ?string $navigationLabel = 'Penarikan E-Wallet Customer';
    protected static ?string $modelLabel = 'Penarikan E-Wallet';
    protected static ?string $pluralModelLabel = 'Penarikan E-Wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Ewallet & Keuangan';

    public static function form(Schema $schema): Schema
    {
        return CustomerWithdrawalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerWithdrawalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerWithdrawalsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'withdrawal');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerWithdrawals::route('/'),
        ];
    }
}
