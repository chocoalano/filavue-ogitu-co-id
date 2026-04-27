<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ReturnItem (Item Retur).
 *
 * Detail item yang diretur dalam satu permintaan retur.
 *
 * @property int $id
 * @property int $return_id Relasi ke return
 * @property int $order_item_id Relasi ke order item
 * @property int $qty Jumlah yang diretur
 * @property string|null $condition_note Catatan kondisi barang
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ReturnItem extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'return_id',
        'order_item_id',
        'qty',
        'condition_note',
    ];

    /**
     * Retur induk.
     */
    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class, 'return_id');
    }

    /**
     * Order item yang diretur.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
