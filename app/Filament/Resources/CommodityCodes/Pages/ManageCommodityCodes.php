<?php

namespace App\Filament\Resources\CommodityCodes\Pages;

use App\Filament\Resources\CommodityCodes\CommodityCodeResource;
use App\Filament\Resources\CommodityCodes\Exports\CommodityCodeExporter;
use App\Filament\Resources\CommodityCodes\Imports\CommodityCodeImporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCommodityCodes extends ManageRecords
{
    protected static string $resource = CommodityCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Import CSV')
                ->importer(CommodityCodeImporter::class)
                ->fileRules([
                    'max:10240',
                ])
                ->chunkSize(250),
            ExportAction::make()
                ->label('Export CSV/XLSX')
                ->exporter(CommodityCodeExporter::class),
        ];
    }
}
