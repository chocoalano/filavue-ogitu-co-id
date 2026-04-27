<?php

namespace App\Jobs;

use App\Services\QontactService;
use App\Support\QontakWhatsAppSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendWhatsAppTestMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public string $recipientName,
        public string $phoneNumber,
        public string $templateId,
        public string $message,
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(QontactService $qontactService): void
    {
        $recipientName = trim($this->recipientName) !== '' ? trim($this->recipientName) : 'Tester Admin';
        $phoneNumber = trim($this->phoneNumber);
        $templateId = trim($this->templateId);
        $message = trim($this->message);

        if ($phoneNumber === '') {
            throw new RuntimeException('Nomor tujuan WhatsApp wajib diisi untuk test message.');
        }

        if ($templateId === '') {
            throw new RuntimeException('Template ID Qontak wajib diisi untuk test message.');
        }

        $bodyParams = [$recipientName];

        if ($message !== '') {
            $bodyParams[] = $message;
        }

        $result = $qontactService->sendWhatsAppWithResultFromParams(
            $recipientName,
            $phoneNumber,
            $templateId,
            $bodyParams,
            'id',
            $this->resolveHeaderImageUrl(),
        );

        if (! (bool) ($result['success'] ?? false)) {
            $errorMessage = trim((string) ($result['error'] ?? ''));

            throw new RuntimeException($errorMessage !== '' ? $errorMessage : 'Pengiriman pesan test WhatsApp gagal.');
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('WhatsApp test message job failed.', [
            'phone' => $this->phoneNumber,
            'template_id' => $this->templateId,
            'error' => $exception?->getMessage(),
        ]);
    }

    private function resolveHeaderImageUrl(): ?string
    {
        $configuredBroadcastHeader = trim((string) QontakWhatsAppSettings::get(
            'broadcast.header_image_url',
            config('services.qontak.broadcast_header_image_url', '')
        ));

        if (($configuredBroadcastHeader !== '') && $this->isSupportedHeaderImageUrl($configuredBroadcastHeader)) {
            return $configuredBroadcastHeader;
        }

        $configuredWithdrawalHeader = trim((string) QontakWhatsAppSettings::get(
            'notifications.withdrawal_approved.header_image_url',
            config('services.qontak.wd_approved_header_image_url', '')
        ));

        if (($configuredWithdrawalHeader !== '') && $this->isSupportedHeaderImageUrl($configuredWithdrawalHeader)) {
            return $configuredWithdrawalHeader;
        }

        return 'https://ogitu.id/logo.png';
    }

    private function isSupportedHeaderImageUrl(string $url): bool
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return \in_array($extension, ['jpg', 'jpeg', 'png'], true);
    }
}
