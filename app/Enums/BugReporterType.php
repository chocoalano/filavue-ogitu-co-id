<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BugReporterType: string implements HasColor, HasLabel
{
    /** Dilaporkan oleh Customer (member/pembeli) */
    case Customer = 'customer';

    /** Dilaporkan oleh User admin/internal */
    case User = 'user';

    /** Dilaporkan tanpa login (anonymous) */
    case Anonymous = 'anonymous';

    public function getLabel(): string
    {
        return match ($this) {
            BugReporterType::Customer => 'Customer',
            BugReporterType::User => 'User (Internal)',
            BugReporterType::Anonymous => 'Anonymous',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugReporterType::Customer => 'primary',
            BugReporterType::User => 'warning',
            BugReporterType::Anonymous => 'gray',
        };
    }
}
