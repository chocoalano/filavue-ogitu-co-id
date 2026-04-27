<?php

namespace App\Filament\Resources\CustomerStockists\Pages;

use App\Filament\Resources\CustomerStockists\CustomerStockistResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerStockist extends EditRecord
{
    protected static string $resource = CustomerStockistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
