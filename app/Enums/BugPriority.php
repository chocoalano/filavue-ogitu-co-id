<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Prioritas penanganan bug oleh tim engineering.
 */
enum BugPriority: string implements HasColor, HasLabel
{
    case Urgent = 'urgent';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function getLabel(): string
    {
        return match ($this) {
            BugPriority::Urgent => 'Urgent',
            BugPriority::High => 'High',
            BugPriority::Medium => 'Medium',
            BugPriority::Low => 'Low',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugPriority::Urgent => 'danger',
            BugPriority::High => 'warning',
            BugPriority::Medium => 'info',
            BugPriority::Low => 'gray',
        };
    }
}
