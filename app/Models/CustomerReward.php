<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerReward (Pencapaian Reward Customer).
 *
 * Tracking pencapaian reward oleh member.
 *
 * @property int $id
 * @property int $member_id Member yang meraih
 * @property int $reward_id Reward yang diraih
 * @property string $reward Nama reward
 * @property float $total_bv_achieved Total BV yang dicapai
 * @property int $type Tipe (0=periode, 1=permanen)
 * @property int $status 0=pending, 1=achieved
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class CustomerReward extends BaseModel
{
    public $timestamps = false;

    protected $table = 'customers_rewards';

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'reward_id',
        'reward',
        'total_bv_achieved',
        'type',
        'status',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'total_bv_achieved' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Member yang meraih reward.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Definisi reward.
     */
    public function rewardDefinition(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
