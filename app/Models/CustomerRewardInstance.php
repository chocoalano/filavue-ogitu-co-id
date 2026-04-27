<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerRewardInstance extends Model
{
    protected $fillable = [
        'customer_id',
        'reward_item_id',
        'source_type',
        'source_id',
        'qty',
        'status',
        'requires_shipping',
        'shipping_address_id',
        'address_snapshot_json',
        'fulfillment_ref_no',
        'tracking_no',
        'shipped_at',
        'delivered_at',
        'used_at',
        'expires_at',
        'reward_snapshot_json',
        'metadata_json',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'reward_item_id' => 'integer',
        'source_id' => 'integer',
        'qty' => 'integer',
        'requires_shipping' => 'boolean',
        'shipping_address_id' => 'integer',
        'address_snapshot_json' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'reward_snapshot_json' => 'array',
        'metadata_json' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class, 'reward_item_id');
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function gachaDraw(): HasOne
    {
        return $this->hasOne(GachaDraw::class, 'reward_instance_id');
    }
}
