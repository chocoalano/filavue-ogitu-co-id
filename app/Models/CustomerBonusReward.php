<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model CustomerBonusReward (Bonus Reward).
 *
 * Reward berupa cash/barang yang diraih berdasarkan pencapaian BV.
 * Bisa berjenis promotion (periode tertentu) atau lifetime.
 *
 * @property int $id
 * @property int $member_id Member penerima
 * @property string|null $reward_type Tipe reward (promotion|lifetime)
 * @property string $reward Nama reward
 * @property float $bv Business Volume syarat
 * @property float $amount Nominal reward
 * @property float|null $index_value Nilai index
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusReward extends BaseModel
{
    use HasFactory;

    public const CACHE_KEY_OVERVIEW = 'customer_bonus_reward_overview';

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
        'reward_type',
        'reward',
        'bv',
        'amount',
        'index_value',
        'status',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bv' => 'decimal:2',
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
        ];
    }

    /**
     * Member penerima reward.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }
}
