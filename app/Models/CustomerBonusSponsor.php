<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model CustomerBonusSponsor (Bonus Sponsor).
 *
 * Bonus yang diterima member ketika merekrut downline baru.
 * Dihitung berdasarkan komponen b_sponsor dari produk yang dibeli downline.
 *
 * @property int $id
 * @property int|null $member_id Member penerima bonus sponsor
 * @property int|null $from_member_id Downline yang memicu bonus
 * @property float $amount Nominal bonus
 * @property float $index_value Nilai index
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusSponsor extends BaseModel
{
    use HasFactory;

    public const CACHE_KEY_OVERVIEW = 'customer_bonus_sponsor_overview';

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
        'from_member_id',
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
     * Member penerima bonus.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Downline yang memicu bonus sponsor.
     */
    public function fromMember(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'from_member_id');
    }
}
