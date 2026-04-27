<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Payment (Pembayaran).
 *
 * Pembayaran untuk sebuah order. Mendukung integrasi Midtrans.
 *
 * @property int $id
 * @property int $order_id Relasi ke order
 * @property int $method_id Metode pembayaran
 * @property string $status Status pembayaran
 * @property float $amount Nominal pembayaran
 * @property string $currency Mata uang
 * @property string|null $provider_txn_id ID transaksi dari provider
 * @property array|null $metadata_json Metadata dari payment gateway (JSON)
 * @property string|null $transaction_id ID transaksi Midtrans
 * @property string|null $signature_key Signature key untuk verifikasi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Payment extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'method_id',
        'status',
        'amount',
        'currency',
        'provider_txn_id',
        'metadata_json',
        'transaction_id',
        'signature_key',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata_json' => 'array',
        ];
    }

    /**
     * Order terkait pembayaran ini.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Metode pembayaran yang digunakan.
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    /**
     * Riwayat transaksi pembayaran.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Refund terkait pembayaran ini.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
