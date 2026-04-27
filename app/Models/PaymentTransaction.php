<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PaymentTransaction (Log Transaksi Pembayaran).
 *
 * Catatan setiap perubahan status / callback dari payment gateway.
 *
 * @property int $id
 * @property int $payment_id Relasi ke pembayaran
 * @property string $status Status transaksi
 * @property float $amount Nominal
 * @property array|null $raw_json Raw response dari gateway (JSON)
 * @property \Illuminate\Support\Carbon $created_at
 */
class PaymentTransaction extends BaseModel
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'payment_id',
        'status',
        'amount',
        'raw_json',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Pembayaran induk.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
