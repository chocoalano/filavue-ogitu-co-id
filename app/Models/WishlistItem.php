<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model WishlistItem (Item Wishlist).
 *
 * Produk individu dalam wishlist customer.
 *
 * @property int $id
 * @property int $wishlist_id Relasi ke wishlist
 * @property int $product_id Relasi ke produk
 * @property string $product_name Nama produk (snapshot)
 * @property string $product_sku SKU produk (snapshot)
 * @property array|null $meta_json Metadata tambahan (JSON)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WishlistItem extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'product_name',
        'product_sku',
        'meta_json',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'meta_json' => 'array',
        ];
    }

    /**
     * Wishlist induk.
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Produk yang di-wishlist.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
