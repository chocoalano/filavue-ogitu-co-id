<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LionParcelService
{
    private string $baseUrl;

    private string $auth;

    private string $origin;

    private string $commodity;

    public function __construct()
    {
        $this->baseUrl = config('services.lion_parcel.base_url');
        $this->auth = config('services.lion_parcel.auth');
        $this->origin = config('services.lion_parcel.origin');
        $this->commodity = config('services.lion_parcel.commodity');
    }

    /**
     * Ambil tarif pengiriman dari API Lion Parcel.
     *
     * @return list<array{product: string, total_tariff: int, estimasi_sla: string}>
     */
    public function getRates(
        string $destinationDistrictLion,
        float $weightKg,
        int $lengthCm,
        int $widthCm,
        int $heightCm,
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$this->auth,
            ])->get($this->baseUrl.'/v3/tariff', [
                'origin' => $this->origin,
                'destination' => $destinationDistrictLion,
                'weight' => $weightKg,
                'commodity' => $this->commodity,
                'length' => $lengthCm,
                'width' => $widthCm,
                'height' => $heightCm,
            ]);

            if (! $response->successful()) {
                $body = $response->json() ?? [];
                $errorEn = strtolower((string) ($body['message']['en'] ?? $body['message'] ?? ''));

                Log::warning('Lion Parcel API error', [
                    'status' => $response->status(),
                    'destination' => $destinationDistrictLion,
                    'body' => $response->body(),
                ]);

                if (str_contains($errorEn, 'failed to get district')) {
                    throw ValidationException::withMessages([
                        'district' => "Kecamatan \"{$destinationDistrictLion}\" belum terdaftar di layanan Lion Parcel. Silakan pilih kecamatan lain atau hubungi admin.",
                    ]);
                }

                return [];
            }

            return collect($response->json('result', []))
                ->filter(fn (array $r) => ($r['status'] ?? '') === 'ACTIVE' && ! ($r['is_embargo'] ?? false))
                ->map(fn (array $r) => [
                    'product' => $r['product'],
                    'total_tariff' => (int) $r['total_tariff'],
                    'estimasi_sla' => $r['estimasi_sla'],
                ])
                ->values()
                ->toArray();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Lion Parcel API exception', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Buat booking pengiriman Lion Parcel.
     *
     * @param  array<string, mixed>  $sttPayload
     * @return array{
     *   success: bool,
     *   status: int|null,
     *   message: string,
     *   tracking_no: string|null,
     *   body: array<mixed>|null
     * }
     */
    public function createBooking(array $sttPayload): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$this->auth,
                'Accept' => 'application/json',
            ])->post($this->baseUrl.'/client/booking', [
                'stt' => $sttPayload,
            ]);

            $body = $response->json();

            if (! $response->successful()) {
                $message = $this->extractBookingMessage($body)
                    ?? "Booking Lion Parcel gagal dengan status {$response->status()}";

                Log::warning('Lion Parcel booking API error', [
                    'status' => $response->status(),
                    'message' => $message,
                    'request' => $sttPayload,
                    'body' => $body,
                ]);

                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $message,
                    'tracking_no' => null,
                    'body' => is_array($body) ? $body : null,
                ];
            }

            $trackingNumber = $this->extractTrackingNumber($body) ?? $this->extractTrackingNumber($sttPayload);
            $message = $this->extractBookingMessage($body) ?? 'Booking Lion Parcel berhasil dibuat.';

            Log::info('Lion Parcel booking API success', [
                'status' => $response->status(),
                'tracking_no' => $trackingNumber,
                'request_ref' => $sttPayload['stt_no_ref_external'] ?? null,
                'body' => $body,
            ]);

            return [
                'success' => true,
                'status' => $response->status(),
                'message' => $message,
                'tracking_no' => $trackingNumber,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Lion Parcel booking API exception', [
                'message' => $e->getMessage(),
                'request' => $sttPayload,
            ]);

            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
                'tracking_no' => null,
                'body' => null,
            ];
        }
    }

    private function extractBookingMessage(mixed $body): ?string
    {
        if (! is_array($body)) {
            return null;
        }

        foreach (['message', 'messages', 'error', 'description'] as $key) {
            if (isset($body[$key]) && is_string($body[$key]) && filled($body[$key])) {
                return $body[$key];
            }
        }

        if (isset($body['result']) && is_array($body['result'])) {
            foreach (['message', 'description', 'error'] as $key) {
                if (isset($body['result'][$key]) && is_string($body['result'][$key]) && filled($body['result'][$key])) {
                    return $body['result'][$key];
                }
            }
        }

        return null;
    }

    private function extractTrackingNumber(mixed $source): ?string
    {
        if (! is_array($source)) {
            return null;
        }

        if (isset($source['stt_no']) && is_scalar($source['stt_no']) && filled((string) $source['stt_no'])) {
            return (string) $source['stt_no'];
        }

        if (isset($source['tracking_no']) && is_scalar($source['tracking_no']) && filled((string) $source['tracking_no'])) {
            return (string) $source['tracking_no'];
        }

        if (isset($source['result']) && is_array($source['result'])) {
            return $this->extractTrackingNumber($source['result']);
        }

        if (isset($source['data']) && is_array($source['data'])) {
            return $this->extractTrackingNumber($source['data']);
        }

        if (isset($source['stt']) && is_array($source['stt'])) {
            return $this->extractTrackingNumber($source['stt']);
        }

        return null;
    }
}
