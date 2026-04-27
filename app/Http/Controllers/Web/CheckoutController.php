<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\MidtransTokenRequest;
use App\Http\Requests\Checkout\SaldoPayRequest;
use App\Models\Customer;
use App\Models\Payment;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Tarif ongkir Lion Parcel untuk tujuan yang dipilih.
     * Parameter `district` adalah kode district_lion dari Lion Parcel.
     */
    public function shippingCost(Request $request): JsonResponse
    {
        $request->validate([
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
        ]);
        $province = trim((string) $request->input('province'));
        $city = trim((string) $request->input('city'));
        $districtLion = trim((string) $request->input('district', ''));
        $districtLion = $districtLion !== '' ? $districtLion : null;

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        // Jika district_lion tidak diberikan, coba fallback lookup berdasarkan province + city
        $destinationLion = $districtLion ?? $this->shippingRepository->findDistrictLion($province, $city);

        if (! $destinationLion) {
            return response()->json(['message' => 'Tujuan pengiriman tidak tersedia.'], 422);
        }

        $rates = $this->checkoutService->calculateShippingRates($customer, $destinationLion);

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
}
