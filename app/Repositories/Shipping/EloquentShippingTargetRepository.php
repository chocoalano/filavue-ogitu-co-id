<?php

namespace App\Repositories\Shipping;

use App\Models\JneDestination;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EloquentShippingTargetRepository implements ShippingTargetRepositoryInterface
{
    public function provinceOptions(): array
    {
        return $this->jneProvinceOptions();
    }

    public function cityOptions(): array
    {
        return $this->jneCityOptions();
    }

    public function districtOptions(): array
    {
        return $this->jneDistrictOptions();
    }

    /**
     * @return list<array{id:int,city_id:int,label:string,district_lion:string}>
     */
    public function districtOptionsByCityId(int $cityId): array
    {
        if ($cityId < 1) {
            return [];
        }

        $city = $this->destinationById($cityId);

        if (! $city || blank($city->province_name) || blank($city->city_name)) {
            return [];
        }

        $provinceName = (string) $city->province_name;
        $cityName = (string) $city->city_name;

        $cacheKey = 'shipping_repository:jne:v1:districts_by_city:' . md5($provinceName . '|' . $cityName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($cityId, $provinceName, $cityName): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, district_name')
                ->where('province_name', $provinceName)
                ->where('city_name', $cityName)
                ->whereNotNull('district_name')
                ->where('district_name', '!=', '')
                ->groupBy('district_name')
                ->orderBy('district_name')
                ->get()
                ->map(function ($row) use ($cityId): ?array {
                    $id = $this->normalizeIntegerId($row->id);
                    $label = $this->toUppercaseLabel($row->district_name);

                    if ($id === null || $label === null) {
                        return null;
                    }

                    return [
                        'id' => $id,
                        'city_id' => $cityId,
                        'label' => $label,
                        'district_lion' => $label,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    public function provinces(): array
    {
        return Cache::remember('shipping_repository:jne:v1:province_names', now()->addHours(12), function (): array {
            return JneDestination::query()
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->distinct()
                ->orderBy('province_name')
                ->pluck('province_name')
                ->map(fn (mixed $province): ?string => $this->toUppercaseLabel($province))
                ->filter()
                ->values()
                ->all();
        });
    }

    public function citiesByProvince(string $province): array
    {
        $provinceName = $this->findProvinceName($province);

        if ($provinceName === null) {
            return [];
        }

        $cacheKey = 'shipping_repository:jne:v1:cities_by_province:' . md5($provinceName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($provinceName): array {
            return JneDestination::query()
                ->where('province_name', $provinceName)
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->distinct()
                ->orderBy('city_name')
                ->pluck('city_name')
                ->map(fn (mixed $city): ?string => $this->toUppercaseLabel($city))
                ->filter()
                ->values()
                ->all();
        });
    }

    public function districtsByProvinceAndCity(string $province, string $city): array
    {
        $region = $this->findRegionByNames($province, $city);

        if ($region === null) {
            return [];
        }

        $provinceName = $region['province_name'];
        $cityName = $region['city_name'];

        $cacheKey = 'shipping_repository:jne:v1:districts_by_province_city:' . md5($provinceName . '|' . $cityName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($provinceName, $cityName): array {
            return JneDestination::query()
                ->where('province_name', $provinceName)
                ->where('city_name', $cityName)
                ->whereNotNull('district_name')
                ->where('district_name', '!=', '')
                ->distinct()
                ->orderBy('district_name')
                ->pluck('district_name')
                ->map(function (mixed $districtName): ?array {
                    $label = $this->toUppercaseLabel($districtName);

                    if ($label === null) {
                        return null;
                    }

                    return [
                        'label' => $label,
                        'value' => $label,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    public function findDistrictLion(string $province, string $city, ?string $district = null): ?string
    {
        $region = $this->findRegionByNames($province, $city);

        if ($region === null) {
            return null;
        }

        $query = JneDestination::query()
            ->where('province_name', $region['province_name'])
            ->where('city_name', $region['city_name'])
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '');

        $normalizedDistrict = $district !== null ? $this->toUppercaseLabel($district) : null;

        if ($normalizedDistrict !== null) {
            $query->whereRaw('UPPER(TRIM(district_name)) = ?', [$normalizedDistrict]);
        }

        $districtName = $query
            ->orderBy('district_name')
            ->value('district_name');

        return $this->toUppercaseLabel($districtName);
    }

    public function findCityByIds(int $provinceId, int $cityId): ?array
    {
        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $province = $this->destinationById($provinceId);
        $city = $this->destinationById($cityId);

        if (
            ! $province ||
            ! $city ||
            blank($province->province_name) ||
            blank($city->province_name) ||
            blank($city->city_name)
        ) {
            return null;
        }

        if ($this->normalizeRegionName($province->province_name) !== $this->normalizeRegionName($city->province_name)) {
            return null;
        }

        return [
            'province_id' => $provinceId,
            'province_label' => $this->toUppercaseLabel($province->province_name),
            'city_id' => $cityId,
            'city_label' => $this->toUppercaseLabel($city->city_name),
        ];
    }

    public function findDistrictByRegionIds(int $provinceId, int $cityId, ?string $district = null): ?array
    {
        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $city = $this->destinationById($cityId);
        $province = $this->destinationById($provinceId);

        if (
            ! $province ||
            ! $city ||
            blank($province->province_name) ||
            blank($city->province_name) ||
            blank($city->city_name)
        ) {
            return null;
        }

        if ($this->normalizeRegionName($province->province_name) !== $this->normalizeRegionName($city->province_name)) {
            return null;
        }

        $query = JneDestination::query()
            ->where('province_name', $city->province_name)
            ->where('city_name', $city->city_name)
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '');

        $normalizedDistrict = $district !== null ? $this->toUppercaseLabel($district) : null;

        if ($normalizedDistrict !== null) {
            $query->whereRaw('UPPER(TRIM(district_name)) = ?', [$normalizedDistrict]);
        }

        $districtRow = $query
            ->select('district_name')
            ->orderBy('district_name')
            ->first();

        if (! $districtRow || blank($districtRow->district_name)) {
            return null;
        }

        $label = $this->toUppercaseLabel($districtRow->district_name);

        if ($label === null) {
            return null;
        }

        return [
            'district' => $label,
            'district_lion' => $label,
        ];
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    private function jneProvinceOptions(): array
    {
        return Cache::remember('shipping_repository:jne:v1:province_options', now()->addHours(12), function (): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->groupBy('province_name')
                ->orderBy('province_name')
                ->get()
                ->map(function ($row): ?array {
                    $id = $this->normalizeIntegerId($row->id);
                    $label = $this->toUppercaseLabel($row->province_name);

                    if ($id === null || $label === null) {
                        return null;
                    }

                    return [
                        'id' => $id,
                        'label' => $label,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<array{id:int,province_id:int,label:string}>
     */
    private function jneCityOptions(): array
    {
        return Cache::remember('shipping_repository:jne:v1:city_options', now()->addHours(12), function (): array {
            $provinceIdByName = $this->provinceIdByName();

            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name, city_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->groupBy('province_name', 'city_name')
                ->orderBy('city_name')
                ->get()
                ->map(function ($row) use ($provinceIdByName): ?array {
                    $id = $this->normalizeIntegerId($row->id);
                    $label = $this->toUppercaseLabel($row->city_name);
                    $provinceKey = $this->normalizeRegionName($row->province_name);
                    $provinceId = $provinceIdByName[$provinceKey] ?? null;

                    if ($id === null || $provinceId === null || $label === null) {
                        return null;
                    }

                    return [
                        'id' => $id,
                        'province_id' => $provinceId,
                        'label' => $label,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<array{id:int,province_id:int,city_id:int,label:string,district_lion:string}>
     */
    private function jneDistrictOptions(): array
    {
        return Cache::remember('shipping_repository:jne:v1:district_options', now()->addHours(12), function (): array {
            $provinceIdByName = $this->provinceIdByName();
            $cityIdByProvinceAndName = $this->cityIdByProvinceAndName();

            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name, city_name, district_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->whereNotNull('district_name')
                ->where('district_name', '!=', '')
                ->groupBy('province_name', 'city_name', 'district_name')
                ->orderBy('district_name')
                ->get()
                ->map(function ($row) use ($provinceIdByName, $cityIdByProvinceAndName): ?array {
                    $id = $this->normalizeIntegerId($row->id);
                    $label = $this->toUppercaseLabel($row->district_name);

                    $provinceKey = $this->normalizeRegionName($row->province_name);
                    $cityKey = $provinceKey . '|' . $this->normalizeRegionName($row->city_name);

                    $provinceId = $provinceIdByName[$provinceKey] ?? null;
                    $cityId = $cityIdByProvinceAndName[$cityKey] ?? null;

                    if ($id === null || $provinceId === null || $cityId === null || $label === null) {
                        return null;
                    }

                    return [
                        'id' => $id,
                        'province_id' => $provinceId,
                        'city_id' => $cityId,
                        'label' => $label,
                        'district_lion' => $label,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return array<string, int>
     */
    private function provinceIdByName(): array
    {
        return Cache::remember('shipping_repository:jne:v1:province_id_by_name', now()->addHours(12), function (): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->groupBy('province_name')
                ->get()
                ->mapWithKeys(function ($row): array {
                    $id = $this->normalizeIntegerId($row->id);
                    $key = $this->normalizeRegionName($row->province_name);

                    if ($id === null || $key === '') {
                        return [];
                    }

                    return [$key => $id];
                })
                ->all();
        });
    }

    /**
     * @return array<string, int>
     */
    private function cityIdByProvinceAndName(): array
    {
        return Cache::remember('shipping_repository:jne:v1:city_id_by_province_and_name', now()->addHours(12), function (): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name, city_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->groupBy('province_name', 'city_name')
                ->get()
                ->mapWithKeys(function ($row): array {
                    $id = $this->normalizeIntegerId($row->id);
                    $provinceKey = $this->normalizeRegionName($row->province_name);
                    $cityKey = $this->normalizeRegionName($row->city_name);

                    if ($id === null || $provinceKey === '' || $cityKey === '') {
                        return [];
                    }

                    return [$provinceKey . '|' . $cityKey => $id];
                })
                ->all();
        });
    }

    private function destinationById(int $id): ?JneDestination
    {
        return Cache::remember("shipping_repository:jne:v1:destination:{$id}", now()->addHours(12), function () use ($id): ?JneDestination {
            return JneDestination::query()->find($id);
        });
    }

    private function findProvinceName(string $province): ?string
    {
        $normalizedProvince = $this->normalizeRegionName($province);

        if ($normalizedProvince === '') {
            return null;
        }

        $cacheKey = 'shipping_repository:jne:v1:find_province:' . md5($normalizedProvince);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($normalizedProvince): ?string {
            $provinceName = JneDestination::query()
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->whereRaw('UPPER(TRIM(province_name)) = ?', [$normalizedProvince])
                ->value('province_name');

            return filled($provinceName) ? (string) $provinceName : null;
        });
    }

    /**
     * @return array{province_name:string,city_name:string}|null
     */
    private function findRegionByNames(string $province, string $city): ?array
    {
        $normalizedProvince = $this->normalizeRegionName($province);
        $normalizedCity = $this->normalizeRegionName($city);

        if ($normalizedProvince === '' || $normalizedCity === '') {
            return null;
        }

        $cacheKey = 'shipping_repository:jne:v1:find_region:' . md5($normalizedProvince . '|' . $normalizedCity);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($normalizedProvince, $normalizedCity): ?array {
            $row = JneDestination::query()
                ->select('province_name', 'city_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->whereRaw('UPPER(TRIM(province_name)) = ?', [$normalizedProvince])
                ->whereRaw('UPPER(TRIM(city_name)) = ?', [$normalizedCity])
                ->first();

            if (! $row || blank($row->province_name) || blank($row->city_name)) {
                return null;
            }

            return [
                'province_name' => (string) $row->province_name,
                'city_name' => (string) $row->city_name,
            ];
        });
    }

    private function normalizeIntegerId(mixed $value): ?int
    {
        if (blank($value) || ! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeRegionName(mixed $value): string
    {
        if (blank($value) || ! is_scalar($value)) {
            return '';
        }

        $normalized = Str::upper(trim((string) $value));
        $normalized = preg_replace('/\s+/u', ' ', $normalized);

        return is_string($normalized) ? trim($normalized) : '';
    }

    private function toUppercaseLabel(mixed $label): ?string
    {
        if (blank($label) || ! is_scalar($label)) {
            return null;
        }

        return $this->normalizeRegionName($label);
    }
}
