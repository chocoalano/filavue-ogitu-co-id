<?php

namespace App\Filament\Resources\ReturnOrders\Pages;

use App\Filament\Resources\ReturnOrders\ReturnOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReturnOrders extends ManageRecords
{
    protected static string $resource = ReturnOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
