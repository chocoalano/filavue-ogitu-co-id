<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardItem extends Model
{
    public const TYPE_PHYSICAL_MERCHANDISE = 'physical_merchandise';
    public const TYPE_VOUCHER = 'voucher';
    public const TYPE_BONUS_POINT = 'bonus_point';
    public const TYPE_DIGITAL_ITEM = 'digital_item';
    public const TYPE_COUPON = 'coupon';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'reward_type',
        'product_id',
        'point_cost',
        'point_reward_amount',
        'requires_shipping',
        'fulfillment_mode',
        'stock_mode',
        'stock_qty',
        'stock_reserved',
        'stock_issued',
        'is_gacha_enabled',
        'is_point_redeemable',
        'is_active',
        'sort_order',
        'thumbnail',
        'metadata_json',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'point_cost' => 'integer',
        'point_reward_amount' => 'integer',
        'requires_shipping' => 'boolean',
        'stock_qty' => 'integer',
        'stock_reserved' => 'integer',
        'stock_issued' => 'integer',
        'is_gacha_enabled' => 'boolean',
        'is_point_redeemable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata_json' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(RewardInventoryLedger::class, 'reward_item_id');
    }

    public function campaignRewardRules(): HasMany
    {
        return $this->hasMany(GachaCampaignRewardRule::class, 'reward_item_id');
    }

    public function boardSlots(): HasMany
    {
        return $this->hasMany(GachaBoardSlot::class, 'reward_item_id');
    }

    public function rewardInstances(): HasMany
    {
        return $this->hasMany(CustomerRewardInstance::class, 'reward_item_id');
    }

    public function gachaDraws(): HasMany
    {
        return $this->hasMany(GachaDraw::class, 'reward_item_id');
    }

    public function redemptionItems(): HasMany
    {
        return $this->hasMany(PointRedemptionItem::class, 'reward_item_id');
    }
}
