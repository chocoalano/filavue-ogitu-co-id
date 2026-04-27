<?php

namespace App\Filament\Resources\Wishlists\Pages;

use App\Filament\Resources\Wishlists\WishlistResource;
use App\Filament\Resources\Wishlists\Widgets\WishlistOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWishlists extends ListRecords
{
    protected static string $resource = WishlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WishlistOverview::class,
        ];
    }
}
