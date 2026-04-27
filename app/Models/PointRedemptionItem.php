<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointRedemptionItem extends Model
{
    protected $fillable = [
        'point_redemption_id',
        'reward_item_id',
        'reward_instance_id',
        'qty',
        'points_cost_each',
        'subtotal_points',
        'reward_snapshot_json',
    ];

    protected $casts = [
        'point_redemption_id' => 'integer',
        'reward_item_id' => 'integer',
        'reward_instance_id' => 'integer',
        'qty' => 'integer',
        'points_cost_each' => 'integer',
        'subtotal_points' => 'integer',
        'reward_snapshot_json' => 'array',
    ];

    public function pointRedemption(): BelongsTo
    {
        return $this->belongsTo(PointRedemption::class, 'point_redemption_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }

    public function rewardInstance(): BelongsTo
    {
        return $this->belongsTo(CustomerRewardInstance::class, 'reward_instance_id');
    }
}
