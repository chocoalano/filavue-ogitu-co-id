<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPointAccount extends Model
{
    protected $fillable = [
        'customer_id',
        'current_balance',
        'locked_balance',
        'lifetime_earned',
        'lifetime_spent',
        'version',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'current_balance' => 'integer',
        'locked_balance' => 'integer',
        'lifetime_earned' => 'integer',
        'lifetime_spent' => 'integer',
        'version' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(CustomerPointLedger::class, 'point_account_id');
    }

    public function gachaDraws(): HasMany
    {
        return $this->hasMany(GachaDraw::class, 'point_account_id');
    }

    public function pointRedemptions(): HasMany
    {
        return $this->hasMany(PointRedemption::class, 'point_account_id');
    }
}
