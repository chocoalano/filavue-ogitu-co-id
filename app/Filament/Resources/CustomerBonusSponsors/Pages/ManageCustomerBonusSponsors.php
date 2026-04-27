<?php

namespace App\Filament\Resources\CustomerBonusSponsors\Pages;

use App\Filament\Resources\CustomerBonusSponsors\CustomerBonusSponsorResource;
use App\Filament\Resources\CustomerBonusSponsors\Widgets\CustomerBonusSponsorOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusSponsors extends ManageRecords
{
    protected static string $resource = CustomerBonusSponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusSponsorOverview::class,
        ];
    }
}
