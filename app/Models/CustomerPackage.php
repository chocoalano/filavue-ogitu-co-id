<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model CustomerPackage (Paket Member).
 *
 * Jenis paket member berdasarkan omset.
 *
 * @property int $id
 * @property string $name Nama paket
 * @property string $alias Alias singkat
 * @property float $price Harga paket
 * @property int $pv Point value
 * @property int $pr Point referral
 * @property float $sponsor Komponen bonus sponsor
 * @property float $discount Diskon paket
 */
class CustomerPackage extends BaseModel
{
    public $timestamps = false;

    protected $table = 'customer_package';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'alias',
        'price',
        'pv',
        'pr',
        'sponsor',
        'discount',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sponsor' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Customer yang memiliki paket ini.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'package_id');
    }
}
