<?php

namespace App\Filament\Resources\GachaDraws\Pages;

use App\Filament\Resources\GachaDraws\GachaDrawsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGachaDraws extends ManageRecords
{
    protected static string $resource = GachaDrawsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
