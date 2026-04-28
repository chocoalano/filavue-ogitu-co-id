<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JneDestination extends Model
{
    use HasFactory;

    protected $table = 'jne_destinations';

    protected $fillable = [
        'country_name',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
        'tariff_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Filter by JNE destination tariff code.
     */
    public function scopeByTariffCode(Builder $query, string $tariffCode): Builder
    {
        return $query->where('tariff_code', $tariffCode);
    }

    /**
     * Filter by zip code.
     */
    public function scopeByZipCode(Builder $query, string $zipCode): Builder
    {
        return $query->where('zip_code', $zipCode);
    }

    /**
     * Filter by province name.
     */
    public function scopeProvince(Builder $query, ?string $provinceName): Builder
    {
        if (blank($provinceName)) {
            return $query;
        }

        return $query->where('province_name', $provinceName);
    }

    /**
     * Filter by city name.
     */
    public function scopeCity(Builder $query, ?string $cityName): Builder
    {
        if (blank($cityName)) {
            return $query;
        }

        return $query->where('city_name', $cityName);
    }

    /**
     * Filter by district name.
     */
    public function scopeDistrict(Builder $query, ?string $districtName): Builder
    {
        if (blank($districtName)) {
            return $query;
        }

        return $query->where('district_name', $districtName);
    }

    /**
     * Filter by subdistrict name.
     */
    public function scopeSubdistrict(Builder $query, ?string $subdistrictName): Builder
    {
        if (blank($subdistrictName)) {
            return $query;
        }

        return $query->where('subdistrict_name', $subdistrictName);
    }

    /**
     * Search destination by tariff code, province, city, district, subdistrict, or zip code.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('tariff_code', 'like', "%{$keyword}%")
                ->orWhere('province_name', 'like', "%{$keyword}%")
                ->orWhere('city_name', 'like', "%{$keyword}%")
                ->orWhere('district_name', 'like', "%{$keyword}%")
                ->orWhere('subdistrict_name', 'like', "%{$keyword}%")
                ->orWhere('zip_code', 'like', "%{$keyword}%");
        });
    }
}
