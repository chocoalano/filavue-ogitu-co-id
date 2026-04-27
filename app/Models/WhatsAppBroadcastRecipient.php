<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppBroadcastRecipient extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_broadcast_recipients';

    protected $fillable = [
        'broadcast_id',
        'customer_id',
        'customer_name',
        'phone',
        'normalized_phone',
        'status',
        'response_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'broadcast_id' => 'integer',
            'customer_id' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(WhatsAppBroadcast::class, 'broadcast_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
