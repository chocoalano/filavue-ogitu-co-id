<?php

namespace App\Filament\Resources\GachaBoards\Pages;

use App\Filament\Resources\GachaBoards\GachaBoardsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGachaBoards extends ManageRecords
{
    protected static string $resource = GachaBoardsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
