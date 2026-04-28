<?php

namespace App\Filament\Resources\JneBranches\Pages;

use App\Filament\Exports\JneBranchExporter;
use App\Filament\Imports\JneBranchImporter;
use App\Filament\Resources\JneBranches\JneBranchResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJneBranches extends ManageRecords
{
    protected static string $resource = JneBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(JneBranchImporter::class),
            ExportAction::make()
                ->exporter(JneBranchExporter::class),
            CreateAction::make(),
        ];
    }
}
