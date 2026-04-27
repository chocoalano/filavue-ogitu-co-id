<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Refund (Pengembalian Dana).
 *
 * Pencatatan pengembalian dana kepada customer.
 *
 * @property int $id
 * @property int $order_id Relasi ke order
 * @property int $payment_id Relasi ke pembayaran
 * @property string $status Status refund (pending|approved|rejected)
 * @property float $amount Nominal refund
 * @property string|null $reason Alasan refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Refund extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'payment_id',
        'status',
        'amount',
        'reason',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Order terkait refund.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Pembayaran yang di-refund.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
