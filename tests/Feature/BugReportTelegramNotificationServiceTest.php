<?php

use App\Models\BugReport;
use App\Services\Telegram\BugReportTelegramNotificationService;
use Telegram\Bot\Laravel\Facades\Telegram;

beforeEach(function (): void {
    config()->set('services.telegram.bug_report_chat_id', null);
    config()->set('telegram.default', 'mybot');
    config()->set('telegram.bots.mybot.token', 'YOUR-BOT-TOKEN');
});

it('identifies missing telegram configuration for bug report notifications', function (): void {
    $service = app(BugReportTelegramNotificationService::class);

    expect($service->missingConfigurationKeys())
        ->toBe(['TELEGRAM_BUG_REPORT_CHAT_ID', 'TELEGRAM_BOT_TOKEN'])
        ->and($service->isConfigured())->toBeFalse()
        ->and($service->configurationErrorMessage())
        ->toContain('TELEGRAM_BUG_REPORT_CHAT_ID')
        ->toContain('TELEGRAM_BOT_TOKEN');
});

it('throws a clear error when telegram notification is sent without complete configuration', function (): void {
    $service = app(BugReportTelegramNotificationService::class);

    expect(fn () => $service->sendCreatedNotification(new BugReport))
        ->toThrow(RuntimeException::class, 'TELEGRAM_BUG_REPORT_CHAT_ID');
});

it('marks telegram bug report notification service as configured when chat id and bot token are set', function (): void {
    config()->set('services.telegram.bug_report_chat_id', '-1001234567890');
    config()->set('telegram.bots.mybot.token', 'telegram-bot-token-valid');

    $service = app(BugReportTelegramNotificationService::class);

    expect($service->missingConfigurationKeys())->toBe([])
        ->and($service->isConfigured())->toBeTrue()
        ->and($service->configurationErrorMessage())->toBe('Konfigurasi Telegram untuk laporan bug sudah lengkap.');
});

it('treats botfather token in bug report chat id as invalid configuration', function (): void {
    config()->set('services.telegram.bug_report_chat_id', '8410921675:AAGUmNYKzYwjp8F2SPoiMpD4jOPr9GwRFPY');
    config()->set('telegram.bots.mybot.token', 'telegram-bot-token-valid');

    $service = app(BugReportTelegramNotificationService::class);

    expect($service->missingConfigurationKeys())->toBe(['TELEGRAM_BUG_REPORT_CHAT_ID'])
        ->and($service->isConfigured())->toBeFalse()
        ->and($service->configurationErrorMessage())->toContain('Jangan isi dengan token bot dari BotFather');
});

it('sends a telegram test message when configuration is complete', function (): void {
    config()->set('services.telegram.bug_report_chat_id', '-1001234567890');
    config()->set('telegram.bots.mybot.token', 'telegram-bot-token-valid');

    $telegramFake = new class
    {
        /** @var list<array<string, mixed>> */
        public array $messages = [];

        /**
         * @param  array<string, mixed>  $payload
         * @return array<string, mixed>
         */
        public function sendMessage(array $payload): array
        {
            $this->messages[] = $payload;

            return $payload;
        }
    };

    Telegram::swap($telegramFake);

    $service = app(BugReportTelegramNotificationService::class);

    $service->sendTestNotification('QA Tester');

    expect($telegramFake->messages)->toHaveCount(1)
        ->and($telegramFake->messages[0]['chat_id'])->toBe('-1001234567890')
        ->and($telegramFake->messages[0]['parse_mode'])->toBe('HTML')
        ->and($telegramFake->messages[0]['disable_web_page_preview'])->toBeTrue()
        ->and($telegramFake->messages[0]['text'])->toContain('Test Notifikasi Telegram Laporan Bug')
        ->and($telegramFake->messages[0]['text'])->toContain('QA Tester')
        ->and($telegramFake->messages[0]['text'])->toContain((string) config('app.name'));
});
