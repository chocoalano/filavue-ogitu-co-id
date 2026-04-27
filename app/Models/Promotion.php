<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model Promotion (Promosi/Campaign).
 *
 * Campaign promosi yang bisa berupa bundle, flash sale, dll.
 * Dapat ditampilkan di homepage atau halaman tertentu.
 *
 * @property int $id
 * @property string $code Kode promo unik
 * @property string $name Nama promosi
 * @property string $type Tipe (bundle|flash_sale|discount)
 * @property string|null $landing_slug Slug landing page
 * @property string|null $description Deskripsi
 * @property string|null $image Gambar promosi
 * @property Carbon $start_at Mulai berlaku
 * @property Carbon $end_at Selesai berlaku
 * @property bool $is_active Status aktif
 * @property int $priority Prioritas tampil
 * @property int|null $max_redemption Batas pemakaian total
 * @property int|null $per_user_limit Batas pemakaian per user
 * @property array|null $conditions_json Kondisi promo (JSON)
 * @property string|null $show_on Ditampilkan di mana (homepage, dll)
 * @property string|null $custom_html HTML kustom
 * @property string|null $page Halaman target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Promotion extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'type',
        'landing_slug',
        'description',
        'image',
        'start_at',
        'end_at',
        'is_active',
        'priority',
        'max_redemption',
        'per_user_limit',
        'conditions_json',
        'show_on',
        'custom_html',
        'page',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
            'conditions_json' => 'array',
        ];
    }

    /**
     * Voucher yang di-assign ke customer dari promosi ini.
     */
    public function customerVouchers(): HasMany
    {
        return $this->hasMany(CustomerVoucher::class);
    }

    /**
     * Produk yang termasuk dalam promosi ini.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot('min_qty', 'discount_value', 'discount_percent', 'bundle_price')
            ->withTimestamps();
    }
}
