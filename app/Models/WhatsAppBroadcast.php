<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppBroadcast extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_broadcasts';

    protected $fillable = [
        'title',
        'message',
        'body_params',
        'template_id',
        'channel_integration_id',
        'status',
        'total_recipients',
        'success_recipients',
        'failed_recipients',
        'sent_at',
        'last_error',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'body_params' => 'array',
            'sent_at' => 'datetime',
            'total_recipients' => 'integer',
            'success_recipients' => 'integer',
            'failed_recipients' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppBroadcastRecipient::class, 'broadcast_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
