<?php

namespace App\Filament\Resources\CustomerStockists\Pages;

use App\Filament\Resources\CustomerStockists\CustomerStockistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerStockists extends ListRecords
{
    protected static string $resource = CustomerStockistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
