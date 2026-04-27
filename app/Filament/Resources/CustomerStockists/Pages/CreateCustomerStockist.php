<?php

namespace App\Filament\Resources\CustomerStockists\Pages;

use App\Filament\Resources\CustomerStockists\CustomerStockistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerStockist extends CreateRecord
{
    protected static string $resource = CustomerStockistResource::class;
}
