<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ProductMedia (Media Produk).
 *
 * Gambar dan video produk dengan dukungan primary image dan sorting.
 *
 * @property int $id
 * @property int $product_id Relasi ke produk
 * @property string $url Path/URL media
 * @property string $type Tipe media (image|video)
 * @property string|null $alt_text Alt text untuk aksesibilitas
 * @property int $sort_order Urutan tampil
 * @property bool $is_primary Apakah gambar utama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProductMedia extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'url',
        'type',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Produk pemilik media ini.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
