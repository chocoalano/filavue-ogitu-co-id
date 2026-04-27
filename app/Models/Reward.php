<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Reward (Definisi Reward).
 *
 * Master data reward yang bisa diraih member.
 * Ada dua tipe: periode (berlaku dalam rentang waktu) dan permanen (lifetime).
 *
 * @property int $id
 * @property string|null $code Kode reward
 * @property string $name Nama reward
 * @property string|null $reward Deskripsi hadiah
 * @property float $value Nilai reward
 * @property \Illuminate\Support\Carbon|null $start Tanggal mulai (untuk tipe periode)
 * @property \Illuminate\Support\Carbon|null $end Tanggal selesai (untuk tipe periode)
 * @property float $bv Business Volume syarat
 * @property int $type 0=periode, 1=permanen
 * @property int $status Status aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class Reward extends BaseModel
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'reward',
        'value',
        'start',
        'end',
        'bv',
        'type',
        'status',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'bv' => 'decimal:2',
            'start' => 'date',
            'end' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Customer yang meraih reward ini.
     */
    public function customerRewards(): HasMany
    {
        return $this->hasMany(CustomerReward::class, 'reward_id');
    }

    /**
     * BV tracking untuk reward ini.
     */
    public function bvRewards(): HasMany
    {
        return $this->hasMany(CustomerBvReward::class, 'reward_id');
    }
}
