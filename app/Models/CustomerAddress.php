<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model CustomerAddress (Alamat Customer).
 *
 * Alamat pengiriman/penagihan customer dengan data
 * provinsi dan kota dari API pihak ketiga (RajaOngkir, dll).
 *
 * @property int $id
 * @property int $customer_id Pemilik alamat
 * @property string|null $label Label (Rumah, Kantor, Pick Up, dll)
 * @property bool $is_default Alamat utama/default
 * @property string $recipient_name Nama penerima
 * @property string $recipient_phone Telepon penerima
 * @property string $address_line1 Alamat utama
 * @property string|null $address_line2 Alamat tambahan
 * @property string $province_label Nama provinsi
 * @property int $province_id ID provinsi (API)
 * @property string $city_label Nama kota/kabupaten
 * @property int $city_id ID kota (API)
 * @property string|null $district Nama kecamatan
 * @property string|null $district_lion Kode/label district Lion Parcel
 * @property string|null $postal_code Kode pos
 * @property string $country Negara (default: Indonesia)
 * @property string|null $description Catatan tambahan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CustomerAddress extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'label',
        'is_default',
        'recipient_name',
        'recipient_phone',
        'address_line1',
        'address_line2',
        'province_label',
        'province_id',
        'city_label',
        'city_id',
        'district',
        'district_lion',
        'postal_code',
        'country',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Customer pemilik alamat ini.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rewardInstances()
    {
        return $this->hasMany(CustomerRewardInstance::class, 'shipping_address_id');
    }

    public function pointRedemptions()
    {
        return $this->hasMany(PointRedemption::class, 'shipping_address_id');
    }
}
