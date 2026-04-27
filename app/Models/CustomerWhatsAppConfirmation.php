<?php

namespace App\Models;

use Database\Factories\CustomerWhatsAppConfirmationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property string $phone Nomor WA yang telah mengirim pesan ke sistem
 * @property Carbon $confirmed_at Pertama kali customer mengirim pesan ke sistem
 * @property Carbon $last_received_at Terakhir kali pesan diterima dari nomor ini
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CustomerWhatsAppConfirmation extends Model
{
    /** @use HasFactory<CustomerWhatsAppConfirmationFactory> */
    use HasFactory;

    protected $table = 'customer_whatsapp_confirmations';

    protected $fillable = [
        'customer_id',
        'phone',
        'confirmed_at',
        'last_received_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'last_received_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Cek apakah nomor WA sudah terkonfirmasi dan boleh dikirim pesan.
     */
    public static function isConfirmed(string $phone): bool
    {
        return static::query()->where('phone', $phone)->exists();
    }

    /**
     * Hubungkan nomor WA yang sudah terkonfirmasi dengan customer_id-nya.
     * Dipanggil saat customer mengklik link konfirmasi setelah pesan WA sudah diterima via webhook.
     */
    public static function linkCustomer(string $phone, int $customerId): void
    {
        static::query()
            ->where('phone', $phone)
            ->whereNull('customer_id')
            ->update(['customer_id' => $customerId]);
    }

    /**
     * Catat atau perbarui konfirmasi saat pesan WA diterima dari nomor ini.
     */
    public static function recordIncoming(string $phone, ?int $customerId = null): static
    {
        $now = now();

        /** @var static $record */
        $record = static::query()->firstOrNew(['phone' => $phone]);

        if (! $record->exists) {
            $record->confirmed_at = $now;
            $record->customer_id = $customerId;
        }

        $record->last_received_at = $now;
        $record->save();

        return $record;
    }
}
