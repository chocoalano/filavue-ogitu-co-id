<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CommodityCodes\CommodityCodeResource;
use App\Filament\Resources\ShippingTargets\ShippingTargetResource;
use Filament\Widgets\Widget;

class ShoppingDataInstructionCalloutWidget extends Widget
{
    protected static ?int $sort = -4;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.shopping-data-instruction-callout-widget';

    public function getCommodityCodeUrl(): string
    {
        return CommodityCodeResource::getUrl();
    }

    public function getShippingTargetUrl(): string
    {
        return ShippingTargetResource::getUrl();
    }
}
