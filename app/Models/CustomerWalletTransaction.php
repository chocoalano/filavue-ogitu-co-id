<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerWalletTransaction (Transaksi E-Wallet).
 *
 * Mencatat semua mutasi saldo e-wallet customer:
 * topup, withdrawal, bonus, purchase, refund, tax.
 *
 * @property int $id
 * @property int $customer_id Pemilik wallet
 * @property string $type Jenis transaksi (topup|withdrawal|bonus|purchase|refund|tax)
 * @property float $amount Nominal
 * @property float $balance_before Saldo sebelum
 * @property float $balance_after Saldo sesudah
 * @property string $status Status (pending|completed|failed|cancelled)
 * @property string|null $payment_method Metode pembayaran
 * @property string|null $transaction_ref Referensi transaksi
 * @property string|null $midtrans_transaction_id ID transaksi Midtrans
 * @property string|null $notes Catatan/bank info (JSON)
 * @property \Illuminate\Support\Carbon|null $completed_at Waktu selesai
 * @property bool $is_system Transaksi sistem otomatis
 * @property string|null $midtrans_signature_key Signature key Midtrans
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerWalletTransaction extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'payment_method',
        'transaction_ref',
        'midtrans_transaction_id',
        'notes',
        'completed_at',
        'is_system',
        'midtrans_signature_key',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'completed_at' => 'datetime',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Customer pemilik transaksi.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
