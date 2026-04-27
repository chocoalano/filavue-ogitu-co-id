<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MobileType: string implements HasLabel
{
    case Android = 'android';
    case Ios = 'ios';

    public function getLabel(): string
    {
        return match ($this) {
            MobileType::Android => 'Android',
            MobileType::Ios => 'iOS',
        };
    }
}
