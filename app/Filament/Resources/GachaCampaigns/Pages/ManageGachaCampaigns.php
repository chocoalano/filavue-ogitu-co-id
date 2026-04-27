<?php

namespace App\Filament\Resources\GachaCampaigns\Pages;

use App\Filament\Resources\GachaCampaigns\GachaCampaignsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGachaCampaigns extends ManageRecords
{
    protected static string $resource = GachaCampaignsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
