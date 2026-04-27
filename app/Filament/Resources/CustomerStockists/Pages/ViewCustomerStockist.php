<?php

namespace App\Filament\Resources\CustomerStockists\Pages;

use App\Filament\Resources\CustomerStockists\CustomerStockistResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerStockist extends ViewRecord
{
    protected static string $resource = CustomerStockistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
