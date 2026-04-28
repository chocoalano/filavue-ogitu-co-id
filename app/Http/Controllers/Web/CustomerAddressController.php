<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddress\SaveCustomerAddressRequest;
use App\Models\Customer;
use App\Models\JneDestination;
use App\Services\CustomerAddress\CustomerAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CustomerAddressController extends Controller
{
    public function __construct(
        private readonly CustomerAddressService $customerAddressService,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'addresses']);
    }

    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->create($customer, $request->payload());

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(SaveCustomerAddressRequest $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->update($customer, $addressId, $request->payload());

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function setDefault(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->setDefault($customer, $addressId);

        return back()->with('success', 'Alamat default berhasil diperbarui.');
    }

    public function destroy(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->delete($customer, $addressId);

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function provinceOptions(): JsonResponse
    {
        return response()->json($this->jneProvinceOptions());
    }

    public function cityOptions(Request $request): JsonResponse
    {
        $provinceId = $this->normalizeIntegerId($request->query('province_id'));

        if ($provinceId === null) {
            return response()->json([]);
        }

        return response()->json($this->jneCityOptions($provinceId));
    }

    public function districtOptions(Request $request): JsonResponse
    {
        $cityId = $this->normalizeIntegerId($request->query('city_id'));

        if ($cityId === null) {
            return response()->json([]);
        }

        return response()->json($this->jneDistrictOptions($cityId));
    }

    /**
     * Ambil daftar provinsi dari tabel jne_destinations.
     *
     * Output:
     * [
     *     ['id' => 1, 'label' => 'BANTEN'],
     *     ['id' => 2, 'label' => 'JAWA BARAT'],
     * ]
     *
     * id menggunakan MIN(id) dari jne_destinations agar tetap integer.
     *
     * @return array<int, array{id:int,label:string}>
     */
    private function jneProvinceOptions(): array
    {
        return Cache::remember('customer_address_web:jne:v1:provinces', now()->addHours(12), function (): array {
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
     * Ambil daftar kota berdasarkan province_id.
     *
     * province_id yang diterima adalah id numerik dari salah satu row jne_destinations.
     * Dari id tersebut sistem ambil province_name, lalu mencari semua city_name di provinsi itu.
     *
     * Output:
     * [
     *     ['id' => 10, 'province_id' => 1, 'label' => 'CILEGON'],
     *     ['id' => 11, 'province_id' => 1, 'label' => 'SERANG'],
     * ]
     *
     * @return array<int, array{id:int,province_id:int,label:string}>
     */
    private function jneCityOptions(int $provinceId): array
    {
        $province = $this->destinationById($provinceId);

        if (! $province || blank($province->province_name)) {
            return [];
        }

        $provinceName = (string) $province->province_name;

        $cacheKey = 'customer_address_web:jne:v1:cities:' . md5($provinceName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($provinceId, $provinceName): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, city_name')
                ->where('province_name', $provinceName)
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->groupBy('city_name')
                ->orderBy('city_name')
                ->get()
                ->map(function ($row) use ($provinceId): ?array {
                    $id = $this->normalizeIntegerId($row->id);
                    $label = $this->toUppercaseLabel($row->city_name);

                    if ($id === null || $label === null) {
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
     * Ambil daftar kecamatan berdasarkan city_id.
     *
     * city_id yang diterima adalah id numerik dari salah satu row jne_destinations.
     * Dari id tersebut sistem ambil province_name + city_name, lalu mencari semua district_name.
     *
     * Output:
     * [
     *     [
     *         'id' => 100,
     *         'city_id' => 10,
     *         'label' => 'CIBEBER',
     *         'district_lion' => 'CIBEBER',
     *         'tariff_code' => 'CGK10000',
     *         'zip_code' => '42423',
     *     ],
     * ]
     *
     * @return array<int, array{id:int,city_id:int,label:string,district_lion:string,tariff_code:?string,zip_code:?string}>
     */
    private function jneDistrictOptions(int $cityId): array
    {
        $city = $this->destinationById($cityId);

        if (! $city || blank($city->province_name) || blank($city->city_name)) {
            return [];
        }

        $provinceName = (string) $city->province_name;
        $cityName = (string) $city->city_name;

        $cacheKey = 'customer_address_web:jne:v1:districts:' . md5($provinceName . '|' . $cityName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($cityId, $provinceName, $cityName): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, district_name, MIN(tariff_code) as tariff_code, MIN(zip_code) as zip_code')
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
                        'tariff_code' => filled($row->tariff_code) ? (string) $row->tariff_code : null,
                        'zip_code' => filled($row->zip_code) ? (string) $row->zip_code : null,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    private function destinationById(int $id): ?JneDestination
    {
        return Cache::remember("customer_address_web:jne:v1:destination:{$id}", now()->addHours(12), function () use ($id): ?JneDestination {
            return JneDestination::query()->find($id);
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

    private function toUppercaseLabel(mixed $label): ?string
    {
        if (blank($label)) {
            return null;
        }

        return Str::upper(trim((string) $label));
    }
}
