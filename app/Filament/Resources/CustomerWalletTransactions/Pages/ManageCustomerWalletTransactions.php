<?php

namespace App\Filament\Resources\CustomerWalletTransactions\Pages;

use App\Filament\Resources\CustomerWalletTransactions\CustomerWalletTransactionResource;
use App\Filament\Resources\CustomerWalletTransactions\Widgets\CustomerWalletTransactionOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerWalletTransactions extends ManageRecords
{
    protected static string $resource = CustomerWalletTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerWalletTransactionOverview::class,
        ];
    }
}
