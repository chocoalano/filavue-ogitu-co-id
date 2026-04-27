<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaDraw extends Model
{
    protected $fillable = [
        'draw_no',
        'campaign_id',
        'board_id',
        'slot_id',
        'customer_id',
        'point_account_id',
        'point_ledger_id',
        'reward_item_id',
        'reward_instance_id',
        'points_spent',
        'channel',
        'handled_by_user_id',
        'status',
        'idempotency_key',
        'result_snapshot_json',
        'drawn_at',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'board_id' => 'integer',
        'slot_id' => 'integer',
        'customer_id' => 'integer',
        'point_account_id' => 'integer',
        'point_ledger_id' => 'integer',
        'reward_item_id' => 'integer',
        'reward_instance_id' => 'integer',
        'points_spent' => 'integer',
        'handled_by_user_id' => 'integer',
        'result_snapshot_json' => 'array',
        'drawn_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GachaCampaign::class, 'campaign_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(GachaBoard::class, 'board_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(GachaBoardSlot::class, 'slot_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function pointAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerPointAccount::class, 'point_account_id');
    }

    public function pointLedger(): BelongsTo
    {
        return $this->belongsTo(CustomerPointLedger::class, 'point_ledger_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }

    public function rewardInstance(): BelongsTo
    {
        return $this->belongsTo(CustomerRewardInstance::class, 'reward_instance_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
