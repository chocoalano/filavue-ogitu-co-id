<?php

namespace App\Filament\Resources\CustomerWalletTransactions;

use App\Filament\Resources\CustomerWalletTransactions\Pages\ManageCustomerWalletTransactions;
use App\Filament\Resources\CustomerWalletTransactions\Schemas\CustomerWalletTransactionForm;
use App\Filament\Resources\CustomerWalletTransactions\Schemas\CustomerWalletTransactionInfolist;
use App\Filament\Resources\CustomerWalletTransactions\Tables\CustomerWalletTransactionsTable;
use App\Models\CustomerWalletTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerWalletTransactionResource extends Resource
{
    protected static ?string $model = CustomerWalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'transaction_ref';
    protected static ?string $navigationLabel = 'Transaksi E-Wallet Customer';
    protected static ?string $modelLabel = 'Transaksi E-Wallet';
    protected static ?string $pluralModelLabel = 'Transaksi E-Wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Ewallet & Keuangan';

    public static function form(Schema $schema): Schema
    {
        return CustomerWalletTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerWalletTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerWalletTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerWalletTransactions::route('/'),
        ];
    }
}
