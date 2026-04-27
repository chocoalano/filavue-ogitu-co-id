<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Model BugReportAttachment (Lampiran Laporan Bug).
 *
 * File lampiran (screenshot, log file, video, dll) yang dilampirkan
 * pada suatu laporan bug untuk membantu tim engineering mereproduksi
 * dan memahami bug tersebut.
 *
 * @property int $id
 * @property int $bug_report_id ID laporan bug
 * @property string $file_path Path file di storage
 * @property string $file_name Nama asli file yang diupload
 * @property string|null $mime_type MIME type file
 * @property int|null $file_size Ukuran file dalam bytes
 * @property string|null $caption Keterangan singkat tentang file
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BugReportAttachment extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updated(function (self $attachment): void {
            if (! $attachment->wasChanged('file_path')) {
                return;
            }

            $originalPath = $attachment->getOriginal('file_path');

            if (blank($originalPath) || $originalPath === $attachment->file_path) {
                return;
            }

            Storage::disk('public')->delete($originalPath);
        });

        static::deleted(function (self $attachment): void {
            if (blank($attachment->file_path)) {
                return;
            }

            Storage::disk('public')->delete($attachment->file_path);
        });
    }

    /** @var list<string> */
    protected $fillable = [
        'bug_report_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'caption',
    ];

    /**
     * Laporan bug yang memiliki lampiran ini.
     *
     * @return BelongsTo<BugReport, $this>
     */
    public function bugReport(): BelongsTo
    {
        return $this->belongsTo(BugReport::class);
    }

    /**
     * Cek apakah lampiran ini berupa gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * Ukuran file dalam format yang mudah dibaca (KB/MB).
     */
    public function readableFileSize(): string
    {
        if ($this->file_size === null) {
            return '-';
        }

        if ($this->file_size < 1024) {
            return $this->file_size.' B';
        }

        if ($this->file_size < 1_048_576) {
            return round($this->file_size / 1024, 1).' KB';
        }

        return round($this->file_size / 1_048_576, 1).' MB';
    }
}
