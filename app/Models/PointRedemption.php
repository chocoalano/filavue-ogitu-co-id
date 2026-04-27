<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointRedemption extends Model
{
    protected $fillable = [
        'redemption_no',
        'customer_id',
        'point_account_id',
        'point_ledger_id',
        'total_points_spent',
        'total_items',
        'status',
        'shipping_address_id',
        'address_snapshot_json',
        'note',
        'requested_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'point_account_id' => 'integer',
        'point_ledger_id' => 'integer',
        'total_points_spent' => 'integer',
        'total_items' => 'integer',
        'shipping_address_id' => 'integer',
        'address_snapshot_json' => 'array',
        'requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PointRedemptionItem::class, 'point_redemption_id');
    }
}
