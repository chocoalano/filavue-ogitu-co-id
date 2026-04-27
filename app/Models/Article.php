<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Article (Artikel Blog).
 *
 * Artikel/blog post dengan dukungan SEO dan soft-delete.
 *
 * @property int $id
 * @property string $title Judul artikel
 * @property string $slug URL-friendly identifier
 * @property string|null $seo_title Judul SEO
 * @property string|null $seo_description Deskripsi SEO
 * @property string|null $image_banner Banner artikel untuk listing/detail
 * @property bool $is_published Status publikasi
 * @property \Illuminate\Support\Carbon|null $published_at Tanggal publikasi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Article extends BaseModel
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'image_banner',
        'is_published',
        'published_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Konten (body) artikel, mendukung multi-section.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(ArticleContent::class);
    }

    /**
     * Konten utama artikel (1 artikel = 1 content row).
     */
    public function content(): HasOne
    {
        return $this->hasOne(ArticleContent::class);
    }
}
