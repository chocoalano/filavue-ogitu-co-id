<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddress\SaveCustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\CustomerAddress\CustomerAddressService;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CustomerAddressController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly CustomerAddressService $customerAddressService,
        private readonly RajaOngkirService $rajaOngkirService,
        private readonly ShippingTargetRepositoryInterface $shippingRepository,
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
     *                     "city_label":"KOTA BANDUNG",
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
     *     summary="Opsi provinsi",
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
     *                 @OA\Property(property="label", type="string", example="Jawa Barat")
     *             ),
     *             example={{"id":9,"label":"Jawa Barat"},{"id":31,"label":"DKI Jakarta"}}
     *         )
     *     )
     * )
     */
    public function provinceOptions(): JsonResponse
    {
        $items = collect($this->rajaOngkirService->getProvinces())
            ->map(function (mixed $province): ?array {
                $id = $this->extractRegionValue($province, ['id', 'province_id']);
                $label = $this->extractRegionValue($province, ['province_name', 'province', 'name']);

                if (! is_numeric($id) || ! is_string($label) || trim($label) === '') {
                    return null;
                }

                return [
                    'id' => (int) $id,
                    'label' => trim($label),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return response()->json($items);
    }

    /**
     * @OA\Get(
     *     path="/api/account/addresses/options/cities",
     *     tags={"Customer Address"},
     *     summary="Opsi kota berdasarkan provinsi",
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
     *                 @OA\Property(property="label", type="string", example="Kota Bandung")
     *             ),
     *             example={{"id":501,"province_id":9,"label":"Kota Bandung"}}
     *         )
     *     )
     * )
     */
    public function cityOptions(Request $request): JsonResponse
    {
        $provinceId = (int) $request->integer('province_id');

        if ($provinceId < 1) {
            return response()->json([]);
        }

        $items = collect($this->rajaOngkirService->getCities($provinceId))
            ->map(function (mixed $city) use ($provinceId): ?array {
                $id = $this->extractRegionValue($city, ['id', 'city_id']);
                $name = $this->extractRegionValue($city, ['city_name', 'city', 'name']);
                $type = $this->extractRegionValue($city, ['type']);

                if (! is_numeric($id) || ! is_string($name) || trim($name) === '') {
                    return null;
                }

                $label = is_string($type) && trim($type) !== ''
                    ? trim($type).' '.trim($name)
                    : trim($name);

                return [
                    'id' => (int) $id,
                    'province_id' => $provinceId,
                    'label' => $label,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return response()->json($items);
    }

    /**
     * @OA\Get(
     *     path="/api/account/addresses/options/districts",
     *     tags={"Customer Address"},
     *     summary="Opsi kecamatan berdasarkan kota",
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
     *                 @OA\Property(property="label", type="string", example="Coblong"),
     *                 @OA\Property(property="district_lion", type="string", example="COBLONG")
     *             ),
     *             example={{"id":7001,"city_id":501,"label":"Coblong","district_lion":"COBLONG"}}
     *         )
     *     )
     * )
     */
    public function districtOptions(Request $request): JsonResponse
    {
        $cityId = (int) $request->integer('city_id');

        if ($cityId < 1) {
            return response()->json([]);
        }

        return response()->json($this->shippingRepository->districtOptionsByCityId($cityId));
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
