<?php

namespace App\Repositories\Shipping\Contracts;

interface ShippingTargetRepositoryInterface
{
    /**
     * @return list<array{id:int,label:string}>
     */
    public function provinceOptions(): array;

    /**
     * @return list<array{id:int,province_id:int,label:string}>
     */
    public function cityOptions(): array;

    /**
     * @return list<array{id:int,province_id:int,city_id:int,label:string,district_lion:string}>
     */
    public function districtOptions(): array;

    /** @return list<string> */
    public function provinces(): array;

    /** @return list<string> */
    public function citiesByProvince(string $province): array;

    /** @return list<array{label:string,value:string}> */
    public function districtsByProvinceAndCity(string $province, string $city): array;

    /**
     * Cari nilai district_lion untuk digunakan sebagai destination Lion Parcel API.
     * Jika district null, ambil district_lion pertama yang cocok di kota tersebut.
     */
    public function findDistrictLion(string $province, string $city, ?string $district = null): ?string;

    /**
     * @return array{province_id:int,province_label:string,city_id:int,city_label:string}|null
     */
    public function findCityByIds(int $provinceId, int $cityId): ?array;

    /**
     * @return array{district:string,district_lion:string}|null
     */
    public function findDistrictByRegionIds(int $provinceId, int $cityId, ?string $district = null): ?array;

    /** @return list<array{id:int,city_id:int,label:string,district_lion:string}> */
    public function districtOptionsByCityId(int $cityId): array;
}
