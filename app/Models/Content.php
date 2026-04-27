<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Content (Konten / Modul Ogitu Club).
 *
 * Konten edukasi dan marketing untuk member Ogitu Club.
 * Satu Content merepresentasikan satu modul/pelajaran dalam sebuah kursus (ContentCategory).
 *
 * @property int $id
 * @property int|null $category_id Kursus induk (ContentCategory)
 * @property int $sort_order Urutan modul dalam kursus
 * @property string $title Judul modul
 * @property string $slug URL-friendly identifier
 * @property string|null $content Konten HTML
 * @property string|null $file Path file lampiran
 * @property string|null $vlink Video link (YouTube, dll)
 * @property string|null $content_type Tipe konten (video|article|pdf)
 * @property string|null $thumbnail_url URL thumbnail modul
 * @property int|null $duration_sec Durasi video dalam detik
 * @property string|null $status Status (published|draft|archived)
 * @property int|null $created_by User yang membuat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Content extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'sort_order',
        'title',
        'slug',
        'content',
        'file',
        'vlink',
        'content_type',
        'thumbnail_url',
        'duration_sec',
        'status',
        'created_by',
    ];

    /**
     * Kursus induk (ContentCategory).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    /**
     * User admin yang membuat konten.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Progres per-customer untuk modul ini.
     *
     * @return HasMany<CustomerModuleProgress>
     */
    public function moduleProgresses(): HasMany
    {
        return $this->hasMany(CustomerModuleProgress::class);
    }
}
