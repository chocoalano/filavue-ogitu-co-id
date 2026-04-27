<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerBvReward (Tracking BV Reward).
 *
 * Tracking omset kiri-kanan untuk penentuan reward berdasarkan BV.
 *
 * @property int $id
 * @property int $member_id Member
 * @property int $reward_id Reward target
 * @property float $omzet_left Omset kaki kiri
 * @property float $omzet_right Omset kaki kanan
 * @property int $status 0=belum tercapai, 1=tercapai
 * @property \Illuminate\Support\Carbon $created_on Tanggal pencatatan
 */
class CustomerBvReward extends BaseModel
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'reward_id',
        'omzet_left',
        'omzet_right',
        'status',
        'created_on',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'omzet_left' => 'decimal:2',
            'omzet_right' => 'decimal:2',
            'created_on' => 'datetime',
        ];
    }

    /**
     * Member pemilik BV reward.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Definisi reward target.
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}
