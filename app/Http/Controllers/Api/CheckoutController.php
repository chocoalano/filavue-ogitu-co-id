<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\MidtransTokenRequest;
use App\Http\Requests\Checkout\SaldoPayRequest;
use App\Models\Payment;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class CheckoutController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly MidtransService $midtransService,
        private readonly ShippingTargetRepositoryInterface $shippingRepository,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/checkout",
     *     tags={"Checkout"},
     *     summary="Data checkout customer",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data checkout berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data checkout berhasil diambil.",
     *                 "data":{
     *                     "items":{{"id":1,"name":"Produk A","qty":2,"row_total":250000}},
     *                     "cart":{"subtotal":250000,"shipping":15000,"total":265000},
     *                     "addresses":{{"id":11,"label":"Rumah"}},
     *                     "saldo":500000,
     *                     "midtrans":{"env":"sandbox","client_key":"SB-Mid-client-xxx"}
     *                 }
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

        return response()->json([
            'message' => 'Data checkout berhasil diambil.',
            'data' => $this->checkoutService->getPageData($customer),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/checkout/shipping/provinces",
     *     tags={"Checkout"},
     *     summary="Daftar provinsi pengiriman",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar provinsi berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(type="string", example="JAWA BARAT"),
     *             example={"JAWA BARAT","DKI JAKARTA"}
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."}))
     * )
     */
    public function provinces(): JsonResponse
    {
        return response()->json($this->shippingRepository->provinces());
    }

    /**
     * @OA\Get(
     *     path="/api/checkout/shipping/cities",
     *     tags={"Checkout"},
     *     summary="Daftar kota berdasarkan provinsi",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="province", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kota berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(type="string", example="KOTA BANDUNG"),
     *             example={"KOTA BANDUNG","KABUPATEN BANDUNG"}
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function cities(Request $request): JsonResponse
    {
        $request->validate(['province' => ['required', 'string', 'max:255']]);
        $province = trim((string) $request->input('province'));

        return response()->json($this->shippingRepository->citiesByProvince($province));
    }

    /**
     * @OA\Get(
     *     path="/api/checkout/shipping/districts",
     *     tags={"Checkout"},
     *     summary="Daftar kecamatan berdasarkan provinsi dan kota",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="province", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="city", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kecamatan berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(type="string", example="COBLONG"),
     *             example={"COBLONG","SUKASARI"}
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function districts(Request $request): JsonResponse
    {
        $request->validate([
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
        ]);

        $province = trim((string) $request->input('province'));
        $city = trim((string) $request->input('city'));

        return response()->json(
            $this->shippingRepository->districtsByProvinceAndCity($province, $city)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/checkout/shipping/cost",
     *     tags={"Checkout"},
     *     summary="Kalkulasi ongkir Lion Parcel",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="province", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="city", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="district", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tarif ongkir berhasil dihitung",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="product", type="string", example="REGPACK"),
     *                 @OA\Property(property="total_tariff", type="integer", example=19000),
     *                 @OA\Property(property="estimasi_sla", type="string", example="2-3 Hari")
     *             ),
     *             example={{"product":"REGPACK","total_tariff":19000,"estimasi_sla":"2-3 Hari"}}
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(
     *         response=422,
     *         description="Tujuan pengiriman tidak tersedia",
     *
     *         @OA\JsonContent(example={"message":"Tujuan pengiriman tidak tersedia."})
     *     )
     * )
     */
    public function shippingCost(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $request->validate([
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
        ]);

        $province = trim((string) $request->input('province'));
        $city = trim((string) $request->input('city'));
        $district = trim((string) $request->input('district', ''));
        $district = $district !== '' ? $district : null;

        $destinationLion = $this->shippingRepository->findDistrictLion($province, $city, $district);

        if (! $destinationLion) {
            return response()->json(['message' => 'Tujuan pengiriman tidak tersedia.'], 422);
        }

        return response()->json($this->checkoutService->calculateShippingRates($customer, $destinationLion));
    }

    /**
     * @OA\Post(
     *     path="/api/checkout/midtrans/token",
     *     tags={"Checkout"},
     *     summary="Buat Midtrans Snap token untuk checkout",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"address_mode","order_type"},
     *
     *             @OA\Property(property="address_mode", type="string", enum={"saved","manual","pickup"}, example="saved"),
     *             @OA\Property(property="order_type", type="string", enum={"planA","planB"}, example="planA"),
     *             @OA\Property(property="address_id", type="integer", nullable=true, example=12),
     *             @OA\Property(property="recipient_name", type="string", nullable=true, example="Budi Santoso"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="08123456789"),
     *             @OA\Property(property="address_line", type="string", nullable=true, example="Jl. Merdeka No. 1, RT 02/RW 01"),
     *             @OA\Property(property="province", type="string", nullable=true, example="JAWA BARAT"),
     *             @OA\Property(property="city", type="string", nullable=true, example="KOTA BANDUNG"),
     *             @OA\Property(property="province_id", type="integer", nullable=true, example=9),
     *             @OA\Property(property="city_id", type="integer", nullable=true, example=501),
     *             @OA\Property(property="district", type="string", nullable=true, example="COBLONG"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="40132"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Tolong kirim sore hari."),
     *             @OA\Property(property="shipping_service_code", type="string", nullable=true, example="REGPACK"),
     *             @OA\Property(property="shipping_cost", type="number", format="float", example=15000),
     *             @OA\Property(property="shipping_etd", type="string", nullable=true, example="2-3 Hari")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Snap token berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "snapToken":"midtrans-snap-token",
     *                 "orderId":1201,
     *                 "orderNo":"ORD-20260301-ABC123"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}})),
     *     @OA\Response(response=500, description="Gagal membuat token", @OA\JsonContent(example={"message":"Gagal membuat token pembayaran Midtrans."}))
     * )
     */
    public function getMidtransToken(MidtransTokenRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            ['order' => $order, 'cart' => $cart] = $this->checkoutService->prepareMidtransOrder(
                $customer,
                $request->addressData()
            );

            $snapToken = $this->midtransService->createSnapToken($order, $cart, $customer);

            $latestPayment = $order->payments()->latest('id')->first();

            if ($latestPayment instanceof Payment) {
                $metadata = is_array($latestPayment->metadata_json) ? $latestPayment->metadata_json : [];
                $latestPayment->update([
                    'metadata_json' => array_merge($metadata, [
                        'snap_token' => $snapToken,
                        'snap_created_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            return response()->json([
                'snapToken' => $snapToken,
                'orderId' => $order->id,
                'orderNo' => $order->order_no,
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal membuat token pembayaran Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/checkout/pay/saldo",
     *     tags={"Checkout"},
     *     summary="Bayar checkout menggunakan saldo ewallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"address_mode","order_type"},
     *
     *             @OA\Property(property="address_mode", type="string", enum={"saved","manual","pickup"}, example="saved"),
     *             @OA\Property(property="order_type", type="string", enum={"planA","planB"}, example="planA"),
     *             @OA\Property(property="address_id", type="integer", nullable=true, example=12),
     *             @OA\Property(property="recipient_name", type="string", nullable=true, example="Budi Santoso"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="08123456789"),
     *             @OA\Property(property="address_line", type="string", nullable=true, example="Jl. Merdeka No. 1, RT 02/RW 01"),
     *             @OA\Property(property="province", type="string", nullable=true, example="JAWA BARAT"),
     *             @OA\Property(property="city", type="string", nullable=true, example="KOTA BANDUNG"),
     *             @OA\Property(property="province_id", type="integer", nullable=true, example=9),
     *             @OA\Property(property="city_id", type="integer", nullable=true, example=501),
     *             @OA\Property(property="district", type="string", nullable=true, example="COBLONG"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="40132"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Tolong kirim sore hari."),
     *             @OA\Property(property="shipping_service_code", type="string", nullable=true, example="REGPACK"),
     *             @OA\Property(property="shipping_cost", type="number", format="float", example=15000),
     *             @OA\Property(property="shipping_etd", type="string", nullable=true, example="2-3 Hari")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Pembayaran saldo berhasil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Pembayaran berhasil.",
     *                 "orderId":1202,
     *                 "orderNo":"ORD-20260301-XYZ999"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function payWithSaldo(SaldoPayRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $order = $this->checkoutService->payWithSaldo($customer, $request->addressData());

            return response()->json([
                'message' => 'Pembayaran berhasil.',
                'orderId' => $order->id,
                'orderNo' => $order->order_no,
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memproses pembayaran saldo.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
