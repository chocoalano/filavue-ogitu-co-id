<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model OrderItem (Item Pesanan).
 *
 * Snapshot produk yang dipesan, termasuk dimensi dan berat untuk pengiriman.
 *
 * @property int $id
 * @property int $order_id Relasi ke order
 * @property int $product_id Relasi ke produk
 * @property string $name Nama produk (snapshot)
 * @property string $sku SKU produk (snapshot)
 * @property int $qty Jumlah item
 * @property float $unit_price Harga satuan
 * @property float $discount_amount Diskon per baris
 * @property float $row_total Total baris
 * @property int|null $weight_gram Berat (snapshot)
 * @property int|null $length_mm Panjang (snapshot)
 * @property int|null $width_mm Lebar (snapshot)
 * @property int|null $height_mm Tinggi (snapshot)
 * @property array|null $meta_json Metadata tambahan (JSON)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OrderItem extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'sku',
        'qty',
        'unit_price',
        'discount_amount',
        'row_total',
        'weight_gram',
        'length_mm',
        'width_mm',
        'height_mm',
        'meta_json',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'row_total' => 'decimal:2',
            'meta_json' => 'array',
        ];
    }

    /**
     * Order induk.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Produk asli (mungkin sudah berubah sejak order dibuat).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Item pengiriman yang berkaitan.
     */
    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * Item retur yang berkaitan.
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Review produk dari order item ini.
     */
    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class, 'order_item_id', 'id');
    }
}
