<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use App\Filament\Resources\Carts\Widgets\CartOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CartOverview::class,
        ];
    }
}
