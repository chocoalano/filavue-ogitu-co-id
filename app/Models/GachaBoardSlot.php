<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GachaBoardSlot extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_POPPED = 'popped';
    public const STATUS_VOID = 'void';

    protected $fillable = [
        'board_id',
        'slot_no',
        'balloon_code',
        'row_no',
        'col_no',
        'balloon_color',
        'reward_item_id',
        'reward_snapshot_json',
        'status',
        'reserved_by_customer_id',
        'reserved_at',
        'reservation_expires_at',
        'popped_by_customer_id',
        'popped_at',
        'checksum_hash',
    ];

    protected $casts = [
        'board_id' => 'integer',
        'slot_no' => 'integer',
        'row_no' => 'integer',
        'col_no' => 'integer',
        'reward_item_id' => 'integer',
        'reward_snapshot_json' => 'array',
        'reserved_by_customer_id' => 'integer',
        'reserved_at' => 'datetime',
        'reservation_expires_at' => 'datetime',
        'popped_by_customer_id' => 'integer',
        'popped_at' => 'datetime',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(GachaBoard::class, 'board_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }

    public function reservedByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'reserved_by_customer_id');
    }

    public function poppedByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'popped_by_customer_id');
    }

    public function draw(): HasOne
    {
        return $this->hasOne(GachaDraw::class, 'slot_id');
    }
}
