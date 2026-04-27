<?php

namespace App\Filament\Resources\CustomerStockists\Schemas;

use App\Filament\Resources\Customers\Schemas\CustomerInfolist as BaseCustomerInfolist;
use Filament\Schemas\Schema;

class CustomerStockistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return BaseCustomerInfolist::configure($schema);
    }
}
