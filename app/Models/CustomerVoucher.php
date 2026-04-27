<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model CustomerVoucher — Voucher yang di-assign admin ke customer tertentu.
 *
 * @property int $id
 * @property int $promotion_id
 * @property int $customer_id
 * @property string $code Kode unik voucher
 * @property string $discount_type 'percent' | 'fixed'
 * @property float $discount_amount Nilai diskon (persen atau nominal Rp)
 * @property float|null $min_spend Minimal belanja
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_until
 * @property bool $is_active
 * @property Carbon|null $used_at
 * @property int|null $used_by_order_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CustomerVoucher extends BaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'promotion_id',
        'customer_id',
        'code',
        'discount_type',
        'discount_amount',
        'min_spend',
        'valid_from',
        'valid_until',
        'is_active',
        'used_at',
        'used_by_order_id',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'discount_amount' => 'float',
            'min_spend' => 'float',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
            'used_at' => 'datetime',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function usedByOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_by_order_id');
    }

    /**
     * Apakah voucher ini masih berlaku dan belum digunakan.
     */
    public function isUsable(): bool
    {
        if (! $this->is_active || $this->used_at !== null) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
