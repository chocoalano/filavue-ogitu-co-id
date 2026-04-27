<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CartItem (Item Keranjang).
 *
 * Setiap baris produk dalam keranjang belanja.
 *
 * @property int $id
 * @property int $cart_id Relasi ke keranjang
 * @property int $product_id Relasi ke produk
 * @property int $qty Jumlah item
 * @property float $unit_price Harga satuan saat ditambahkan
 * @property string $currency Mata uang
 * @property string $product_sku SKU produk (snapshot)
 * @property string $product_name Nama produk (snapshot)
 * @property float $row_total Total baris (qty * unit_price)
 * @property array|null $meta_json Metadata tambahan (JSON)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CartItem extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'unit_price',
        'currency',
        'product_sku',
        'product_name',
        'row_total',
        'meta_json',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'row_total' => 'decimal:2',
            'meta_json' => 'array',
        ];
    }

    /**
     * Keranjang induk.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Produk yang ada di item ini.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
