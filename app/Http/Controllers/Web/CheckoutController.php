<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\MidtransTokenRequest;
use App\Http\Requests\Checkout\SaldoPayRequest;
use App\Models\Customer;
use App\Models\JneDestination;
use App\Models\Payment;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected MidtransService $midtransService,
        protected ShippingTargetRepositoryInterface $shippingRepository,
    ) {}

    /**
     * Halaman checkout — muat data dari keranjang customer.
     */
    public function index(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return inertia('Auth/Checkout/Index', $this->checkoutService->getPageData($customer));
    }

    /**
     * Daftar provinsi yang tersedia di shipping_targets.
     */
    public function provinces(): JsonResponse
    {
        return response()->json($this->shippingRepository->provinces());
    }

    /**
     * Daftar kota untuk provinsi tertentu.
     */
    public function cities(Request $request): JsonResponse
    {
        $request->validate(['province' => ['required', 'string', 'max:255']]);
        $province = trim((string) $request->input('province'));

        return response()->json(
            $this->shippingRepository->citiesByProvince($province)
        );
    }

    /**
     * Daftar kecamatan untuk provinsi + kota tertentu.
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
            $this->shippingRepository->districtsByProvinceAndCity(
                $province,
                $city,
            )
        );
    }

    /**
     * Tarif ongkir untuk tujuan yang dipilih.
     * Parameter `district` adalah kode district_lion dari.
     */
    public function shippingCost(Request $request): JsonResponse
    {
        $request->validate([
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return response()->json([
                'message' => 'Customer tidak terautentikasi.',
            ], 401);
        }

        $province = $this->normalizeJneRegionName($request->input('province'));
        $city = $this->normalizeJneRegionName($request->input('city'));
        $district = $this->normalizeJneRegionName($request->input('district'));

        if ($province === '' || $city === '') {
            return response()->json([
                'message' => 'Provinsi dan kota tujuan wajib diisi.',
            ], 422);
        }

        $destinationCode = $this->resolveJneDestinationTariffCode(
            province: $province,
            city: $city,
            district: $district !== '' ? $district : null,
        );

        if (! $destinationCode) {
            return response()->json([
                'message' => 'Kode tujuan JNE tidak ditemukan. Pastikan provinsi, kota, dan kecamatan sesuai data JNE.',
            ], 422);
        }

        $rates = $this->checkoutService->calculateShippingRates(
            customer: $customer,
            destinationDistrictLion: $destinationCode,
        );

        return response()->json($rates);
    }

    /**
     * Buat Midtrans Snap token untuk order yang baru dibuat.
     */
    public function getMidtransToken(MidtransTokenRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        try {
            ['order' => $order, 'cart' => $cart] = $this->checkoutService->prepareMidtransOrder(
                $customer,
                $request->addressData(),
            );

            $snapToken = $this->midtransService->createSnapToken($order, $cart, $customer);

            /** @var Payment|null $latestPayment */
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

            $result = [
                'snapToken' => $snapToken,
                'orderId' => $order->id,
                'orderNo' => $order->order_no,
                'successUrl' => route('dashboard'),
            ];

            if ($this->isInertiaRequest($request)) {
                return back()->with('checkout', [
                    'action' => 'midtrans_token_created',
                    'message' => 'Token pembayaran Midtrans berhasil dibuat.',
                    'payload' => $result,
                ]);
            }

            return response()->json($result);
        } catch (ValidationException $e) {
            dd($e->errors());
            return $this->validationFailure($request, $e, 'Gagal membuat token pembayaran Midtrans.');
        } catch (\RuntimeException $e) {
            if ($this->isInertiaRequest($request)) {
                return back()->with('checkout', [
                    'action' => 'error',
                    'message' => $e->getMessage(),
                ]);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proses pembayaran menggunakan saldo ewallet customer.
     */
    public function payWithSaldo(SaldoPayRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        try {
            $order = $this->checkoutService->payWithSaldo($customer, $request->addressData());

            $result = [
                'message' => 'Pembayaran berhasil.',
                'orderId' => $order->id,
                'orderNo' => $order->order_no,
                'redirectTo' => route('dashboard'),
            ];

            if ($this->isInertiaRequest($request)) {
                return back()->with('checkout', [
                    'action' => 'saldo_paid',
                    'message' => $result['message'],
                    'payload' => $result,
                ]);
            }

            return response()->json($result);
        } catch (ValidationException $e) {
            return $this->validationFailure($request, $e, 'Gagal memproses pembayaran saldo.');
        }
    }

    private function isInertiaRequest(Request $request): bool
    {
        return $request->headers->has('X-Inertia');
    }

    private function validationFailure(
        Request $request,
        ValidationException $exception,
        string $fallbackMessage
    ): JsonResponse|RedirectResponse {
        $firstError = collect($exception->errors())->flatten()->first();
        $message = is_string($firstError) ? $firstError : ($exception->getMessage() ?: $fallbackMessage);

        if ($this->isInertiaRequest($request)) {
            return back()
                ->withErrors($exception->errors())
                ->with('checkout', [
                    'action' => 'error',
                    'message' => $message,
                ]);
        }

        return response()->json([
            'message' => $message,
            'errors' => $exception->errors(),
        ], 422);
    }

    private function resolveJneDestinationTariffCode(
        string $province,
        string $city,
        ?string $district = null,
    ): ?string {
        $province = $this->normalizeJneRegionName($province);
        $city = $this->normalizeJneRegionName($city);
        $district = $district !== null ? $this->normalizeJneRegionName($district) : null;

        if ($province === '' || $city === '') {
            return null;
        }

        $cityCandidates = $this->cityCandidates($city);
        $districtCandidates = $district ? $this->districtCandidates($district) : [];

        $query = JneDestination::query()
            ->whereNotNull('tariff_code')
            ->where('tariff_code', '!=', '')
            ->whereRaw('UPPER(TRIM(province_name)) = ?', [$province]);

        $query->where(function ($query) use ($cityCandidates): void {
            foreach ($cityCandidates as $cityCandidate) {
                $query->orWhereRaw('UPPER(TRIM(city_name)) = ?', [$cityCandidate]);
            }
        });

        if ($districtCandidates !== []) {
            $query->where(function ($query) use ($districtCandidates): void {
                foreach ($districtCandidates as $districtCandidate) {
                    $query
                        ->orWhereRaw('UPPER(TRIM(district_name)) = ?', [$districtCandidate])
                        ->orWhereRaw('UPPER(TRIM(subdistrict_name)) = ?', [$districtCandidate]);
                }
            });
        }

        $tariffCode = $query
            ->orderBy('id')
            ->value('tariff_code');

        if (filled($tariffCode)) {
            return Str::upper(trim((string) $tariffCode));
        }

        /**
         * Fallback:
         * Kalau district tidak ketemu, ambil tariff_code pertama berdasarkan provinsi + kota.
         */
        $fallbackQuery = JneDestination::query()
            ->whereNotNull('tariff_code')
            ->where('tariff_code', '!=', '')
            ->whereRaw('UPPER(TRIM(province_name)) = ?', [$province]);

        $fallbackQuery->where(function ($query) use ($cityCandidates): void {
            foreach ($cityCandidates as $cityCandidate) {
                $query->orWhereRaw('UPPER(TRIM(city_name)) = ?', [$cityCandidate]);
            }
        });

        $fallbackTariffCode = $fallbackQuery
            ->orderBy('id')
            ->value('tariff_code');

        return filled($fallbackTariffCode)
            ? Str::upper(trim((string) $fallbackTariffCode))
            : null;
    }

    private function normalizeJneRegionName(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        $value = Str::upper(trim((string) $value));

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value);

        return is_string($value) ? trim($value) : '';
    }

    /**
     * @return list<string>
     */
    private function cityCandidates(string $city): array
    {
        $city = $this->normalizeJneRegionName($city);

        if ($city === '') {
            return [];
        }

        $withoutPrefix = $this->removeCityPrefix($city);

        return collect([
            $city,
            $withoutPrefix,
        ])
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function districtCandidates(string $district): array
    {
        $district = $this->normalizeJneRegionName($district);

        if ($district === '') {
            return [];
        }

        /**
         * Support input lama dari Lion:
         * "KRONJO, TIGARAKSA"
         * Maka yang dipakai juga "KRONJO".
         */
        $beforeComma = $this->normalizeJneRegionName(Str::before($district, ','));

        return collect([
            $district,
            $beforeComma,
        ])
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function removeCityPrefix(string $city): string
    {
        $city = $this->normalizeJneRegionName($city);

        if ($city === '') {
            return '';
        }

        $city = preg_replace('/^(KOTA|KABUPATEN|KAB\.|KOTA ADM\.|KABUPATEN ADM\.)\s+/u', '', $city);

        return is_string($city) ? trim($city) : '';
    }
}
