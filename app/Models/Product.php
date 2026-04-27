<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model Product.
 *
 * Produk yang dijual di toko.
 *
 * @property int $id
 * @property string $commodity_code Kode unik produk
 * @property string $sku Kode unik produk
 * @property string $slug URL-friendly identifier
 * @property string $name Nama produk
 * @property string|null $short_desc Deskripsi singkat
 * @property string|null $long_desc Deskripsi lengkap
 * @property string|null $brand Merek produk
 * @property int|null $warranty_months Garansi dalam bulan
 * @property float $base_price Harga dasar
 * @property float $discount Diskon produk
 * @property string $currency Mata uang (default IDR)
 * @property int $stock Jumlah stok tersedia
 * @property int|null $weight_gram Berat dalam gram
 * @property int|null $length_mm Panjang dalam mm
 * @property int|null $width_mm Lebar dalam mm
 * @property int|null $height_mm Tinggi dalam mm
 * @property float $pv Point value untuk penghitungan bonus
 * @property float $b_sponsor Nilai bonus sponsor per unit
 * @property float $b_royalty Nilai bonus royalty per unit
 * @property float $b_cashback Nilai bonus cashback per unit
 * @property bool $is_active Apakah produk aktif dijual
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Product extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'commodity_code',
        'sku',
        'slug',
        'name',
        'short_desc',
        'long_desc',
        'brand',
        'warranty_months',
        'base_price',
        'discount',
        'currency',
        'stock',
        'weight_gram',
        'length_mm',
        'width_mm',
        'height_mm',
        'pv',
        'b_sponsor',
        'b_royalty',
        'b_cashback',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'pv' => 'decimal:2',
            'b_sponsor' => 'decimal:2',
            'b_royalty' => 'decimal:2',
            'b_cashback' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kategori-kategori yang dimiliki produk (many-to-many).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Media (gambar/video) produk.
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    /**
     * Gambar utama produk.
     */
    public function primaryMedia(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->where('is_primary', true);
    }

    /**
     * Review dari customer untuk produk ini.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Item di order yang mengandung produk ini.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Item di cart yang mengandung produk ini.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Promosi yang mencakup produk ini.
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')
            ->withPivot('min_qty', 'discount_value', 'discount_percent', 'bundle_price')
            ->withTimestamps();
    }

    public function rewardItems()
    {
        return $this->hasMany(RewardItem::class, 'product_id');
    }
}
