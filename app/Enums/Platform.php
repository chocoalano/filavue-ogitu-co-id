<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Platform: string implements HasColor, HasLabel
{
    case Web = 'web';
    case Mobile = 'mobile';

    public function getLabel(): string
    {
        return match ($this) {
            Platform::Web => 'Web',
            Platform::Mobile => 'Mobile',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            Platform::Web => 'info',
            Platform::Mobile => 'success',
        };
    }
}
