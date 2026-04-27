<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Category (Kategori Produk).
 *
 * Kategori bersifat hierarkis (parent-child) untuk mengelompokkan produk.
 *
 * @property int $id
 * @property int|null $parent_id ID kategori induk
 * @property string $slug URL-friendly identifier
 * @property string $name Nama kategori
 * @property string|null $description Deskripsi kategori
 * @property int $sort_order Urutan tampil
 * @property bool $is_active Status aktif kategori
 * @property string|null $image Path gambar kategori
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Category extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'slug',
        'name',
        'description',
        'sort_order',
        'is_active',
        'image',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kategori induk.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-kategori (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Produk dalam kategori ini (many-to-many).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }
}
