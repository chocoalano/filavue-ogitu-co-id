<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GachaCampaign extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ENDED = 'ended';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'gacha_model',
        'point_cost_per_draw',
        'max_draw_per_customer_per_day',
        'max_draw_per_customer_total',
        'requires_manual_pick',
        'guaranteed_prize',
        'status',
        'start_at',
        'end_at',
        'banner_image',
        'terms_json',
        'metadata_json',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'point_cost_per_draw' => 'integer',
        'max_draw_per_customer_per_day' => 'integer',
        'max_draw_per_customer_total' => 'integer',
        'requires_manual_pick' => 'boolean',
        'guaranteed_prize' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'terms_json' => 'array',
        'metadata_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function rewardRules(): HasMany
    {
        return $this->hasMany(GachaCampaignRewardRule::class, 'campaign_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(GachaBoard::class, 'campaign_id');
    }

    public function draws(): HasMany
    {
        return $this->hasMany(GachaDraw::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
