<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BugSource: string implements HasColor, HasLabel
{
    case Storefront = 'storefront';
    case AdminConsole = 'admin_console';

    public function getLabel(): string
    {
        return match ($this) {
            BugSource::Storefront => 'Storefront',
            BugSource::AdminConsole => 'Admin Console',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugSource::Storefront => 'primary',
            BugSource::AdminConsole => 'warning',
        };
    }
}
