<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model ContentCategory (Kategori Konten Ogitu Club).
 *
 * Kategori hierarkis untuk konten Ogitu Club
 * (Marketing Kit, Academy, Leaderboard, Rules & FAQ, dll).
 *
 * @property int $id
 * @property int|null $parent_id Kategori induk
 * @property string $name Nama kategori
 * @property string $slug URL-friendly identifier
 * @property string|null $icon_key Kunci ikon Material Symbols (e.g. campaign_rounded)
 * @property string|null $accent_hex Warna aksen hex (e.g. #60A5FA)
 * @property int $sort_order Urutan tampil di UI
 * @property string|null $thumbnail_url URL thumbnail kursus
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ContentCategory extends BaseModel
{
    use HasFactory;

    protected $table = 'contents_category';

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon_key',
        'accent_hex',
        'sort_order',
        'thumbnail_url',
    ];

    /**
     * Kategori induk.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-kategori.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Konten dalam kategori ini.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'category_id');
    }
}
