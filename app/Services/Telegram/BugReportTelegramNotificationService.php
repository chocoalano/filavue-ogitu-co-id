<?php

namespace App\Services\Telegram;

use App\Filament\Resources\BugReports\BugReportResource;
use App\Models\BugReport;
use Illuminate\Support\Str;
use RuntimeException;
use Telegram\Bot\Laravel\Facades\Telegram;

class BugReportTelegramNotificationService
{
    public function isConfigured(): bool
    {
        return $this->missingConfigurationKeys() === [];
    }

    /**
     * @return list<string>
     */
    public function missingConfigurationKeys(): array
    {
        $missingKeys = [];

        if (! $this->hasValidChatId()) {
            $missingKeys[] = 'TELEGRAM_BUG_REPORT_CHAT_ID';
        }

        if (blank($this->botToken())) {
            $missingKeys[] = 'TELEGRAM_BOT_TOKEN';
        }

        return $missingKeys;
    }

    public function configurationErrorMessage(): string
    {
        if ($this->isConfigured()) {
            return 'Konfigurasi Telegram untuk laporan bug sudah lengkap.';
        }

        $messages = [];

        if (! $this->hasValidChatId()) {
            $messages[] = $this->chatIdConfigurationMessage();
        }

        if (blank($this->botToken())) {
            $messages[] = 'Isi TELEGRAM_BOT_TOKEN dengan token bot yang diberikan oleh BotFather.';
        }

        return implode(' ', $messages);
    }

    public function sendCreatedNotification(BugReport $bugReport): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException($this->configurationErrorMessage());
        }

        Telegram::sendMessage([
            'chat_id' => $this->chatId(),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'text' => $this->formatCreatedNotification($bugReport),
        ]);
    }

    public function sendTestNotification(?string $senderName = null): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException($this->configurationErrorMessage());
        }

        Telegram::sendMessage([
            'chat_id' => $this->chatId(),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'text' => $this->formatTestNotification($senderName),
        ]);
    }

    private function formatCreatedNotification(BugReport $bugReport): string
    {
        $bugReport->loadMissing([
            'assignee:id,name,email',
            'reporterUser:id,name,email',
            'reporterCustomer:id,name,email',
        ])->loadCount('attachments');

        $title = $this->escape($bugReport->title);
        $reporter = $this->escape($this->resolveReporterName($bugReport));
        $reporterType = $this->escape($bugReport->reporter_type->getLabel());
        $platform = $this->escape($bugReport->platform->getLabel());
        $source = $this->escape($bugReport->source->getLabel());
        $severity = $this->escape($bugReport->severity->getLabel());
        $priority = $this->escape($bugReport->priority->getLabel());
        $status = $this->escape($bugReport->status->getLabel());
        $pageUrl = $this->escape((string) Str::limit((string) $bugReport->page_url, 200));
        $description = $this->escape((string) Str::limit((string) $bugReport->description, 700));
        $assignee = $this->escape($bugReport->assignee?->name ?? 'Belum ditugaskan');
        $detailUrl = $this->escape(BugReportResource::getUrl('view', ['record' => $bugReport]));
        $attachmentCount = (int) ($bugReport->attachments_count ?? 0);

        return implode(PHP_EOL, [
            '<b>Laporan Bug Baru</b>',
            '',
            "<b>ID:</b> #{$bugReport->id}",
            "<b>Judul:</b> {$title}",
            "<b>Status:</b> {$status}",
            "<b>Pelapor:</b> {$reporter} ({$reporterType})",
            "<b>Platform:</b> {$platform}",
            "<b>Sumber:</b> {$source}",
            "<b>Severity:</b> {$severity}",
            "<b>Prioritas:</b> {$priority}",
            "<b>Ditugaskan:</b> {$assignee}",
            '<b>URL Halaman:</b> '.($pageUrl !== '' ? $pageUrl : '-'),
            "<b>Lampiran:</b> {$attachmentCount} file",
            '',
            '<b>Deskripsi Singkat:</b>',
            $description !== '' ? $description : '-',
            '',
            "<a href=\"{$detailUrl}\">Buka detail laporan</a>",
        ]);
    }

    private function formatTestNotification(?string $senderName = null): string
    {
        $appName = $this->escape((string) config('app.name'));
        $environment = $this->escape((string) app()->environment());
        $sender = $this->escape($senderName ?: 'Sistem');
        $sentAt = $this->escape(now()->format('d M Y H:i:s'));

        return implode(PHP_EOL, [
            '<b>Test Notifikasi Telegram Laporan Bug</b>',
            '',
            "<b>Aplikasi:</b> {$appName}",
            "<b>Environment:</b> {$environment}",
            "<b>Dikirim Oleh:</b> {$sender}",
            "<b>Waktu:</b> {$sentAt}",
            '',
            'Pesan ini menandakan integrasi Telegram untuk modul laporan bug aktif dan dapat dipakai.',
        ]);
    }

    private function resolveReporterName(BugReport $bugReport): string
    {
        return match ($bugReport->reporter_type->value) {
            'customer' => (string) ($bugReport->reporterCustomer?->name ?? "Customer #{$bugReport->reporter_id}"),
            'user' => (string) ($bugReport->reporterUser?->name ?? "User #{$bugReport->reporter_id}"),
            default => (string) ($bugReport->reporter_name ?: $bugReport->reporter_email ?: 'Anonymous'),
        };
    }

    private function chatId(): ?string
    {
        $chatId = $this->rawChatId();

        return $this->hasValidChatId() ? $chatId : null;
    }

    private function rawChatId(): string
    {
        return trim((string) data_get(config('services'), 'telegram.bug_report_chat_id'));
    }

    private function hasValidChatId(): bool
    {
        $chatId = $this->rawChatId();

        if ($chatId === '' || str_contains($chatId, ':')) {
            return false;
        }

        if (preg_match('/^-?\d+$/', $chatId) === 1) {
            return true;
        }

        return preg_match('/^@[A-Za-z0-9_]{5,}$/', $chatId) === 1;
    }

    private function chatIdConfigurationMessage(): string
    {
        $chatId = $this->rawChatId();

        if ($chatId === '') {
            return 'Isi TELEGRAM_BUG_REPORT_CHAT_ID dengan chat ID tujuan Telegram. Token dari BotFather hanya dipakai untuk TELEGRAM_BOT_TOKEN.';
        }

        return 'TELEGRAM_BUG_REPORT_CHAT_ID tidak valid. Gunakan chat ID Telegram seperti 123456789 atau -100..., atau username channel seperti @nama_channel. Jangan isi dengan token bot dari BotFather.';
    }

    private function botToken(): ?string
    {
        $telegramConfig = config('telegram');
        $defaultBot = trim((string) data_get($telegramConfig, 'default'));
        $token = trim((string) data_get($telegramConfig, "bots.{$defaultBot}.token"));

        if ($token === '' || $token === 'YOUR-BOT-TOKEN') {
            return null;
        }

        return $token;
    }

    private function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
