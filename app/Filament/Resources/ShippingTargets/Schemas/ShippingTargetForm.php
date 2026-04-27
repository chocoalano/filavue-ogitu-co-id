<?php

namespace App\Filament\Resources\ShippingTargets\Schemas;

use App\Filament\Resources\CommodityCodes\CommodityCodeResource;
use App\Filament\Resources\ShippingTargets\ShippingTargetResource;
use App\Services\RajaOngkirService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShippingTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.shipping-targets.callouts.form-instruction')
                ->viewData([
                    'commodityCodeUrl' => CommodityCodeResource::getUrl(),
                    'shippingTargetUrl' => ShippingTargetResource::getUrl(),
                ])
                ->columnSpanFull(),

            Grid::make(12)
                ->schema([
                    TextInput::make('three_lc_code')
                        ->label('3LC Code')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('Contoh: CGK')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('country')
                        ->label('Negara')
                        ->required()
                        ->default('Indonesia')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Select::make('province_id')
                        ->label('Province ID')
                        ->options(fn (): array => self::provinceOptions())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->required()
                        ->afterStateUpdated(function (Set $set, mixed $state): void {
                            $selectedId = self::normalizeRegionId($state);
                            $provinceLabel = $selectedId ? (self::provinceOptions()[$selectedId] ?? null) : null;

                            $set('province', self::toUppercaseLabel($provinceLabel));
                            $set('city_id', null);
                            $set('city', null);
                            $set('district', null);
                            $set('district_lion', null);
                        })
                        ->helperText('Pilih ID provinsi dari master RajaOngkir.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Select::make('city_id')
                        ->label('City ID')
                        ->options(fn (Get $get): array => self::cityOptions($get('province_id')))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->required()
                        ->disabled(fn (Get $get): bool => blank($get('province_id')))
                        ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                            $selectedId = self::normalizeRegionId($state);
                            $cityOptions = self::cityOptions($get('province_id'));
                            $cityLabel = $selectedId ? ($cityOptions[$selectedId] ?? null) : null;

                            $set('city', self::toUppercaseLabel($cityLabel));
                            $set('district', null);
                            $set('district_lion', null);
                        })
                        ->helperText('Pilih ID kota/kabupaten setelah provinsi ditentukan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('province')
                        ->label('Provinsi')
                        ->required()
                        ->readOnly()
                        ->maxLength(255)
                        ->afterStateHydrated(function (Set $set, mixed $state): void {
                            $set('province', self::toUppercaseLabel($state));
                        })
                        ->placeholder('Terisi otomatis dari RajaOngkir')
                        ->helperText('Nama provinsi terisi otomatis dari pilihan Province ID.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('city')
                        ->label('Kota / Kabupaten')
                        ->required()
                        ->readOnly()
                        ->maxLength(255)
                        ->afterStateHydrated(function (Set $set, mixed $state): void {
                            $set('city', self::toUppercaseLabel($state));
                        })
                        ->placeholder('Terisi otomatis dari RajaOngkir')
                        ->helperText('Nama kota/kabupaten terisi otomatis dari pilihan City ID.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('district')
                        ->label('Kecamatan')
                        ->options(fn (Get $get): array => self::districtOptions($get('city_id')))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->required()
                        ->disabled(fn (Get $get): bool => blank($get('city_id')))
                        ->placeholder('Pilih kecamatan')
                        ->afterStateHydrated(function (Set $set, mixed $state): void {
                            $set('district', self::toUppercaseLabel($state));
                        })
                        ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                            $districtLionLabels = self::districtLionOptions($get('city_id'), $get('city'));
                            $districtLabel = self::toUppercaseLabel($state);

                            $set('district', $districtLabel);
                            $set('district_lion', $districtLabel ? ($districtLionLabels[$districtLabel] ?? $districtLabel) : null);
                        })
                        ->helperText('Daftar kecamatan dimuat dari service RajaOngkir berdasarkan City ID.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('district_lion')
                        ->label('Kecamatan (Lion)')
                        ->required()
                        ->maxLength(255)
                        ->afterStateHydrated(function (Set $set, mixed $state): void {
                            $set('district_lion', self::toUppercaseLabel($state));
                        })
                        ->placeholder('Terisi otomatis dari pilihan kecamatan')
                        ->helperText('Nilai default diisi otomatis. Ubah hanya jika vendor Lion Parcel membutuhkan format lain.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function provinceOptions(): array
    {
        return Cache::remember('shipping_target_form:provinces', now()->addHours(12), function (): array {
            $provinces = app(RajaOngkirService::class)->getProvinces();
            $options = [];

            foreach ($provinces as $province) {
                $id = self::extractRajaOngkirValue($province, ['id', 'province_id']);
                $name = self::extractRajaOngkirValue($province, ['province_name', 'province', 'name']);

                if (blank($id) || blank($name)) {
                    continue;
                }

                $options[(string) $id] = self::toUppercaseLabel($name) ?? (string) $name;
            }

            return $options;
        });
    }

    /**
     * @return array<string, string>
     */
    private static function cityOptions(mixed $provinceId): array
    {
        $provinceId = self::normalizeRegionId($provinceId);

        if ($provinceId === null) {
            return [];
        }

        return Cache::remember("shipping_target_form:cities:{$provinceId}", now()->addHours(12), function () use ($provinceId): array {
            $cities = app(RajaOngkirService::class)->getCities((int) $provinceId);
            $options = [];

            foreach ($cities as $city) {
                $id = self::extractRajaOngkirValue($city, ['id', 'city_id']);
                $name = self::extractRajaOngkirValue($city, ['city_name', 'city', 'name']);
                $type = self::extractRajaOngkirValue($city, ['type']);

                if (blank($id) || blank($name)) {
                    continue;
                }

                $label = filled($type) ? "{$type} {$name}" : (string) $name;
                $options[(string) $id] = self::toUppercaseLabel($label) ?? (string) $label;
            }

            return $options;
        });
    }

    /**
     * @return array<string, string>
     */
    private static function districtOptions(mixed $cityId): array
    {
        $cityId = self::normalizeRegionId($cityId);

        if ($cityId === null) {
            return [];
        }

        return Cache::remember("shipping_target_form:districts:{$cityId}", now()->addHours(12), function () use ($cityId): array {
            $districts = app(RajaOngkirService::class)->getDistricts((int) $cityId);
            $options = [];

            foreach ($districts as $district) {
                $name = self::extractRajaOngkirValue($district, ['district_name', 'subdistrict_name', 'district', 'name']);
                $label = self::toUppercaseLabel($name);

                if ($label === null) {
                    continue;
                }

                $options[$label] = $label;
            }

            return $options;
        });
    }

    /**
     * @return array<string, string>
     */
    private static function districtLionOptions(mixed $cityId, mixed $cityLabel = null): array
    {
        $cityId = self::normalizeRegionId($cityId);

        if ($cityId === null) {
            return [];
        }

        return Cache::remember(
            "shipping_target_form:district_lion:{$cityId}:".md5((string) $cityLabel),
            now()->addHours(12),
            function () use ($cityId, $cityLabel): array {
                $districts = app(RajaOngkirService::class)->getDistricts((int) $cityId);
                $options = [];

                foreach ($districts as $district) {
                    $label = self::toUppercaseLabel(self::extractRajaOngkirValue($district, ['district_name', 'subdistrict_name', 'district', 'name']));

                    if ($label === null) {
                        continue;
                    }

                    $districtLionLabel = self::toUppercaseLabel(self::extractRajaOngkirValue($district, ['district_lion']));
                    $options[$label] = $districtLionLabel ?? self::formatDistrictLionLabel($label, $cityLabel);
                }

                return $options;
            },
        );
    }

    private static function normalizeRegionId(mixed $value): ?string
    {
        if (blank($value) || ! is_scalar($value)) {
            return null;
        }

        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private static function extractRajaOngkirValue(mixed $row, array $keys): mixed
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

    private static function toUppercaseLabel(mixed $label): ?string
    {
        if (blank($label)) {
            return null;
        }

        return Str::upper(trim((string) $label));
    }

    private static function formatDistrictLionLabel(string $districtLabel, mixed $cityLabel = null): string
    {
        $normalizedDistrict = self::toUppercaseLabel($districtLabel) ?? '';

        if ($normalizedDistrict === '') {
            return '';
        }

        $normalizedCity = self::normalizeCityLabelForDistrictLion((string) ($cityLabel ?? ''));

        return $normalizedCity !== '' ? "{$normalizedDistrict}, {$normalizedCity}" : $normalizedDistrict;
    }

    private static function normalizeCityLabelForDistrictLion(string $cityLabel): string
    {
        $normalizedCity = self::toUppercaseLabel($cityLabel) ?? '';

        if ($normalizedCity === '') {
            return '';
        }

        $withoutPrefix = preg_replace('/^(KOTA|KABUPATEN)\s+/u', '', $normalizedCity);

        if (! is_string($withoutPrefix)) {
            return $normalizedCity;
        }

        return trim($withoutPrefix);
    }
}
