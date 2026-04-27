<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model CustomerBonusCashback (Bonus Cashback / Plan B).
 *
 * Cashback dari pembelian produk (b_cashback).
 *
 * @property int $id
 * @property int $member_id Member penerima cashback
 * @property int|null $order_id Order yang memicu cashback
 * @property float $amount Nominal cashback
 * @property float|null $index_value Nilai index
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusCashback extends BaseModel
{
    use HasFactory;

    public const CACHE_KEY_OVERVIEW = 'customer_bonus_cashback_overview';

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY_OVERVIEW);

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'order_id',
        'amount',
        'index_value',
        'status',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
        ];
    }

    /**
     * Member penerima cashback.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Order yang memicu cashback.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
