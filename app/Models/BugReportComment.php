<?php

namespace App\Models;

use App\Enums\BugCommentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model BugReportComment (Komentar / Log Penanganan Bug).
 *
 * Merepresentasikan satu entri dalam log penanganan bug. Dapat berupa
 * komentar manual dari tim (comment, internal_note, handling_step, resolution)
 * maupun entri otomatis yang dihasilkan sistem saat ada perubahan
 * status, penugasan, atau kategori error.
 *
 * @property int $id
 * @property int $bug_report_id ID laporan bug
 * @property int|null $user_id ID user yang membuat entri (null = sistem)
 * @property BugCommentType $type Tipe entri
 * @property string $body Isi komentar atau deskripsi
 * @property string|null $old_value Nilai lama (untuk entri perubahan otomatis)
 * @property string|null $new_value Nilai baru (untuk entri perubahan otomatis)
 * @property int|null $step_number Nomor urut langkah penanganan
 * @property bool $is_pinned Apakah di-pin sebagai komentar penting
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BugReportComment extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'bug_report_id',
        'user_id',
        'type',
        'body',
        'old_value',
        'new_value',
        'step_number',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'type' => BugCommentType::class,
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * Laporan bug yang memiliki komentar ini.
     *
     * @return BelongsTo<BugReport, $this>
     */
    public function bugReport(): BelongsTo
    {
        return $this->belongsTo(BugReport::class);
    }

    /**
     * User admin yang membuat komentar (null jika dibuat otomatis sistem).
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Apakah entri ini dibuat otomatis oleh sistem.
     */
    public function isSystemGenerated(): bool
    {
        return $this->type->isSystemGenerated();
    }

    /**
     * Apakah entri ini merupakan langkah penanganan terdokumentasi.
     */
    public function isHandlingRecord(): bool
    {
        return $this->type->isHandlingRecord();
    }

    /**
     * Helper untuk membuat entri perubahan status secara otomatis.
     */
    public static function recordStatusChange(
        int $bugReportId,
        string $oldStatus,
        string $newStatus,
        int $userId,
    ): static {
        return static::create([
            'bug_report_id' => $bugReportId,
            'user_id' => $userId,
            'type' => BugCommentType::StatusChange,
            'body' => "Status berubah dari **{$oldStatus}** menjadi **{$newStatus}**.",
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
        ]);
    }

    /**
     * Helper untuk membuat entri perubahan kategori error secara otomatis.
     */
    public static function recordCategoryChange(
        int $bugReportId,
        ?string $oldCategory,
        string $newCategory,
        int $userId,
    ): static {
        $old = $oldCategory ?? 'belum dikategorikan';

        return static::create([
            'bug_report_id' => $bugReportId,
            'user_id' => $userId,
            'type' => BugCommentType::CategoryChange,
            'body' => "Kategori error ditetapkan dari **{$old}** menjadi **{$newCategory}**.",
            'old_value' => $oldCategory,
            'new_value' => $newCategory,
        ]);
    }

    /**
     * Helper untuk membuat entri perubahan penugasan secara otomatis.
     */
    public static function recordAssignmentChange(
        int $bugReportId,
        ?string $oldAssignee,
        ?string $newAssignee,
        int $userId,
    ): static {
        $body = match (true) {
            $oldAssignee === null => "Bug di-assign kepada **{$newAssignee}**.",
            $newAssignee === null => "Penugasan kepada **{$oldAssignee}** dihapus.",
            default => "Penugasan dipindah dari **{$oldAssignee}** ke **{$newAssignee}**.",
        };

        return static::create([
            'bug_report_id' => $bugReportId,
            'user_id' => $userId,
            'type' => BugCommentType::AssignmentChange,
            'body' => $body,
            'old_value' => $oldAssignee,
            'new_value' => $newAssignee,
        ]);
    }
}
