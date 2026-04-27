<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Tingkat keparahan dampak bug terhadap sistem/pengguna.
 */
enum BugSeverity: string implements HasColor, HasLabel
{
    /** Sistem tidak bisa digunakan sama sekali, data loss, security breach */
    case Critical = 'critical';

    /** Fitur utama terganggu, tidak ada workaround */
    case High = 'high';

    /** Fitur terganggu tapi ada workaround */
    case Medium = 'medium';

    /** Dampak kecil, kosmetik, atau tidak mempengaruhi fungsi */
    case Low = 'low';

    public function getLabel(): string
    {
        return match ($this) {
            BugSeverity::Critical => 'Critical',
            BugSeverity::High => 'High',
            BugSeverity::Medium => 'Medium',
            BugSeverity::Low => 'Low',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugSeverity::Critical => 'danger',
            BugSeverity::High => 'warning',
            BugSeverity::Medium => 'info',
            BugSeverity::Low => 'gray',
        };
    }
}
