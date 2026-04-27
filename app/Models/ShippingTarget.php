<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model ShippingTarget (Target Pengiriman).
 *
 * Data wilayah pengiriman dengan hirarki: Provinsi → Kota → Kecamatan.
 *
 * @property int $id
 * @property string $three_lc_code Kode unik tiga huruf wilayah
 * @property string $country Negara
 * @property int|null $province_id ID provinsi internal (master shipping target)
 * @property string $province Provinsi
 * @property int|null $city_id ID kota/kabupaten internal (master shipping target)
 * @property string $city Kota/Kabupaten
 * @property string $district Kecamatan
 */
class ShippingTarget extends BaseModel
{
    use HasFactory;

    protected $table = 'shipping_targets';

    /** @var list<string> */
    protected $fillable = [
        'three_lc_code',
        'country',
        'province_id',
        'province',
        'city_id',
        'city',
        'district',
        'district_lion',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'city_id'     => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ShippingTarget $shippingTarget): void {
            $provinceName = trim((string) $shippingTarget->province);
            $cityName     = trim((string) $shippingTarget->city);

            $shippingTarget->province = $provinceName;
            $shippingTarget->city     = $cityName;

            if ($provinceName !== '' && ! $shippingTarget->province_id) {
                $shippingTarget->province_id = static::query()
                    ->where('province', $provinceName)
                    ->when(
                        $shippingTarget->exists,
                        fn ($query) => $query->where('id', '!=', $shippingTarget->id)
                    )
                    ->value('province_id');

                if (! $shippingTarget->province_id) {
                    $shippingTarget->province_id = (int) (static::query()->max('province_id') ?? 0) + 1;
                }
            }

            if ($provinceName !== '' && $cityName !== '' && ! $shippingTarget->city_id) {
                $shippingTarget->city_id = static::query()
                    ->where('province', $provinceName)
                    ->where('city', $cityName)
                    ->when(
                        $shippingTarget->exists,
                        fn ($query) => $query->where('id', '!=', $shippingTarget->id)
                    )
                    ->value('city_id');

                if (! $shippingTarget->city_id) {
                    $shippingTarget->city_id = (int) (static::query()->max('city_id') ?? 0) + 1;
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'three_lc_code';
    }
}
