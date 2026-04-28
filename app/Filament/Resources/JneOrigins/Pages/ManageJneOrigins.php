<?php

namespace App\Filament\Resources\JneOrigins\Pages;

use App\Filament\Exports\JneOriginExporter;
use App\Filament\Imports\JneOriginImporter;
use App\Filament\Resources\JneOrigins\JneOriginResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJneOrigins extends ManageRecords
{
    protected static string $resource = JneOriginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(JneOriginImporter::class),
            ExportAction::make()
                ->exporter(JneOriginExporter::class),
            CreateAction::make(),
        ];
    }
}
