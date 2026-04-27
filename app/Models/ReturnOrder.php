<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model ReturnOrder (Retur Barang).
 *
 * Permintaan pengembalian barang dari customer.
 * Menggunakan nama ReturnOrder karena "Return" adalah reserved word PHP.
 *
 * @property int $id
 * @property int $order_id Relasi ke order
 * @property string $status Status retur (pending|approved|rejected)
 * @property string|null $reason Alasan retur
 * @property \Illuminate\Support\Carbon|null $requested_at Waktu permintaan
 * @property \Illuminate\Support\Carbon|null $processed_at Waktu diproses
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ReturnOrder extends BaseModel
{
    use HasFactory;

    protected $table = 'returns';

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'status',
        'reason',
        'requested_at',
        'processed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Order terkait retur.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Item-item yang diretur.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
