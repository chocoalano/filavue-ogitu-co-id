<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ShipmentItem (Item Pengiriman).
 *
 * Detail item yang termasuk dalam satu paket pengiriman.
 *
 * @property int $id
 * @property int $shipment_id Relasi ke pengiriman
 * @property int $order_item_id Relasi ke order item
 * @property int $qty Jumlah yang dikirim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ShipmentItem extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'shipment_id',
        'order_item_id',
        'qty',
    ];

    /**
     * Pengiriman induk.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Order item yang dikirim.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
