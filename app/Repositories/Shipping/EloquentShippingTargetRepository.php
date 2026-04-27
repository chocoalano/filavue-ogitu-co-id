<?php

namespace App\Repositories\Shipping;

use App\Models\ShippingTarget;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EloquentShippingTargetRepository implements ShippingTargetRepositoryInterface
{
    public function provinceOptions(): array
    {
        $this->ensureRegionIds();

        $localProvinces = ShippingTarget::query()
            ->selectRaw('MIN(province_id) as id, province as label')
            ->whereNotNull('province_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->groupBy('province')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $province): array => [
                'id' => (int) $province->id,
                'label' => (string) $province->label,
            ])
            ->toArray();

        $rajaOngkirProvinces = Cache::remember(
            'dashboard:address:rajaongkir:provinces',
            now()->addHours(6),
            fn (): array => app(RajaOngkirService::class)->getProvinces(),
        );

        if ($rajaOngkirProvinces === []) {
            return $localProvinces;
        }

        $localProvinceIdByName = collect($localProvinces)
            ->mapWithKeys(fn (array $province): array => [
                $this->normalizeProvinceLabel((string) ($province['label'] ?? '')) => (int) ($province['id'] ?? 0),
            ])
            ->filter(fn (int $id): bool => $id > 0)
            ->all();

        $ordered = [];

        foreach ($rajaOngkirProvinces as $province) {
            $label = $this->extractRegionValue($province, ['province_name', 'province', 'name']);

            if (! is_string($label) || trim($label) === '') {
                continue;
            }

            $normalizedLabel = $this->normalizeProvinceLabel($label);
            $localId = $localProvinceIdByName[$normalizedLabel] ?? null;

            if (! is_int($localId) || $localId < 1) {
                continue;
            }

            $ordered[] = [
                'id' => $localId,
                'label' => trim($label),
            ];
        }

        if ($ordered === []) {
            return $localProvinces;
        }

        $orderedKeys = collect($ordered)
            ->map(fn (array $province): string => $this->normalizeProvinceLabel((string) ($province['label'] ?? '')))
            ->all();

        $remaining = collect($localProvinces)
            ->reject(fn (array $province): bool => in_array(
                $this->normalizeProvinceLabel((string) ($province['label'] ?? '')),
                $orderedKeys,
                true
            ))
            ->values()
            ->all();

        return [...$ordered, ...$remaining];
    }

    public function cityOptions(): array
    {
        $this->ensureRegionIds();

        return ShippingTarget::query()
            ->selectRaw('MIN(city_id) as id, MIN(province_id) as province_id, city as label')
            ->whereNotNull('city_id')
            ->whereNotNull('province_id')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('province', 'city')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $city): array => [
                'id' => (int) $city->id,
                'province_id' => (int) $city->province_id,
                'label' => (string) $city->label,
            ])
            ->toArray();
    }

    public function districtOptions(): array
    {
        $this->ensureRegionIds();

        return ShippingTarget::query()
            ->selectRaw('MIN(id) as id, MIN(province_id) as province_id, MIN(city_id) as city_id, district as label, MIN(district_lion) as district_lion')
            ->whereNotNull('province_id')
            ->whereNotNull('city_id')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '')
            ->groupBy('province', 'city', 'district')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $district): array => [
                'id' => (int) $district->id,
                'province_id' => (int) $district->province_id,
                'city_id' => (int) $district->city_id,
                'label' => (string) $district->label,
                'district_lion' => (string) $district->district_lion,
            ])
            ->toArray();
    }

    /** @return list<array{id:int,city_id:int,label:string,district_lion:string}> */
    public function districtOptionsByCityId(int $cityId): array
    {
        $this->ensureRegionIds();

        return ShippingTarget::query()
            ->selectRaw('MIN(id) as id, MIN(city_id) as city_id, district as label, MIN(district_lion) as district_lion')
            ->where('city_id', $cityId)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '')
            ->groupBy('district')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $district): array => [
                'id' => (int) $district->id,
                'city_id' => (int) $district->city_id,
                'label' => (string) $district->label,
                'district_lion' => (string) $district->district_lion,
            ])
            ->toArray();
    }

    public function provinces(): array
    {
        return ShippingTarget::query()
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->toArray();
    }

    public function citiesByProvince(string $province): array
    {
        return ShippingTarget::query()
            ->where('province', $province)
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->toArray();
    }

    public function districtsByProvinceAndCity(string $province, string $city): array
    {
        return ShippingTarget::query()
            ->select('district', 'district_lion')
            ->where('province', $province)
            ->where('city', $city)
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->get()
            ->map(fn (ShippingTarget $row): array => [
                'label' => (string) $row->district_lion,
                'value' => (string) $row->district_lion,
            ])
            ->toArray();
    }

    public function findDistrictLion(string $province, string $city, ?string $district = null): ?string
    {
        $query = ShippingTarget::query()
            ->where('province', $province)
            ->where('city', $city)
            ->whereNotNull('district_lion');

        $normalizedDistrict = $district !== null ? trim($district) : null;

        if ($normalizedDistrict !== null && $normalizedDistrict !== '') {
            $query->where('district', $normalizedDistrict);
        }

        return $query->value('district_lion');
    }

    public function findCityByIds(int $provinceId, int $cityId): ?array
    {
        $this->ensureRegionIds();

        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $city = ShippingTarget::query()
            ->selectRaw('MIN(province_id) as province_id, MIN(city_id) as city_id, province as province_label, city as city_label')
            ->where('province_id', $provinceId)
            ->where('city_id', $cityId)
            ->whereNotNull('province')
            ->whereNotNull('city')
            ->groupBy('province', 'city')
            ->first();

        if (! $city) {
            return null;
        }

        return [
            'province_id' => (int) $city->province_id,
            'province_label' => (string) $city->province_label,
            'city_id' => (int) $city->city_id,
            'city_label' => (string) $city->city_label,
        ];
    }

    public function findDistrictByRegionIds(int $provinceId, int $cityId, ?string $district = null): ?array
    {
        $this->ensureRegionIds();

        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $normalizedDistrict = $district !== null ? trim($district) : null;

        $query = ShippingTarget::query()
            ->selectRaw('district, district_lion')
            ->where('province_id', $provinceId)
            ->where('city_id', $cityId)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '');

        if ($normalizedDistrict !== null && $normalizedDistrict !== '') {
            $query->where('district', $normalizedDistrict);
        }

        $districtRow = $query
            ->orderBy('district')
            ->first();

        if (! $districtRow) {
            return null;
        }

        return [
            'district' => (string) $districtRow->district,
            'district_lion' => (string) $districtRow->district_lion,
        ];
    }

    private function ensureRegionIds(): void
    {
        if (! DB::table('shipping_targets')->whereNull('province_id')->orWhereNull('city_id')->exists()) {
            return;
        }

        $this->backfillProvinceIds();
        $this->backfillCityIds();
    }

    /**
     * @return array<string, int>
     */
    private function backfillProvinceIds(): array
    {
        $existingProvinceRows = DB::table('shipping_targets')
            ->selectRaw('province, MIN(province_id) as province_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('province_id')
            ->groupBy('province')
            ->get();

        $provinceIdByName = [];

        foreach ($existingProvinceRows as $row) {
            $provinceIdByName[(string) $row->province] = (int) $row->province_id;
        }

        $nextProvinceId = (int) (DB::table('shipping_targets')->max('province_id') ?? 0) + 1;

        $provinceValues = DB::table('shipping_targets')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->groupBy('province')
            ->orderByRaw('MIN(id)')
            ->pluck('province');

        foreach ($provinceValues as $province) {
            $provinceName = (string) $province;

            if ($provinceName === '') {
                continue;
            }

            if (! array_key_exists($provinceName, $provinceIdByName)) {
                $provinceIdByName[$provinceName] = $nextProvinceId;
                $nextProvinceId++;
            }

            DB::table('shipping_targets')
                ->where('province', $provinceName)
                ->whereNull('province_id')
                ->update(['province_id' => $provinceIdByName[$provinceName]]);
        }

        return $provinceIdByName;
    }

    /**
     * @return array<string, int>
     */
    private function backfillCityIds(): array
    {
        $existingCityRows = DB::table('shipping_targets')
            ->selectRaw('province, city, MIN(city_id) as city_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereNotNull('city_id')
            ->groupBy('province', 'city')
            ->get();

        $cityIdByProvinceAndName = [];

        foreach ($existingCityRows as $row) {
            $cityKey = (string) $row->province.'|'.(string) $row->city;
            $cityIdByProvinceAndName[$cityKey] = (int) $row->city_id;
        }

        $nextCityId = (int) (DB::table('shipping_targets')->max('city_id') ?? 0) + 1;

        $cityRows = DB::table('shipping_targets')
            ->select('province', 'city')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('province', 'city')
            ->orderByRaw('MIN(id)')
            ->get();

        foreach ($cityRows as $cityRow) {
            $provinceName = (string) $cityRow->province;
            $cityName = (string) $cityRow->city;

            if ($provinceName === '' || $cityName === '') {
                continue;
            }

            $cityKey = $provinceName.'|'.$cityName;

            if (! array_key_exists($cityKey, $cityIdByProvinceAndName)) {
                $cityIdByProvinceAndName[$cityKey] = $nextCityId;
                $nextCityId++;
            }

            $cityId = $cityIdByProvinceAndName[$cityKey];

            DB::table('shipping_targets')
                ->where('province', $provinceName)
                ->where('city', $cityName)
                ->update([
                    'city_id' => DB::raw("COALESCE(city_id, {$cityId})"),
                ]);
        }

        return $cityIdByProvinceAndName;
    }

    private function normalizeProvinceLabel(string $label): string
    {
        $normalized = trim(mb_strtolower($label));

        if (str_starts_with($normalized, 'provinsi ')) {
            $normalized = trim(substr($normalized, strlen('provinsi ')));
        }

        return $normalized;
    }

    private function extractRegionValue(mixed $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (is_array($row) && array_key_exists($key, $row)) {
                return $row[$key];
            }

            if (is_object($row) && isset($row->{$key})) {
                return $row->{$key};
            }
        }

        return null;
    }
}
