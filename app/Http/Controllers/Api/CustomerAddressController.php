<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddress\SaveCustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Models\JneDestination;
use App\Services\CustomerAddress\CustomerAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class CustomerAddressController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly CustomerAddressService $customerAddressService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/account/addresses",
     *     tags={"Customer Address"},
     *     summary="Daftar alamat customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar alamat berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Daftar alamat berhasil diambil.",
     *                 "data":{{
     *                     "id":11,
     *                     "label":"Rumah",
     *                     "recipient_name":"Budi Santoso",
     *                     "recipient_phone":"08123456789",
     *                     "address_line1":"Jl. Merdeka No. 1",
     *                     "province_label":"JAWA BARAT",
     *                     "province_id":9,
     *                     "city_label":"KOTA BANDUNG",
     *                     "city_id":501,
     *                     "district":"COBLONG",
     *                     "is_default":true
     *                 }}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $addresses = $customer->addresses()
            ->latest('is_default')
            ->latest('id')
            ->get()
            ->map(fn (CustomerAddress $address): array => [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'address_line1' => $address->address_line1,
                'address_line2' => $address->address_line2,
                'province_label' => $address->province_label,
                'province_id' => $address->province_id,
                'city_label' => $address->city_label,
                'city_id' => $address->city_id,
                'district' => $address->district,
                'district_lion' => $address->district_lion,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'description' => $address->description,
                'is_default' => (bool) $address->is_default,
                'created_at' => $address->created_at?->toIso8601String(),
                'updated_at' => $address->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return response()->json([
            'message' => 'Daftar alamat berhasil diambil.',
            'data' => $addresses,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/account/addresses",
     *     tags={"Customer Address"},
     *     summary="Tambah alamat customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"recipient_name","recipient_phone","address_line1","province_label","province_id","city_label","city_id","district"},
     *
     *             @OA\Property(property="label", type="string", nullable=true, example="Rumah"),
     *             @OA\Property(property="is_default", type="boolean", example=true),
     *             @OA\Property(property="recipient_name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="recipient_phone", type="string", example="08123456789"),
     *             @OA\Property(property="address_line1", type="string", example="Jl. Merdeka No. 1"),
     *             @OA\Property(property="address_line2", type="string", nullable=true, example="Dekat masjid"),
     *             @OA\Property(property="province_label", type="string", example="JAWA BARAT"),
     *             @OA\Property(property="province_id", type="integer", example=9),
     *             @OA\Property(property="city_label", type="string", example="KOTA BANDUNG"),
     *             @OA\Property(property="city_id", type="integer", example=501),
     *             @OA\Property(property="district", type="string", example="COBLONG"),
     *             @OA\Property(property="district_lion", type="string", nullable=true, example="COBLONG"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="40132"),
     *             @OA\Property(property="country", type="string", nullable=true, example="Indonesia"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Alamat pengiriman utama")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Alamat berhasil ditambahkan", @OA\JsonContent(example={"message":"Alamat berhasil ditambahkan.","data":{"id":11}})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function store(SaveCustomerAddressRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $address = $this->customerAddressService->create($customer, $request->payload());

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => [
                'id' => $address->id,
            ],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/account/addresses/{addressId}",
     *     tags={"Customer Address"},
     *     summary="Perbarui alamat customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="addressId", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"recipient_name","recipient_phone","address_line1","province_label","province_id","city_label","city_id","district"},
     *
     *             @OA\Property(property="label", type="string", nullable=true, example="Rumah"),
     *             @OA\Property(property="is_default", type="boolean", example=false),
     *             @OA\Property(property="recipient_name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="recipient_phone", type="string", example="08123456789"),
     *             @OA\Property(property="address_line1", type="string", example="Jl. Merdeka No. 1"),
     *             @OA\Property(property="address_line2", type="string", nullable=true, example="Blok A2"),
     *             @OA\Property(property="province_label", type="string", example="JAWA BARAT"),
     *             @OA\Property(property="province_id", type="integer", example=9),
     *             @OA\Property(property="city_label", type="string", example="KOTA BANDUNG"),
     *             @OA\Property(property="city_id", type="integer", example=501),
     *             @OA\Property(property="district", type="string", example="COBLONG"),
     *             @OA\Property(property="district_lion", type="string", nullable=true, example="COBLONG"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="40132"),
     *             @OA\Property(property="country", type="string", nullable=true, example="Indonesia"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Alamat kirim terbaru")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Alamat berhasil diperbarui", @OA\JsonContent(example={"message":"Alamat berhasil diperbarui."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function update(SaveCustomerAddressRequest $request, int $addressId): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->customerAddressService->update($customer, $addressId, $request->payload());

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/account/addresses/{addressId}/default",
     *     tags={"Customer Address"},
     *     summary="Set alamat default",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="addressId", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(response=200, description="Alamat default berhasil diperbarui", @OA\JsonContent(example={"message":"Alamat default berhasil diperbarui."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function setDefault(Request $request, int $addressId): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->customerAddressService->setDefault($customer, $addressId);

        return response()->json([
            'message' => 'Alamat default berhasil diperbarui.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/account/addresses/{addressId}",
     *     tags={"Customer Address"},
     *     summary="Hapus alamat customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="addressId", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Alamat berhasil dihapus", @OA\JsonContent(example={"message":"Alamat berhasil dihapus."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function destroy(Request $request, int $addressId): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $this->customerAddressService->delete($customer, $addressId);

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/account/addresses/options/provinces",
     *     tags={"Customer Address"},
     *     summary="Opsi provinsi dari master JNE lokal",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar provinsi berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=9),
     *                 @OA\Property(property="label", type="string", example="JAWA BARAT")
     *             ),
     *             example={{"id":9,"label":"JAWA BARAT"},{"id":31,"label":"DKI JAKARTA"}}
     *         )
     *     )
     * )
     */
    public function provinceOptions(): JsonResponse
    {
        return response()->json($this->jneProvinceOptions());
    }

    /**
     * @OA\Get(
     *     path="/api/account/addresses/options/cities",
     *     tags={"Customer Address"},
     *     summary="Opsi kota berdasarkan provinsi dari master JNE lokal",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="province_id", in="query", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kota berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=501),
     *                 @OA\Property(property="province_id", type="integer", example=9),
     *                 @OA\Property(property="label", type="string", example="KOTA BANDUNG")
     *             ),
     *             example={{"id":501,"province_id":9,"label":"KOTA BANDUNG"}}
     *         )
     *     )
     * )
     */
    public function cityOptions(Request $request): JsonResponse
    {
        $provinceId = $this->normalizeIntegerId($request->query('province_id'));

        if ($provinceId === null) {
            return response()->json([]);
        }

        return response()->json($this->jneCityOptions($provinceId));
    }

    /**
     * @OA\Get(
     *     path="/api/account/addresses/options/districts",
     *     tags={"Customer Address"},
     *     summary="Opsi kecamatan berdasarkan kota dari master JNE lokal",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="city_id", in="query", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kecamatan berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=7001),
     *                 @OA\Property(property="city_id", type="integer", example=501),
     *                 @OA\Property(property="label", type="string", example="COBLONG"),
     *                 @OA\Property(property="district_lion", type="string", example="COBLONG"),
     *                 @OA\Property(property="tariff_code", type="string", nullable=true, example="BDO10000"),
     *                 @OA\Property(property="zip_code", type="string", nullable=true, example="40132")
     *             ),
     *             example={{"id":7001,"city_id":501,"label":"COBLONG","district_lion":"COBLONG","tariff_code":"BDO10000","zip_code":"40132"}}
     *         )
     *     )
     * )
     */
    public function districtOptions(Request $request): JsonResponse
    {
        $cityId = $this->normalizeIntegerId($request->query('city_id'));

        if ($cityId === null) {
            return response()->json([]);
        }

        return response()->json($this->jneDistrictOptions($cityId));
    }

    /**
     * @return array<int, array{id:int,label:string}>
     */
    private function jneProvinceOptions(): array
    {
        return Cache::remember('customer_address_api:jne:v1:provinces', now()->addHours(12), function (): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, province_name')
                ->whereNotNull('province_name')
                ->where('province_name', '!=', '')
                ->groupBy('province_name')
                ->orderBy('province_name')
                ->get()
                ->map(function (JneDestination $row): ?array {
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
     * @return array<int, array{id:int,province_id:int,label:string}>
     */
    private function jneCityOptions(int $provinceId): array
    {
        $province = $this->destinationById($provinceId);

        if (! $province || blank($province->province_name)) {
            return [];
        }

        $provinceName = (string) $province->province_name;
        $cacheKey = 'customer_address_api:jne:v1:cities:'.md5($provinceName);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($provinceId, $provinceName): array {
            return JneDestination::query()
                ->selectRaw('MIN(id) as id, city_name')
                ->where('province_name', $provinceName)
                ->whereNotNull('city_name')
                ->where('city_name', '!=', '')
                ->groupBy('city_name')
                ->orderBy('city_name')
                ->get()
                ->map(function (JneDestination $row) use ($provinceId): ?array {
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
        $cacheKey = 'customer_address_api:jne:v1:districts:'.md5($provinceName.'|'.$cityName);

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
                ->map(function (JneDestination $row) use ($cityId): ?array {
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
        return Cache::remember("customer_address_api:jne:v1:destination:{$id}", now()->addHours(12), function () use ($id): ?JneDestination {
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
