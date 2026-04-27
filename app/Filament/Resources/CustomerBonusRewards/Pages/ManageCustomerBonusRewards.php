<?php

namespace App\Filament\Resources\CustomerBonusRewards\Pages;

use App\Filament\Resources\CustomerBonusRewards\CustomerBonusRewardResource;
use App\Filament\Resources\CustomerBonusRewards\Widgets\CustomerBonusRewardOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusRewards extends ManageRecords
{
    protected static string $resource = CustomerBonusRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusRewardOverview::class,
        ];
    }
}
