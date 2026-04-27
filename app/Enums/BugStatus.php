<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BugStatus: string implements HasColor, HasLabel
{
    /** Baru dilaporkan, belum ditinjau */
    case Open = 'open';

    /** Sedang dianalisis oleh QA */
    case UnderReview = 'under_review';

    /** Dikonfirmasi sebagai bug, siap dikerjakan */
    case Confirmed = 'confirmed';

    /** Sedang dikerjakan oleh developer */
    case InProgress = 'in_progress';

    /** Perbaikan selesai, menunggu verifikasi */
    case Resolved = 'resolved';

    /** Terverifikasi selesai dan ditutup */
    case Closed = 'closed';

    /** Ditolak — bukan bug atau tidak dapat direproduksi */
    case Rejected = 'rejected';

    /** Duplikat dari laporan bug lain */
    case Duplicate = 'duplicate';

    public function getLabel(): string
    {
        return match ($this) {
            BugStatus::Open => 'Open',
            BugStatus::UnderReview => 'Under Review',
            BugStatus::Confirmed => 'Confirmed',
            BugStatus::InProgress => 'In Progress',
            BugStatus::Resolved => 'Resolved',
            BugStatus::Closed => 'Closed',
            BugStatus::Rejected => 'Rejected',
            BugStatus::Duplicate => 'Duplicate',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugStatus::Open => 'warning',
            BugStatus::UnderReview => 'info',
            BugStatus::Confirmed => 'primary',
            BugStatus::InProgress => 'primary',
            BugStatus::Resolved => 'success',
            BugStatus::Closed => 'gray',
            BugStatus::Rejected => 'danger',
            BugStatus::Duplicate => 'gray',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            BugStatus::Open,
            BugStatus::UnderReview,
            BugStatus::Confirmed,
            BugStatus::InProgress,
        ]);
    }
}
