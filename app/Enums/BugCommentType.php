<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Tipe komentar/entri pada log penanganan bug.
 *
 * Membedakan antara komentar manual dari tim dengan entri otomatis
 * yang dihasilkan sistem saat ada perubahan status atau penugasan.
 */
enum BugCommentType: string implements HasColor, HasLabel
{
    /** Komentar umum — update progress, pertanyaan, atau informasi tambahan */
    case Comment = 'comment';

    /** Catatan internal — hanya terlihat oleh tim, tidak dikirim ke pelapor */
    case InternalNote = 'internal_note';

    /** Langkah penanganan — dokumentasi tindakan yang diambil untuk menyelesaikan bug */
    case HandlingStep = 'handling_step';

    /** Entri otomatis saat status bug berubah */
    case StatusChange = 'status_change';

    /** Entri otomatis saat bug di-assign ke anggota tim */
    case AssignmentChange = 'assignment_change';

    /** Entri otomatis saat kategori error ditetapkan atau diubah */
    case CategoryChange = 'category_change';

    /** Komentar penutup saat bug di-resolve, closed, atau rejected */
    case Resolution = 'resolution';

    public function getLabel(): string
    {
        return match ($this) {
            BugCommentType::Comment => 'Comment',
            BugCommentType::InternalNote => 'Internal Note',
            BugCommentType::HandlingStep => 'Handling Step',
            BugCommentType::StatusChange => 'Status Changed',
            BugCommentType::AssignmentChange => 'Assignment Changed',
            BugCommentType::CategoryChange => 'Category Set',
            BugCommentType::Resolution => 'Resolution',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugCommentType::Comment => 'gray',
            BugCommentType::InternalNote => 'warning',
            BugCommentType::HandlingStep => 'primary',
            BugCommentType::StatusChange => 'info',
            BugCommentType::AssignmentChange => 'info',
            BugCommentType::CategoryChange => 'info',
            BugCommentType::Resolution => 'success',
        };
    }

    /** Apakah entri ini dibuat otomatis oleh sistem (bukan manual dari user). */
    public function isSystemGenerated(): bool
    {
        return in_array($this, [
            BugCommentType::StatusChange,
            BugCommentType::AssignmentChange,
            BugCommentType::CategoryChange,
        ]);
    }

    /** Apakah entri ini merupakan langkah penanganan yang terdokumentasi. */
    public function isHandlingRecord(): bool
    {
        return in_array($this, [
            BugCommentType::HandlingStep,
            BugCommentType::Resolution,
        ]);
    }
}
