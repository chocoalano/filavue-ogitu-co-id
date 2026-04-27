<?php

namespace App\Filament\Resources\CustomerBonusCashbacks\Pages;

use App\Filament\Resources\CustomerBonusCashbacks\CustomerBonusCashbackResource;
use App\Filament\Resources\CustomerBonusCashbacks\Widgets\CustomerBonusCashbackOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusCashbacks extends ManageRecords
{
    protected static string $resource = CustomerBonusCashbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusCashbackOverview::class,
        ];
    }
}
