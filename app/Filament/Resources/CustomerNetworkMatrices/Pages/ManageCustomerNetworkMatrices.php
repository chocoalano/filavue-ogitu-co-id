<?php

namespace App\Filament\Resources\CustomerNetworkMatrices\Pages;

use App\Filament\Resources\CustomerNetworkMatrices\CustomerNetworkMatrixResource;
use App\Filament\Resources\CustomerNetworkMatrices\Widgets\CustomerNetworkMatrixOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerNetworkMatrices extends ManageRecords
{
    protected static string $resource = CustomerNetworkMatrixResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerNetworkMatrixOverview::class,
        ];
    }
}
