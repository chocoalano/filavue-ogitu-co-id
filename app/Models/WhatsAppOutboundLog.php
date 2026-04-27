<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppOutboundLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_outbound_logs';

    protected $fillable = [
        'broadcast_id',
        'qontak_id',
        'name',
        'organization_id',
        'channel_integration_id',
        'contact_list_id',
        'contact_id',
        'target_channel',
        'send_at',
        'execute_status',
        'execute_type',
        'parameters',
        'message_status_count',
        'contact_extra',
        'message_template',
        'division_id',
        'message_broadcast_plan_id',
        'message_broadcast_error',
        'sender_name',
        'sender_email',
        'channel_account_name',
        'channel_phone_number',
        'qontak_created_at',
    ];

    protected function casts(): array
    {
        return [
            'broadcast_id' => 'string',
            'parameters' => 'array',
            'message_status_count' => 'array',
            'contact_extra' => 'array',
            'message_template' => 'array',
            'send_at' => 'datetime',
            'qontak_created_at' => 'datetime',
        ];
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(WhatsAppBroadcast::class, 'broadcast_id');
    }

    /**
     * Perbarui record dari satu entri respons GET /v1/broadcasts/{id}/whatsapp/log.
     *
     * @param  array<string, mixed>  $entry  Satu item dari array 'data' pada respons log Qontak
     */
    public function updateFromBroadcastLogEntry(array $entry): void
    {
        $updates = [];

        if (isset($entry['status'])) {
            $updates['execute_status'] = $entry['status'];
        }

        if (isset($entry['contact_phone_number'])) {
            $updates['channel_phone_number'] = $entry['contact_phone_number'];
        }

        if (isset($entry['contact_full_name'])) {
            $updates['name'] = $entry['contact_full_name'];
        }

        if (array_key_exists('whatsapp_error_message', $entry)) {
            $updates['message_broadcast_error'] = $entry['whatsapp_error_message'];
        }

        if (isset($entry['channel_integration_id'])) {
            $updates['channel_integration_id'] = $entry['channel_integration_id'];
        }

        if (isset($entry['messages_broadcast_id'])) {
            $updates['message_broadcast_plan_id'] = $entry['messages_broadcast_id'];
        }

        if (isset($entry['created_at'])) {
            $updates['qontak_created_at'] = Carbon::parse($entry['created_at']);
        }

        if ($updates !== []) {
            $this->update($updates);
        }
    }

    /**
     * Buat atau perbarui log dari respons Qontak API.
     *
     * @param  array<string, mixed>  $data  Isi dari key 'data' pada respons Qontak
     * @param  int|null  $broadcastId  ID broadcast lokal, opsional
     */
    public static function upsertFromQontakResponse(array $data, int|string|null $broadcastId = null): static
    {
        return static::updateOrCreate(
            ['qontak_id' => $data['id']],
            [
                'broadcast_id' => $broadcastId ?? ($data['id'] ?? null),
                'name' => $data['name'] ?? null,
                'organization_id' => $data['organization_id'] ?? null,
                'channel_integration_id' => $data['channel_integration_id'] ?? null,
                'contact_list_id' => $data['contact_list_id'] ?? null,
                'contact_id' => $data['contact_id'] ?? null,
                'target_channel' => $data['target_channel'] ?? 'wa_cloud',
                'send_at' => isset($data['send_at']) ? Carbon::parse($data['send_at']) : null,
                'execute_status' => $data['execute_status'] ?? 'todo',
                'execute_type' => $data['execute_type'] ?? 'immediately',
                'parameters' => $data['parameters'] ?? null,
                'message_status_count' => $data['message_status_count'] ?? null,
                'contact_extra' => $data['contact_extra'] ?? null,
                'message_template' => $data['message_template'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'message_broadcast_plan_id' => $data['message_broadcast_plan_id'] ?? null,
                'message_broadcast_error' => $data['message_broadcast_error'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
                'sender_email' => $data['sender_email'] ?? null,
                'channel_account_name' => $data['channel_account_name'] ?? null,
                'channel_phone_number' => $data['channel_phone_number'] ?? null,
                'qontak_created_at' => isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            ],
        );
    }
}
