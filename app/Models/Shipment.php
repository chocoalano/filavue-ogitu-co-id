<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Shipment (Pengiriman).
 *
 * Pengiriman fisik barang dari order ke alamat customer.
 *
 * @property int $id
 * @property int $order_id Relasi ke order
 * @property string|null $courier_id ID/kode kurir
 * @property string|null $tracking_no Nomor resi
 * @property string $status Status pengiriman (pending|shipped|delivered|READY_TO_SHIP)
 * @property \Illuminate\Support\Carbon|null $shipped_at Waktu pengiriman
 * @property \Illuminate\Support\Carbon|null $delivered_at Waktu diterima
 * @property float $shipping_fee Biaya pengiriman
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Shipment extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'courier_id',
        'tracking_no',
        'status',
        'shipped_at',
        'delivered_at',
        'shipping_fee',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'shipping_fee' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function courierOptions(): array
    {
        $couriers = config('shipping.rajaongkir.couriers', []);

        if (! is_array($couriers)) {
            return [];
        }

        return collect($couriers)
            ->mapWithKeys(function (mixed $label, mixed $code): array {
                if (! is_string($code) || blank($code)) {
                    return [];
                }

                $normalizedCode = strtolower(trim($code));
                $normalizedLabel = is_string($label) && filled($label)
                    ? trim($label)
                    : strtoupper($normalizedCode);

                return [$normalizedCode => $normalizedLabel];
            })
            ->all();
    }

    /**
     * Order yang dikirimkan.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Item-item yang termasuk dalam pengiriman ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
