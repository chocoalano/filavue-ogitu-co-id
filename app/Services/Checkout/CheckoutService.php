<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\JneDestination;
use App\Models\JneOrigin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use App\Services\Jne\JneShippingException;
use App\Services\Jne\JneShippingService;
use App\Support\Media\PublicMediaUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CheckoutRepositoryInterface $checkoutRepository,
        protected JneShippingService $jneShippingService,
    ) {}

    /**
     * Data halaman checkout: items, totals, addresses, saldo, midtrans config.
     *
     * @return array{items: list<array>, cart: array<string,float>|null, addresses: list<array>, pickup: array<string,mixed>|null, saldo: float, midtrans: array{env: string, client_key: string}}
     */
    public function getPageData(Customer $customer): array
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);
        $addresses = $this->checkoutRepository->getCustomerAddresses($customer->id);

        return [
            'items' => $cart ? $this->formatItems($cart) : [],
            'cart' => $cart ? $this->formatCart($cart) : null,
            'addresses' => $this->formatAddresses($addresses),
            'pickup' => $this->resolvePickupLocation(),
            'saldo' => (float) ($customer->ewallet_saldo ?? 0),
            'midtrans' => [
                'env' => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
        ];
    }

    /**
     * Hitung tarif pengiriman JNE berdasarkan cart ecommerce.
     *
     * Parameter $destinationDistrictLion tetap dipertahankan agar frontend lama tidak rusak.
     * Sekarang nilainya bisa berupa:
     * - tariff_code JNE langsung, contoh: BDO10000
     * - nama kecamatan, contoh: COBLONG
     * - format lama Lion, contoh: COBLONG, BANDUNG
     *
     * @return list<array{product: string, total_tariff: int, estimasi_sla: string}>
     */
    public function calculateShippingRates(Customer $customer, string $destinationDistrictLion): array
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);

        if (! $cart || $cart->items->isEmpty()) {
            return [];
        }

        $originCode = $this->resolveJneOriginCode();
        $destinationCode = $this->resolveJneDestinationCode($customer, $destinationDistrictLion);
        $items = $this->buildJneCartItems($cart);

        try {
            $result = $this->jneShippingService->checkTariffForCart(
                originCode: $originCode,
                destinationCode: $destinationCode,
                items: $items,
            );

            return $this->formatJneRates($result);
        } catch (JneShippingException $exception) {
            Log::error('Failed to calculate JNE shipping rates.', [
                'customer_id' => $customer->id,
                'origin_code' => $originCode,
                'destination_code' => $destinationCode,
                'destination_input' => $destinationDistrictLion,
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            throw ValidationException::withMessages([
                'shipping' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unexpected error while calculating JNE shipping rates.', [
                'customer_id' => $customer->id,
                'origin_code' => $originCode,
                'destination_code' => $destinationCode,
                'destination_input' => $destinationDistrictLion,
                'error' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'shipping' => 'Gagal menghitung ongkir JNE. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Buat order + payment, potong saldo ewallet customer.
     *
     * @param  array<string, mixed>  $addressData
     *
     * @throws ValidationException
     */
    public function payWithSaldo(Customer $customer, array $addressData): Order
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);
        $this->assertCartNotEmpty($cart);

        $orderType = (string) ($addressData['order_type'] ?? 'planA');
        $shippingAmount = $this->resolveShippingAmount($addressData);
        $total = (float) $cart->subtotal_amount
            + $shippingAmount
            + (float) $cart->tax_amount
            - (float) $cart->discount_amount;

        if ((float) ($customer->ewallet_saldo ?? 0) < $total) {
            throw ValidationException::withMessages([
                'payment' => 'Saldo ewallet tidak mencukupi untuk membayar total pesanan.',
            ]);
        }

        $isFirstPurchase = (int) ($customer->status ?? 1) === 1
            && ! Order::query()
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['processing', 'completed'])
                ->exists();

        $order = DB::transaction(function () use ($customer, $cart, $addressData, $total, $shippingAmount, $orderType, $isFirstPurchase): Order {
            $shippingAddressId = $this->resolveShippingAddressId($customer, $addressData);
            $order = $this->buildOrder(
                $customer,
                $cart,
                $shippingAddressId,
                'processing',
                $shippingAmount,
                $orderType,
            );

            $order->update(['paid_at' => now()]);

            Payment::create([
                'order_id' => $order->id,
                'method_id' => PaymentMethod::where('code', 'midtrans')->value('id'),
                'status' => 'paid',
                'amount' => $total,
                'currency' => $cart->currency,
            ]);

            $customer->decrement('ewallet_saldo', $total);

            if ($isFirstPurchase) {
                $customer->update(['status' => 2]);
            }

            $this->checkoutRepository->clearCart($cart);

            return $order;
        });

        $this->syncOrderRetailAndStockistAmounts($order);
        $this->runBonusEngineForOrder($order, (int) ($customer->status ?? 0));

        return $order;
    }

    /**
     * Buat order pending + payment pending, siapkan data untuk Midtrans Snap.
     *
     * @param  array<string, mixed>  $addressData
     * @return array{order: Order, cart: Cart}
     *
     * @throws ValidationException
     */
    public function prepareMidtransOrder(Customer $customer, array $addressData): array
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);
        $this->assertCartNotEmpty($cart);

        $orderType = (string) ($addressData['order_type'] ?? 'planA');
        $shippingAmount = $this->resolveShippingAmount($addressData);

        $result = DB::transaction(function () use ($customer, $cart, $addressData, $shippingAmount, $orderType): array {
            $shippingAddressId = $this->resolveShippingAddressId($customer, $addressData);
            $order = $this->buildOrder(
                $customer,
                $cart,
                $shippingAddressId,
                'pending',
                $shippingAmount,
                $orderType,
            );

            $grandTotal = (float) $cart->subtotal_amount
                + $shippingAmount
                + (float) $cart->tax_amount
                - (float) $cart->discount_amount;

            Payment::create([
                'order_id' => $order->id,
                'method_id' => PaymentMethod::where('code', 'midtrans')->value('id'),
                'status' => 'pending',
                'amount' => $grandTotal,
                'currency' => $cart->currency,
            ]);

            return ['order' => $order, 'cart' => $cart];
        });

        $this->syncOrderRetailAndStockistAmounts($result['order']);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function assertCartNotEmpty(?Cart $cart): void
    {
        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang belanja kosong.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $addressData
     */
    private function resolveShippingAddressId(Customer $customer, array $addressData): int
    {
        $addressMode = (string) ($addressData['address_mode'] ?? 'saved');

        if ($addressMode === 'saved') {
            $addressId = (int) $addressData['address_id'];

            $isOwnedByCustomer = CustomerAddress::query()
                ->whereKey($addressId)
                ->where('customer_id', $customer->id)
                ->exists();

            if (! $isOwnedByCustomer) {
                throw ValidationException::withMessages([
                    'address_id' => 'Alamat tidak valid untuk customer saat ini.',
                ]);
            }

            return $addressId;
        }

        if ($addressMode === 'pickup') {
            $pickupLocation = $this->resolvePickupLocation();

            if (! $pickupLocation) {
                throw ValidationException::withMessages([
                    'address_mode' => 'Alamat pickup kantor belum dikonfigurasi.',
                ]);
            }

            $provinceId = (int) ($pickupLocation['province_id'] ?? $customer->province_id ?? 0);
            $cityId = (int) ($pickupLocation['city_id'] ?? $customer->city_id ?? 0);
            $recipientPhone = trim((string) ($pickupLocation['phone'] ?? $customer->phone ?? ''));

            $address = CustomerAddress::create([
                'customer_id' => $customer->id,
                'label' => (string) ($pickupLocation['label'] ?? 'Pickup Kantor'),
                'recipient_name' => (string) ($pickupLocation['recipient_name'] ?? config('app.name')),
                'recipient_phone' => $recipientPhone !== '' ? $recipientPhone : '-',
                'address_line1' => (string) ($pickupLocation['address_line'] ?? ''),
                'province_label' => (string) ($pickupLocation['province'] ?? ''),
                'province_id' => $provinceId,
                'city_label' => (string) ($pickupLocation['city'] ?? ''),
                'city_id' => $cityId,
                'district' => (string) ($pickupLocation['district'] ?? ''),
                'district_lion' => (string) ($pickupLocation['district'] ?? ''),
                'postal_code' => (string) ($pickupLocation['postal_code'] ?? ''),
                'description' => 'Self pickup ke kantor.',
                'country' => 'Indonesia',
            ]);

            return (int) $address->id;
        }

        $provinceId = (int) ($addressData['province_id'] ?? $customer->province_id ?? 0);
        $cityId = (int) ($addressData['city_id'] ?? $customer->city_id ?? 0);

        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'label' => 'Checkout',
            'recipient_name' => $addressData['recipient_name'],
            'recipient_phone' => $addressData['phone'],
            'address_line1' => $addressData['address_line'],
            'province_label' => $addressData['province'],
            'province_id' => $provinceId,
            'city_label' => $addressData['city'],
            'city_id' => $cityId,
            'district' => $addressData['district'] ?? null,
            'district_lion' => $addressData['district_lion'] ?? $addressData['district'] ?? null,
            'postal_code' => $addressData['postal_code'] ?? null,
            'description' => $addressData['notes'] ?? null,
            'country' => 'Indonesia',
        ]);

        return $address->id;
    }

    /**
     * @param  array<string, mixed>  $addressData
     */
    private function resolveShippingAmount(array $addressData): float
    {
        if (($addressData['address_mode'] ?? null) === 'pickup') {
            return 0.0;
        }

        return (float) ($addressData['shipping_cost'] ?? 0);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolvePickupLocation(): ?array
    {
        $settings = Setting::query()
            ->whereIn('key', [
                'store.name',
                'store.phone',
                'shipping.origin_code',
                'shipping.origin_tariff_code',
                'shipping.origin_province_id',
                'shipping.origin_province_label',
                'shipping.origin_city_id',
                'shipping.origin_city_label',
                'shipping.origin_district_label',
                'shipping.origin_postal_code',
                'shipping.origin_address',
                'address.line1',
                'address.line2',
                'address.city',
                'address.province',
                'address.postal_code',
            ])
            ->pluck('value', 'key')
            ->all();

        $originAddress = trim((string) ($settings['shipping.origin_address'] ?? ''));
        $fallbackAddress = collect([
            $settings['address.line1'] ?? null,
            $settings['address.line2'] ?? null,
        ])
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->implode(', ');

        $addressLine = $originAddress !== '' ? $originAddress : $fallbackAddress;
        $district = trim((string) ($settings['shipping.origin_district_label'] ?? ''));
        $city = trim((string) ($settings['shipping.origin_city_label'] ?? $settings['address.city'] ?? ''));
        $province = trim((string) ($settings['shipping.origin_province_label'] ?? $settings['address.province'] ?? ''));
        $postalCode = trim((string) ($settings['shipping.origin_postal_code'] ?? $settings['address.postal_code'] ?? ''));

        if ($addressLine === '' && $city === '' && $province === '') {
            return null;
        }

        $provinceId = is_numeric($settings['shipping.origin_province_id'] ?? null)
            ? (int) $settings['shipping.origin_province_id']
            : null;
        $cityId = is_numeric($settings['shipping.origin_city_id'] ?? null)
            ? (int) $settings['shipping.origin_city_id']
            : null;
        $storeName = trim((string) ($settings['store.name'] ?? config('app.name')));
        $storePhone = trim((string) ($settings['store.phone'] ?? ''));

        return [
            'label' => 'Pickup Kantor',
            'recipient_name' => $storeName !== '' ? $storeName : config('app.name'),
            'phone' => $storePhone !== '' ? $storePhone : null,
            'address_line' => $addressLine,
            'district' => $district !== '' ? $district : null,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode !== '' ? $postalCode : null,
            'province_id' => $provinceId,
            'city_id' => $cityId,
            'origin_code' => $this->firstFilledString([
                $settings['shipping.origin_code'] ?? null,
                $settings['shipping.origin_tariff_code'] ?? null,
            ]),
        ];
    }

    private function buildOrder(
        Customer $customer,
        Cart $cart,
        int $shippingAddressId,
        string $status,
        float $shippingAmount = 0.0,
        string $orderType = 'planA',
    ): Order {
        $order = Order::create(
            $this->buildOrderPayload(
                $customer,
                $cart,
                $shippingAddressId,
                $status,
                $shippingAmount,
                $orderType,
            )
        );

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'row_total' => $item->row_total,
                'weight_gram' => $item->product?->weight_gram,
                'length_mm' => $item->product?->length_mm,
                'width_mm' => $item->product?->width_mm,
                'height_mm' => $item->product?->height_mm,
            ]);
        }

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrderPayload(
        Customer $customer,
        Cart $cart,
        int $shippingAddressId,
        string $status,
        float $shippingAmount = 0.0,
        string $orderType = 'planA',
    ): array {
        $normalizedOrderType = in_array($orderType, ['planA', 'planB'], true) ? $orderType : 'planA';
        $bonusAmounts = $this->calculateCartBonusAmounts($cart);

        $grandTotal = (float) $cart->subtotal_amount
            + $shippingAmount
            + (float) $cart->tax_amount
            - (float) $cart->discount_amount;

        return [
            'order_no' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'currency' => $cart->currency,
            'status' => $status,
            'type' => $normalizedOrderType,
            'subtotal_amount' => $cart->subtotal_amount,
            'discount_amount' => $cart->discount_amount,
            'shipping_amount' => $shippingAmount,
            'tax_amount' => $cart->tax_amount,
            'grand_total' => $grandTotal,
            'bv_amount' => $bonusAmounts['pv'],
            'sponsor_amount' => $bonusAmounts['sponsor'],
            'match_amount' => $bonusAmounts['match'],
            'pairing_amount' => $bonusAmounts['pairing'],
            'cashback_amount' => $bonusAmounts['cashback'],
            'shipping_address_id' => $shippingAddressId,
            'placed_at' => now(),
        ];
    }

    /**
     * @return array{pv: float, sponsor: float, match: float, pairing: float, cashback: float}
     */
    protected function calculateCartBonusAmounts(Cart $cart): array
    {
        $totals = [
            'pv' => 0.0,
            'sponsor' => 0.0,
            'match' => 0.0,
            'pairing' => 0.0,
            'cashback' => 0.0,
        ];

        foreach ($cart->items as $item) {
            $product = $item->product;
            $quantity = (int) $item->qty;

            $totals['pv'] += ((float) ($product?->pv ?? 0)) * $quantity;
            $totals['sponsor'] += ((float) ($product?->b_sponsor ?? 0)) * $quantity;
            $totals['cashback'] += ((float) ($product?->b_cashback ?? 0)) * $quantity;
        }

        return $totals;
    }

    private function syncOrderRetailAndStockistAmounts(Order $order): void
    {
        try {
            DB::statement('CALL sp_accumulation_stockist_retail_amount_orders(?)', [$order->id]);
        } catch (\Throwable $exception) {
            Log::error('Failed to sync stockist and retail accumulation for order.', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function runBonusEngineForOrder(Order $order, int $customerStatus): void
    {
        if ($customerStatus !== 3) {
            return;
        }

        if ((bool) ($order->bonus_generated ?? false)) {
            return;
        }

        try {
            $this->checkoutRepository->callBonusEngine((int) $order->id);
            $this->checkoutRepository->markOrderBonusGenerated($order);
        } catch (\Throwable $exception) {
            Log::error('Failed to run bonus engine for ewallet checkout.', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Build cart items untuk JneShippingService::checkTariffForCart().
     *
     * @return list<array<string, mixed>>
     */
    private function buildJneCartItems(Cart $cart): array
    {
        return $cart->items
            ->map(function (CartItem $item): array {
                return [
                    'name' => $item->product_name,
                    'qty' => max(1, (int) $item->qty),
                    'weight_gram' => (int) ($item->product?->weight_gram ?? 200),
                    'length_cm' => $this->millimeterToCentimeter($item->product?->length_mm, 100),
                    'width_cm' => $this->millimeterToCentimeter($item->product?->width_mm, 100),
                    'height_cm' => $this->millimeterToCentimeter($item->product?->height_mm, 100),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Format response JNE supaya kompatibel dengan frontend.
     *
     * Output lama:
     * [
     *     [
     *         'product' => 'REG',
     *         'total_tariff' => 12000,
     *         'estimasi_sla' => '2-3 HARI',
     *     ]
     * ]
     *
     * @param  array<string, mixed>  $result
     * @return list<array<string, mixed>>
     */
    private function formatJneRates(array $result): array
    {
        $services = $result['services'] ?? [];

        if (! is_array($services)) {
            return [];
        }

        return collect($services)
            ->map(function (array $service): ?array {
                $code = $this->toUppercaseLabel($service['code'] ?? $service['name'] ?? null);
                $price = (int) ($service['price'] ?? 0);

                if ($code === null || $price <= 0) {
                    return null;
                }

                return [
                    'product' => $code,
                    'service_code' => $code,
                    'service_name' => (string) ($service['name'] ?? $code),
                    'description' => (string) ($service['description'] ?? $service['name'] ?? $code),
                    'total_tariff' => $price,
                    'estimasi_sla' => (string) ($service['etd'] ?? '-'),
                    'currency' => (string) ($service['currency'] ?? 'IDR'),
                    'raw' => $service['raw'] ?? $service,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveJneOriginCode(): string
    {
        $settings = Setting::query()
            ->whereIn('key', [
                'shipping.origin_code',
                'shipping.origin_tariff_code',
                'shipping.origin_city_id',
                'shipping.origin_city_label',
                'shipping.origin_district_label',
                'address.city',
            ])
            ->pluck('value', 'key')
            ->all();

        $directCode = $this->firstFilledString([
            $settings['shipping.origin_code'] ?? null,
            $settings['shipping.origin_tariff_code'] ?? null,
            config('jne.default_origin_code'),
            env('JNE_DEFAULT_ORIGIN_CODE'),
        ]);

        if ($directCode !== null) {
            return $this->normalizeJneCode($directCode);
        }

        $originCityId = $this->normalizeIntegerId($settings['shipping.origin_city_id'] ?? null);

        if ($originCityId !== null) {
            $originCityDestination = JneDestination::query()->find($originCityId);

            if ($originCityDestination && filled($originCityDestination->city_name)) {
                $originCode = $this->findJneOriginCodeByName((string) $originCityDestination->city_name);

                if ($originCode !== null) {
                    return $originCode;
                }
            }
        }

        $cityLabel = $this->firstFilledString([
            $settings['shipping.origin_city_label'] ?? null,
            $settings['address.city'] ?? null,
        ]);

        if ($cityLabel !== null) {
            $originCode = $this->findJneOriginCodeByName($cityLabel);

            if ($originCode !== null) {
                return $originCode;
            }
        }

        throw ValidationException::withMessages([
            'shipping' => 'Origin JNE belum dikonfigurasi. Isi shipping.origin_code / JNE_DEFAULT_ORIGIN_CODE atau pastikan data JNE origin tersedia.',
        ]);
    }

    private function resolveJneDestinationCode(Customer $customer, string $destinationDistrictLion): string
    {
        $input = $this->toUppercaseLabel($destinationDistrictLion);

        if ($input === null) {
            throw ValidationException::withMessages([
                'shipping' => 'Tujuan pengiriman JNE wajib diisi.',
            ]);
        }

        $directTariffCode = JneDestination::query()
            ->whereNotNull('tariff_code')
            ->where('tariff_code', '!=', '')
            ->whereRaw('UPPER(TRIM(tariff_code)) = ?', [$input])
            ->value('tariff_code');

        if (filled($directTariffCode)) {
            return $this->normalizeJneCode((string) $directTariffCode);
        }

        $districtCandidates = $this->districtCandidates($input);
        $address = $this->findCustomerAddressForDestination($customer, $districtCandidates);

        if ($address) {
            $tariffCode = $this->findJneTariffCodeFromAddress($address, $districtCandidates);

            if ($tariffCode !== null) {
                return $tariffCode;
            }
        }

        $fallbackTariffCode = $this->findJneTariffCodeGlobally($districtCandidates);

        if ($fallbackTariffCode !== null) {
            return $fallbackTariffCode;
        }

        throw ValidationException::withMessages([
            'shipping' => 'Kode tujuan JNE tidak ditemukan. Pastikan alamat customer memiliki kecamatan yang sesuai dengan data jne_destinations.tariff_code.',
        ]);
    }

    /**
     * @param  list<string>  $districtCandidates
     */
    private function findCustomerAddressForDestination(Customer $customer, array $districtCandidates): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('customer_id', $customer->id)
            ->where(function ($query) use ($districtCandidates): void {
                foreach ($districtCandidates as $district) {
                    $query
                        ->orWhereRaw('UPPER(TRIM(district)) = ?', [$district])
                        ->orWhereRaw('UPPER(TRIM(district_lion)) = ?', [$district]);
                }
            })
            ->latest('is_default')
            ->latest('id')
            ->first();
    }

    /**
     * @param  list<string>  $districtCandidates
     */
    private function findJneTariffCodeFromAddress(CustomerAddress $address, array $districtCandidates): ?string
    {
        $cityDestination = null;

        if ($address->city_id) {
            $cityDestination = JneDestination::query()->find((int) $address->city_id);
        }

        $provinceName = $cityDestination?->province_name ?: $address->province_label;
        $cityName = $cityDestination?->city_name ?: $address->city_label;

        if (blank($provinceName) || blank($cityName)) {
            return null;
        }

        $query = JneDestination::query()
            ->whereNotNull('tariff_code')
            ->where('tariff_code', '!=', '')
            ->whereRaw('UPPER(TRIM(province_name)) = ?', [$this->normalizeRegionName($provinceName)])
            ->whereRaw('UPPER(TRIM(city_name)) = ?', [$this->normalizeRegionName($cityName)]);

        $query->where(function ($query) use ($districtCandidates): void {
            foreach ($districtCandidates as $district) {
                $query
                    ->orWhereRaw('UPPER(TRIM(district_name)) = ?', [$district])
                    ->orWhereRaw('UPPER(TRIM(subdistrict_name)) = ?', [$district]);
            }
        });

        $tariffCode = $query
            ->orderBy('id')
            ->value('tariff_code');

        return filled($tariffCode) ? $this->normalizeJneCode((string) $tariffCode) : null;
    }

    /**
     * @param  list<string>  $districtCandidates
     */
    private function findJneTariffCodeGlobally(array $districtCandidates): ?string
    {
        $query = JneDestination::query()
            ->whereNotNull('tariff_code')
            ->where('tariff_code', '!=', '');

        $query->where(function ($query) use ($districtCandidates): void {
            foreach ($districtCandidates as $district) {
                $query
                    ->orWhereRaw('UPPER(TRIM(district_name)) = ?', [$district])
                    ->orWhereRaw('UPPER(TRIM(subdistrict_name)) = ?', [$district])
                    ->orWhereRaw('UPPER(TRIM(city_name)) = ?', [$district]);
            }
        });

        $tariffCode = $query
            ->orderBy('id')
            ->value('tariff_code');

        return filled($tariffCode) ? $this->normalizeJneCode((string) $tariffCode) : null;
    }

    private function findJneOriginCodeByName(string $originName): ?string
    {
        $normalized = $this->normalizeRegionName($originName);
        $withoutPrefix = $this->removeCityPrefix($normalized);

        if ($normalized === '') {
            return null;
        }

        $query = JneOrigin::query()
            ->whereNotNull('origin_code')
            ->where('origin_code', '!=', '');

        $query->where(function ($query) use ($normalized, $withoutPrefix): void {
            $query
                ->whereRaw('UPPER(TRIM(origin_name)) = ?', [$normalized])
                ->orWhereRaw('UPPER(TRIM(origin_name)) = ?', [$withoutPrefix])
                ->orWhereRaw('UPPER(TRIM(origin_code)) = ?', [$normalized]);

            if ($withoutPrefix !== '') {
                $query->orWhereRaw('UPPER(TRIM(origin_name)) LIKE ?', ['%'.$withoutPrefix.'%']);
            }
        });

        $originCode = $query
            ->orderBy('id')
            ->value('origin_code');

        return filled($originCode) ? $this->normalizeJneCode((string) $originCode) : null;
    }

    /**
     * @return list<string>
     */
    private function districtCandidates(string $district): array
    {
        $district = $this->normalizeRegionName($district);

        if ($district === '') {
            return [];
        }

        $beforeComma = trim(Str::before($district, ','));

        return collect([
            $district,
            $beforeComma,
        ])
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function millimeterToCentimeter(mixed $millimeter, int $fallbackMillimeter = 100): float
    {
        $value = is_numeric($millimeter) ? (float) $millimeter : (float) $fallbackMillimeter;

        return max(1.0, $value / 10);
    }

    /** @return list<array<string, mixed>> */
    private function formatItems(Cart $cart): array
    {
        return $cart->items->map(function (CartItem $item): array {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'variant' => $item->meta_json['variant'] ?? null,
                'price' => (float) $item->unit_price,
                'qty' => $item->qty,
                'row_total' => (float) $item->row_total,
                'image' => PublicMediaUrl::resolve($item->product?->primaryMedia->first()?->url),
                'weight_gram' => $item->product?->weight_gram,
            ];
        })->toArray();
    }

    /** @return array<string, float> */
    private function formatCart(Cart $cart): array
    {
        return [
            'subtotal' => (float) $cart->subtotal_amount,
            'discount' => (float) $cart->discount_amount,
            'shipping' => (float) $cart->shipping_amount,
            'tax' => (float) $cart->tax_amount,
            'total' => (float) $cart->grand_total,
        ];
    }

    /**
     * @param  Collection<int, CustomerAddress>  $addresses
     * @return list<array<string, mixed>>
     */
    private function formatAddresses(Collection $addresses): array
    {
        return $addresses->map(fn (CustomerAddress $a): array => [
            'id' => $a->id,
            'label' => $a->label,
            'recipient_name' => $a->recipient_name,
            'phone' => $a->recipient_phone,
            'address_line' => $a->address_line1,
            'address_line2' => $a->address_line2,
            'province' => $a->province_label,
            'province_id' => $a->province_id,
            'city' => $a->city_label,
            'city_id' => $a->city_id,
            'district' => $a->district,
            'district_lion' => $a->district_lion,
            'postal_code' => $a->postal_code,
            'description' => $a->description,
            'is_default' => $a->is_default,
        ])->toArray();
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function firstFilledString(array $values): ?string
    {
        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
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

    private function normalizeJneCode(string $code): string
    {
        return Str::upper(trim($code));
    }

    private function normalizeRegionName(mixed $value): string
    {
        if (blank($value) || ! is_scalar($value)) {
            return '';
        }

        $normalized = Str::upper(trim((string) $value));
        $normalized = preg_replace('/\s+/u', ' ', $normalized);

        return is_string($normalized) ? trim($normalized) : '';
    }

    private function toUppercaseLabel(mixed $label): ?string
    {
        $normalized = $this->normalizeRegionName($label);

        return $normalized !== '' ? $normalized : null;
    }

    private function removeCityPrefix(string $city): string
    {
        $city = $this->normalizeRegionName($city);

        if ($city === '') {
            return '';
        }

        $city = preg_replace('/^(KOTA|KABUPATEN|KAB\.|KOTA ADM\.|KABUPATEN ADM\.)\s+/u', '', $city);

        return is_string($city) ? trim($city) : '';
    }
}
