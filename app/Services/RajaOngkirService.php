<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key_shipping');
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    }

    /**
     * Get list of provinces
     */
    public function getProvinces(): array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/province");

            if ($response->successful()) {
                $data = $response->json();

                // New API v2 returns data directly in 'data' key
                return $data['data'] ?? [];
            }

            Log::error('RajaOngkir Get Provinces Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Provinces Exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get list of cities by province
     */
    public function getCities(?int $provinceId = null): array
    {
        try {
            if (! $provinceId) {
                return [];
            }

            // New API v2: GET /destination/city/{province_id}
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/city/{$provinceId}");

            if ($response->successful()) {
                $data = $response->json();

                // New API v2 returns data directly in 'data' key
                return $data['data'] ?? [];
            }

            Log::error('RajaOngkir Get Cities Error', [
                'province_id' => $provinceId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Cities Exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get list of districts by city
     */
    public function getDistricts(?int $cityId = null): array
    {
        try {
            if (! $cityId) {
                return [];
            }

            // API v2 style: GET /destination/district/{city_id}
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/district/{$cityId}");

            if ($response->successful()) {
                $data = $response->json();

                return $data['data'] ?? [];
            }

            Log::error('RajaOngkir Get Districts Error', [
                'city_id' => $cityId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Districts Exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get shipping cost calculation
     *
     * @param  int  $originDistrictId  District ID of origin
     * @param  int  $destinationDistrictId  District ID of destination
     * @param  int  $weight  Weight in grams
     * @param  string  $courier  Courier code (jne, pos, tiki, etc.)
     */
    public function getCost(int $originDistrictId, int $destinationDistrictId, int $weight, string $courier): array
    {
        try {
            // New API v2: POST /calculate/district/domestic-cost
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post("{$this->baseUrl}/calculate/district/domestic-cost", [
                'origin' => $originDistrictId,
                'destination' => $destinationDistrictId,
                'weight' => $weight,
                'courier' => $courier,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Log the response for debugging
                Log::info('RajaOngkir Get Cost Response', [
                    'origin' => $originDistrictId,
                    'destination' => $destinationDistrictId,
                    'courier' => $courier,
                    'response' => $data,
                ]);

                // New API v2 returns array of services directly in 'data' key
                $services = $data['data'] ?? [];

                // If no services returned, log and return structured error
                if (empty($services)) {
                    Log::warning('RajaOngkir returned empty services', [
                        'origin' => $originDistrictId,
                        'destination' => $destinationDistrictId,
                        'courier' => $courier,
                        'full_response' => $data,
                    ]);

                    return [
                        'code' => $courier,
                        'name' => strtoupper($courier),
                        'costs' => [],
                        'error' => 'Tidak ada layanan tersedia',
                    ];
                }

                // Filter services for the requested courier
                $courierServices = array_filter($services, function ($service) use ($courier) {
                    return strtolower($service['code'] ?? '') === strtolower($courier);
                });

                // If no matching courier found, return all services or empty
                if (empty($courierServices)) {
                    Log::warning('No services found for requested courier', [
                        'requested_courier' => $courier,
                        'available_couriers' => array_unique(array_column($services, 'code')),
                    ]);

                    return [
                        'code' => $courier,
                        'name' => strtoupper($courier),
                        'costs' => [],
                        'error' => 'Kurir tidak tersedia',
                    ];
                }

                // Format to match the expected structure
                return [
                    'code' => $courier,
                    'name' => ! empty($courierServices) ? reset($courierServices)['name'] : strtoupper($courier),
                    'costs' => array_map(function ($service) {
                        return [
                            'service' => $service['service'] ?? '',
                            'description' => $service['description'] ?? '',
                            'cost' => [
                                [
                                    'value' => $service['cost'] ?? 0,
                                    'etd' => $service['etd'] ?? '',
                                    'note' => '',
                                ],
                            ],
                        ];
                    }, array_values($courierServices)),
                ];
            }

            Log::error('RajaOngkir Get Cost Error', [
                'origin' => $originDistrictId,
                'destination' => $destinationDistrictId,
                'weight' => $weight,
                'courier' => $courier,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'error' => 'API Error',
                'status' => $response->status(),
                'message' => 'Failed to fetch shipping cost',
            ];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Cost Exception: '.$e->getMessage(), [
                'origin' => $originDistrictId,
                'destination' => $destinationDistrictId,
                'weight' => $weight,
                'courier' => $courier,
                'exception' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Exception',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of available couriers from API
     * Note: API v2 doesn't have /courier endpoint, so we use common couriers
     */
    public function getCouriers(): array
    {
        // API v2 doesn't provide a courier list endpoint
        // Return common couriers used in Indonesia
        return $this->getCommonCouriers();
    }

    /**
     * Get common couriers as fallback
     */
    public function getCommonCouriers(): array
    {
        return [
            'jne' => 'JNE',
            'pos' => 'POS Indonesia',
            'tiki' => 'TIKI',
            'jnt' => 'J&T Express',
            'sicepat' => 'SiCepat',
            'anteraja' => 'AnterAja',
            'ninja' => 'Ninja Xpress',
            'idexpress' => 'ID Express',
            'rpx' => 'RPX',
            'sap' => 'SAP Express',
        ];
    }
}
