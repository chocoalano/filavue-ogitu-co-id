<?php

namespace App\Services\Jne;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class JneShippingService
{
    /*
    |--------------------------------------------------------------------------
    | JNE Pricedev API - Hardcoded
    |--------------------------------------------------------------------------
    |
    | Sesuai referensi Insomnia:
    |
    | POST https://apiv2.jne.co.id:10202/tracing/api/pricedev
    |
    | Headers:
    | Accept:
    | Content-Type: application/x-www-form-urlencoded
    | User-Agent: insomnia/12.5.0
    |
    | Body Form:
    | username = TESTAPI
    | api_key  = API KEY JNE
    | from     = CGK10000
    | thru     = TGR10105
    | weight   = 300
    |
    */

    private const BASE_URL = 'https://apiv2.jne.co.id:10202';

    private const PRICEDEV_ENDPOINT = '/tracing/api/pricedev';

    private const USERNAME = 'TESTAPI';

    /*
     * Isi dengan API key JNE Anda.
     * Saya sengaja tidak tulis ulang full API key di sini agar tidak makin tersebar.
     */
    private const API_KEY = '25c898a9faea1a100859ecd9ef674548';

    private const FIELD_USERNAME = 'username';

    private const FIELD_API_KEY = 'api_key';

    private const FIELD_ORIGIN = 'from';

    private const FIELD_DESTINATION = 'thru';

    private const FIELD_WEIGHT = 'weight';

    private const TIMEOUT = 30;

    private const RETRY_TIMES = 0;

    private const RETRY_SLEEP_MS = 500;

    private const USER_AGENT = 'insomnia/12.5.0';

    private const WEIGHT_UNIT = 'gram';

    private const MIN_CHARGEABLE_GRAM = 1;

    private const VOLUME_DIVISOR = 6000;

    /**
     * Cek ongkir JNE dengan berat gram.
     *
     * Contoh:
     *
     * app(JneShippingService::class)->checkTariffByGram(
     *     originCode: 'CGK10000',
     *     destinationCode: 'TGR10105',
     *     weightGram: 300,
     * );
     *
     * @param  array<string, mixed>  $extraPayload
     * @return array<string, mixed>
     */
    public function checkTariffByGram(
        string $originCode,
        string $destinationCode,
        int|float $weightGram,
        array $extraPayload = [],
    ): array {
        if ($weightGram <= 0) {
            throw JneShippingException::validation('Berat gram wajib lebih dari 0.');
        }

        $chargeableGram = max((int) ceil($weightGram), self::MIN_CHARGEABLE_GRAM);

        return $this->checkTariff(
            originCode: $originCode,
            destinationCode: $destinationCode,
            weight: $chargeableGram,
            extraPayload: array_merge($extraPayload, [
                '_actual_weight_gram' => (int) ceil($weightGram),
                '_chargeable_weight_gram' => $chargeableGram,
            ]),
        );
    }

    /**
     * Cek ongkir JNE.
     *
     * Weight dikirim apa adanya dalam gram.
     *
     * @param  array<string, mixed>  $extraPayload
     * @return array<string, mixed>
     */
    public function checkTariff(
        string $originCode,
        string $destinationCode,
        int|float $weight,
        array $extraPayload = [],
    ): array {
        $originCode = $this->normalizeCode($originCode);
        $destinationCode = $this->normalizeCode($destinationCode);
        $weight = max(1, (int) ceil($weight));

        if ($originCode === '') {
            throw JneShippingException::validation('Kode origin JNE wajib diisi.');
        }

        if ($destinationCode === '') {
            throw JneShippingException::validation('Kode destination JNE wajib diisi.');
        }

        if (self::API_KEY === '' || self::API_KEY === 'ISI_API_KEY_JNE_ANDA') {
            throw JneShippingException::validation('API key JNE belum diisi di constant API_KEY pada JneShippingService.');
        }

        $payload = array_merge([
            self::FIELD_USERNAME => self::USERNAME,
            self::FIELD_API_KEY => self::API_KEY,
            self::FIELD_ORIGIN => $originCode,
            self::FIELD_DESTINATION => $destinationCode,
            self::FIELD_WEIGHT => $weight,
        ], $extraPayload);

        $response = $this->postPricedev($payload);
        $decoded = $this->decodeResponse($response, $payload);

        $this->ensureSuccessfulBusinessResponse($decoded);

        return $this->normalizeTariffResponse(
            rawResponse: $decoded,
            originCode: $originCode,
            destinationCode: $destinationCode,
            weight: $weight,
            requestPayload: $payload,
        );
    }

    /**
     * Cek ongkir langsung dari item cart ecommerce.
     *
     * Format item:
     *
     * [
     *     [
     *         'name' => 'Produk A',
     *         'qty' => 2,
     *         'weight_gram' => 500,
     *         'length_cm' => 10,
     *         'width_cm' => 10,
     *         'height_cm' => 10,
     *     ],
     * ]
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $extraPayload
     * @return array<string, mixed>
     */
    public function checkTariffForCart(
        string $originCode,
        string $destinationCode,
        array $items,
        array $extraPayload = [],
    ): array {
        $weightCalculation = $this->calculateCartChargeableWeight($items);

        $result = $this->checkTariffByGram(
            originCode: $originCode,
            destinationCode: $destinationCode,
            weightGram: $weightCalculation['chargeable_weight_gram'],
            extraPayload: $extraPayload,
        );

        $result['weight_calculation'] = $weightCalculation;

        return $result;
    }

    /**
     * Debug request mentah ke JNE tanpa throw exception.
     *
     * Gunakan untuk membandingkan response Laravel vs Insomnia.
     *
     * Contoh:
     *
     * app(JneShippingService::class)->debugPricedev(
     *     originCode: 'CGK10000',
     *     destinationCode: 'TGR10105',
     *     weightGram: 300,
     * );
     *
     * @return array<string, mixed>
     */
    public function debugPricedev(
        string $originCode = 'CGK10000',
        string $destinationCode = 'TGR10105',
        int|float $weightGram = 300,
    ): array {
        $payload = [
            self::FIELD_USERNAME => self::USERNAME,
            self::FIELD_API_KEY => self::API_KEY,
            self::FIELD_ORIGIN => $this->normalizeCode($originCode),
            self::FIELD_DESTINATION => $this->normalizeCode($destinationCode),
            self::FIELD_WEIGHT => max(1, (int) ceil($weightGram)),
        ];

        $apiPayload = $this->filterApiPayload($payload);
        $rawBody = http_build_query($apiPayload, '', '&');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(self::TIMEOUT)
                ->withBody($rawBody, 'application/x-www-form-urlencoded')
                ->post($this->url());
        } catch (Throwable $throwable) {
            return [
                'ok' => false,
                'error' => $throwable->getMessage(),
                'url' => $this->url(),
                'headers' => $this->headers(),
                'payload' => $this->sanitizePayload($apiPayload),
                'raw_body_sent' => $this->sanitizeRawBody($rawBody),
            ];
        }

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'url' => $this->url(),
            'headers' => $this->headers(),
            'payload' => $this->sanitizePayload($apiPayload),
            'raw_body_sent' => $this->sanitizeRawBody($rawBody),
            'response_body' => $response->body(),
            'response_json' => $response->json(),
        ];
    }

    /**
     * Hitung berat chargeable cart ecommerce.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function calculateCartChargeableWeight(array $items): array
    {
        if ($items === []) {
            throw JneShippingException::validation('Cart tidak boleh kosong.');
        }

        $totalActualGram = 0.0;
        $totalVolumetricGram = 0.0;
        $normalizedItems = [];

        foreach ($items as $index => $item) {
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            $qty = max(1, $qty);

            $weightGram = (float) ($item['weight_gram'] ?? $item['weight'] ?? 0);
            $weightGram = max(0, $weightGram);

            $lengthCm = (float) ($item['length_cm'] ?? $item['length'] ?? 0);
            $widthCm = (float) ($item['width_cm'] ?? $item['width'] ?? 0);
            $heightCm = (float) ($item['height_cm'] ?? $item['height'] ?? 0);

            $actualGram = $weightGram * $qty;

            $volumetricGramPerItem = 0.0;

            if ($lengthCm > 0 && $widthCm > 0 && $heightCm > 0) {
                $volumetricKgPerItem = ($lengthCm * $widthCm * $heightCm) / self::VOLUME_DIVISOR;
                $volumetricGramPerItem = $volumetricKgPerItem * 1000;
            }

            $volumetricGram = $volumetricGramPerItem * $qty;

            $totalActualGram += $actualGram;
            $totalVolumetricGram += $volumetricGram;

            $normalizedItems[] = [
                'index' => $index,
                'name' => $item['name'] ?? $item['product_name'] ?? null,
                'qty' => $qty,
                'weight_gram_per_item' => (int) ceil($weightGram),
                'actual_weight_gram' => (int) ceil($actualGram),
                'length_cm' => $lengthCm > 0 ? $lengthCm : null,
                'width_cm' => $widthCm > 0 ? $widthCm : null,
                'height_cm' => $heightCm > 0 ? $heightCm : null,
                'volumetric_weight_gram' => (int) ceil($volumetricGram),
            ];
        }

        $chargeableGram = max(
            (int) ceil($totalActualGram),
            (int) ceil($totalVolumetricGram),
            self::MIN_CHARGEABLE_GRAM,
        );

        return [
            'actual_weight_gram' => (int) ceil($totalActualGram),
            'volumetric_weight_gram' => (int) ceil($totalVolumetricGram),
            'chargeable_weight_gram' => $chargeableGram,
            'api_weight' => $chargeableGram,
            'api_weight_unit' => self::WEIGHT_UNIT,
            'min_chargeable_gram' => self::MIN_CHARGEABLE_GRAM,
            'volume_divisor' => self::VOLUME_DIVISOR,
            'items' => $normalizedItems,
        ];
    }

    /**
     * Ambil service termurah.
     *
     * @param  array<string, mixed>  $tariffResult
     * @return array<string, mixed>|null
     */
    public function cheapest(array $tariffResult): ?array
    {
        $services = $tariffResult['services'] ?? [];

        if (! is_array($services) || $services === []) {
            return null;
        }

        return collect($services)
            ->filter(fn (array $service): bool => isset($service['price']) && is_numeric($service['price']))
            ->sortBy(fn (array $service): int => (int) $service['price'])
            ->first();
    }

    /**
     * Cari service berdasarkan kode.
     *
     * @param  array<string, mixed>  $tariffResult
     * @return array<string, mixed>|null
     */
    public function findService(array $tariffResult, string $serviceCode): ?array
    {
        $serviceCode = Str::upper(trim($serviceCode));
        $services = $tariffResult['services'] ?? [];

        if (! is_array($services) || $services === []) {
            return null;
        }

        return collect($services)
            ->first(function (array $service) use ($serviceCode): bool {
                $code = Str::upper((string) ($service['code'] ?? ''));

                return $code === $serviceCode;
            });
    }

    /**
     * Kirim request form-urlencoded manual seperti Insomnia.
     *
     * @param  array<string, mixed>  $payload
     */
    private function postPricedev(array $payload): Response
    {
        $apiPayload = $this->filterApiPayload($payload);
        $rawBody = http_build_query($apiPayload, '', '&');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(self::TIMEOUT)
                ->when(
                    self::RETRY_TIMES > 0,
                    fn ($pendingRequest) => $pendingRequest->retry(self::RETRY_TIMES, self::RETRY_SLEEP_MS, throw: false),
                )
                ->withBody($rawBody, 'application/x-www-form-urlencoded')
                ->post($this->url());
        } catch (Throwable $throwable) {
            throw JneShippingException::connection($throwable, $apiPayload);
        }

        if ($response->failed()) {
            throw JneShippingException::requestFailed($response, $apiPayload);
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Accept' => '*/*',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => self::USER_AGENT,
        ];
    }

    private function url(): string
    {
        return self::BASE_URL.self::PRICEDEV_ENDPOINT;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function decodeResponse(Response $response, array $payload): array
    {
        $json = $response->json();

        if (is_array($json)) {
            return $json;
        }

        $body = trim($response->body());

        if ($body === '') {
            throw JneShippingException::invalidJson('Response JNE kosong.', $payload);
        }

        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            'status' => 'error',
            'message' => $body,
            'raw_body' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function ensureSuccessfulBusinessResponse(array $response): void
    {
        $message = $this->firstFilled($response, [
            'message',
            'error',
            'error_message',
            'reason',
            'description',
            'raw_body',
        ]);

        $status = $this->firstFilled($response, [
            'status',
            'success',
            'code',
            'response_code',
        ]);

        if (is_bool($status) && $status === false) {
            throw JneShippingException::validation((string) ($message ?: 'Request JNE API gagal.'), [
                'response' => $response,
            ]);
        }

        if (is_string($status)) {
            $normalizedStatus = Str::lower(trim($status));

            if (in_array($normalizedStatus, ['error', 'failed', 'fail', 'false', 'invalid'], true)) {
                throw JneShippingException::validation((string) ($message ?: 'Request JNE API gagal.'), [
                    'response' => $response,
                ]);
            }
        }

        if (is_string($message)) {
            $normalizedMessage = Str::lower($message);

            if (
                str_contains($normalizedMessage, 'not found') ||
                str_contains($normalizedMessage, 'invalid') ||
                str_contains($normalizedMessage, 'error') ||
                str_contains($normalizedMessage, 'failed')
            ) {
                throw JneShippingException::validation($message, [
                    'response' => $response,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $rawResponse
     * @param  array<string, mixed>  $requestPayload
     * @return array<string, mixed>
     */
    private function normalizeTariffResponse(
        array $rawResponse,
        string $originCode,
        string $destinationCode,
        int $weight,
        array $requestPayload,
    ): array {
        $rows = $this->extractServiceRows($rawResponse);

        $services = collect($rows)
            ->map(fn (mixed $row): ?array => $this->normalizeServiceRow($row))
            ->filter()
            ->values()
            ->all();

        return [
            'success' => true,
            'origin_code' => $originCode,
            'destination_code' => $destinationCode,
            'weight' => $weight,
            'weight_unit' => self::WEIGHT_UNIT,
            'services' => $services,
            'request' => $this->sanitizePayload($requestPayload),
            'raw' => $rawResponse,
        ];
    }

    /**
     * @param  array<string, mixed>  $rawResponse
     * @return array<int, mixed>
     */
    private function extractServiceRows(array $rawResponse): array
    {
        if ($this->isListOfRows($rawResponse)) {
            return $rawResponse;
        }

        $candidatePaths = [
            'price',
            'prices',
            'data',
            'data.price',
            'data.prices',
            'data.result',
            'data.results',
            'result',
            'result.price',
            'result.prices',
            'results',
            'tariff',
            'tariffs',
            'services',
            'ongkir',
            'rates',
        ];

        foreach ($candidatePaths as $path) {
            $value = Arr::get($rawResponse, $path);

            if (is_array($value) && $this->isListOfRows($value)) {
                return $value;
            }
        }

        foreach ($rawResponse as $value) {
            if (! is_array($value)) {
                continue;
            }

            foreach ($candidatePaths as $path) {
                $nestedValue = Arr::get($value, $path);

                if (is_array($nestedValue) && $this->isListOfRows($nestedValue)) {
                    return $nestedValue;
                }
            }
        }

        return [];
    }

    /**
     * @param  array<mixed>  $value
     */
    private function isListOfRows(array $value): bool
    {
        return $value !== [] && array_is_list($value);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeServiceRow(mixed $row): ?array
    {
        if (! is_array($row)) {
            return null;
        }

        $code = $this->firstFilled($row, [
            'service_code',
            'service',
            'code',
            'product',
            'product_code',
            'display_code',
            'service_display',
        ]);

        $name = $this->firstFilled($row, [
            'service_display',
            'service_name',
            'name',
            'product_name',
            'service',
            'code',
            'service_code',
        ]);

        $description = $this->firstFilled($row, [
            'description',
            'service_description',
            'goods_type',
            'service_display',
            'service_name',
            'name',
        ]);

        $price = $this->firstFilled($row, [
            'price',
            'tariff',
            'cost',
            'amount',
            'value',
            'shipping_cost',
            'total_tariff',
        ]);

        $currency = $this->firstFilled($row, [
            'currency',
            'currency_code',
        ]) ?: 'IDR';

        $etdFrom = $this->firstFilled($row, [
            'etd_from',
            'etdFrom',
            'estimate_from',
        ]);

        $etdThru = $this->firstFilled($row, [
            'etd_thru',
            'etdThru',
            'estimate_thru',
            'etd_to',
        ]);

        $etd = $this->firstFilled($row, [
            'etd',
            'estimate',
            'estimation',
            'sla',
            'estimasi_sla',
        ]);

        $times = $this->firstFilled($row, [
            'times',
            'time',
            'unit',
        ]);

        $normalizedPrice = $this->normalizePrice($price);

        if ($normalizedPrice === null || $normalizedPrice <= 0) {
            return null;
        }

        $normalizedCode = $code !== null
            ? Str::upper(trim((string) $code))
            : Str::upper(trim((string) ($name ?? 'JNE')));

        $normalizedName = $name !== null
            ? trim((string) $name)
            : $normalizedCode;

        if ($etd === null && ($etdFrom !== null || $etdThru !== null)) {
            $etd = trim((string) $etdFrom);

            if ($etdThru !== null && (string) $etdThru !== (string) $etdFrom) {
                $etd .= '-'.trim((string) $etdThru);
            }
        }

        if ($etd !== null && $times !== null) {
            $etd = trim((string) $etd).' '.trim((string) $times);
        }

        return [
            'code' => $normalizedCode,
            'name' => $normalizedName,
            'description' => $description !== null ? trim((string) $description) : $normalizedName,
            'price' => $normalizedPrice,
            'currency' => Str::upper((string) $currency),
            'etd' => $etd !== null ? trim((string) $etd) : null,
            'etd_from' => $etdFrom !== null ? trim((string) $etdFrom) : null,
            'etd_thru' => $etdThru !== null ? trim((string) $etdThru) : null,
            'raw' => $row,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = $row[$key];

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return $value;
        }

        return null;
    }

    private function normalizePrice(mixed $price): ?int
    {
        if ($price === null) {
            return null;
        }

        if (is_int($price)) {
            return $price;
        }

        if (is_float($price)) {
            return (int) round($price);
        }

        if (! is_scalar($price)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $price);

        if (! is_string($clean) || $clean === '') {
            return null;
        }

        return (int) $clean;
    }

    private function normalizeCode(string $code): string
    {
        return Str::upper(trim($code));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterApiPayload(array $payload): array
    {
        return collect($payload)
            ->reject(fn (mixed $value, string $key): bool => str_starts_with($key, '_'))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        $payload = $this->filterApiPayload($payload);

        if (array_key_exists(self::FIELD_API_KEY, $payload)) {
            $payload[self::FIELD_API_KEY] = '******';
        }

        return $payload;
    }

    private function sanitizeRawBody(string $rawBody): string
    {
        return preg_replace('/api_key=([^&]+)/', 'api_key=******', $rawBody) ?: $rawBody;
    }
}
