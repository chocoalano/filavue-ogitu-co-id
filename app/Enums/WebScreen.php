<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WebScreen: string implements HasLabel
{
    case Desktop = 'desktop';
    case Tablet = 'tablet';
    case Smartphone = 'smartphone';

    public function getLabel(): string
    {
        return match ($this) {
            WebScreen::Desktop => 'Desktop',
            WebScreen::Tablet => 'Tablet',
            WebScreen::Smartphone => 'Smartphone',
        };
    }
}
