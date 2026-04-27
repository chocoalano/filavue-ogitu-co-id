<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardInventoryLedger extends Model
{
    protected $fillable = [
        'reward_item_id',
        'movement_type',
        'qty_in',
        'qty_out',
        'balance_after',
        'source_type',
        'source_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'reward_item_id' => 'integer',
        'qty_in' => 'integer',
        'qty_out' => 'integer',
        'balance_after' => 'integer',
        'source_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
