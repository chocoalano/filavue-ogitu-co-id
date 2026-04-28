<?php

namespace App\Filament\Resources\JneDestinations\Pages;

use App\Filament\Exports\JneDestinationExporter;
use App\Filament\Imports\JneDestinationImporter;
use App\Filament\Resources\JneDestinations\JneDestinationResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJneDestinations extends ManageRecords
{
    protected static string $resource = JneDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(JneDestinationImporter::class),
            ExportAction::make()
                ->exporter(JneDestinationExporter::class),
            CreateAction::make(),
        ];
    }
}
