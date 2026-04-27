<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GachaBoard extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_EXHAUSTED = 'exhausted';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'campaign_id',
        'board_code',
        'title',
        'rows',
        'cols',
        'total_slots',
        'available_slots',
        'popped_slots',
        'status',
        'generated_at',
        'generated_by',
        'activated_at',
        'closed_at',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'rows' => 'integer',
        'cols' => 'integer',
        'total_slots' => 'integer',
        'available_slots' => 'integer',
        'popped_slots' => 'integer',
        'generated_at' => 'datetime',
        'generated_by' => 'integer',
        'activated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GachaCampaign::class, 'campaign_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(GachaBoardSlot::class, 'board_id');
    }

    public function draws(): HasMany
    {
        return $this->hasMany(GachaDraw::class, 'board_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
