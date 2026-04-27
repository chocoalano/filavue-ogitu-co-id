<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaCampaignRewardRule extends Model
{
    protected $fillable = [
        'campaign_id',
        'reward_item_id',
        'quota_total',
        'quota_per_board',
        'weight',
        'is_jackpot',
        'is_active',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'reward_item_id' => 'integer',
        'quota_total' => 'integer',
        'quota_per_board' => 'integer',
        'weight' => 'decimal:4',
        'is_jackpot' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GachaCampaign::class, 'campaign_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }
}
