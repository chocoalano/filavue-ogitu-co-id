<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerWhatsAppConfirmation;
use App\Services\QontactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QontakIncomingWebhookController extends Controller
{
    public function __construct(
        private readonly QontactService $qontactService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $rawPhone = $this->extractSenderPhone($request);

        if ($rawPhone === null) {
            Log::warning('Qontak incoming webhook: sender phone not found in payload.', [
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'no_phone'], 200);
        }

        $normalizedPhone = $this->qontactService->normalizePhoneNumber($rawPhone);

        if ($normalizedPhone === '') {
            Log::warning('Qontak incoming webhook: phone could not be normalized.', [
                'raw_phone' => $rawPhone,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'invalid_phone'], 200);
        }

        CustomerWhatsAppConfirmation::recordIncoming($normalizedPhone);

        Log::info('Qontak incoming webhook: WA number recorded.', [
            'phone' => $normalizedPhone,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Ekstrak nomor WA pengirim dari berbagai kemungkinan format payload Qontak/Mekari.
     * Format bisa berbeda tergantung versi API dan channel integration.
     */
    private function extractSenderPhone(Request $request): ?string
    {
        $candidates = [
            // Mekari Qontak Omnichannel (paling umum)
            $request->input('contact.phone_number'),
            $request->input('contact.phone'),
            $request->input('data.contact.phone_number'),
            $request->input('data.contact.phone'),
            $request->input('data.from_number'),
            $request->input('data.sender'),
            // Format Qontak Chat API
            $request->input('from_number'),
            $request->input('customer_phone'),
            $request->input('customer.phone'),
            $request->input('customer.phone_number'),
            // Format WA Business API proxied via Qontak
            $request->input('from'),
            $request->input('sender'),
            $request->input('wa_id'),
            $request->input('data.wa_id'),
        ];

        foreach ($candidates as $raw) {
            if (! filled($raw)) {
                continue;
            }

            // Buang suffix WA seperti "@s.whatsapp.net" atau "@c.us"
            $cleaned = (string) str((string) $raw)->before('@');

            if (filled($cleaned)) {
                return $cleaned;
            }
        }

        return null;
    }
}
