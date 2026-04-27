<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class QontakWhatsAppSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function getState(): array
    {
        return Cache::remember(self::cacheKey(), now()->addMinutes(30), static fn (): array => self::loadStateFromDatabase());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::cacheKey());
    }

    public static function get(string $path, mixed $default = null): mixed
    {
        return data_get(self::getState(), $path, $default);
    }

    public static function hasConfiguredApiToken(): bool
    {
        return trim((string) self::get('connection.api_token', '')) !== '';
    }

    public static function notificationEnabled(string $notification): bool
    {
        return (bool) self::get("notifications.{$notification}.enabled", false);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function writeState(array $state): void
    {
        $normalized = self::normalizeState($state);

        foreach (self::settingPaths() as $path) {
            $raw = data_get($normalized, $path);

            if (self::isSecretPath($path) && trim((string) $raw) === '') {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => self::resolveExistingKeyForWrite($path)],
                [
                    'value' => self::serializeValueForStorage($path, $raw),
                    'type' => 'text',
                    'group' => 'qontak',
                ],
            );
        }

        self::forgetCache();
    }

    public static function defaultState(): array
    {
        return [
            'connection' => [
                'base_url' => (string) config('services.qontak.base_url', 'https://service-chat.qontak.com/api/open/v1'),
                'api_token' => (string) config('services.qontak.api_token', ''),
                'channel_integration_id' => (string) config('services.qontak.channel_integration_id', ''),
                'timeout' => (int) config('services.qontak.timeout', 30),
            ],
            'notifications' => [
                'withdrawal_approved' => [
                    'enabled' => (bool) config('services.qontak.wd_approved_enabled', true),
                    'template_id' => (string) config('services.qontak.wd_approved_template_id', ''),
                    'header_image_url' => (string) config('services.qontak.wd_approved_header_image_url', ''),
                    'parameters' => [],
                ],
                'withdrawal_rejected' => [
                    'enabled' => (bool) config('services.qontak.wd_rejected_enabled', true),
                    'template_id' => (string) config('services.qontak.wd_rejected_template_id', ''),
                    'parameters' => [],
                ],
            ],
            'broadcast' => [
                'default_template_id' => (string) config('services.qontak.broadcast_template_id', ''),
                'header_image_url' => (string) config('services.qontak.broadcast_header_image_url', ''),
                'bulk_retry_attempts' => (int) config('services.qontak.bulk_retry_attempts', 2),
                'bulk_retry_buffer_seconds' => (int) config('services.qontak.bulk_retry_buffer_seconds', 2),
            ],
        ];
    }

    private static function loadStateFromDatabase(): array
    {
        $default = self::defaultState();
        $state = $default;

        try {
            $rows = Setting::query()
                ->whereIn('key', self::allCandidateKeys())
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable) {
            return $default;
        }

        foreach (self::settingPaths() as $path) {
            $stored = self::firstStoredValue($rows, $path);

            if ($stored === null) {
                continue;
            }

            Arr::set($state, $path, self::parseValueFromStorage($path, $stored));
        }

        return self::normalizeState($state);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private static function normalizeState(array $state): array
    {
        $default = self::defaultState();
        $normalized = $default;

        $normalized['connection']['base_url'] = rtrim(
            trim((string) data_get($state, 'connection.base_url', $default['connection']['base_url'])),
            '/'
        );
        $normalized['connection']['api_token'] = trim((string) data_get($state, 'connection.api_token', $default['connection']['api_token']));
        $normalized['connection']['channel_integration_id'] = trim((string) data_get($state, 'connection.channel_integration_id', $default['connection']['channel_integration_id']));
        $normalized['connection']['timeout'] = max(1, (int) data_get($state, 'connection.timeout', $default['connection']['timeout']));

        $normalized['notifications']['withdrawal_approved']['enabled'] = (bool) data_get(
            $state,
            'notifications.withdrawal_approved.enabled',
            $default['notifications']['withdrawal_approved']['enabled']
        );
        $normalized['notifications']['withdrawal_approved']['template_id'] = trim((string) data_get(
            $state,
            'notifications.withdrawal_approved.template_id',
            $default['notifications']['withdrawal_approved']['template_id']
        ));
        $normalized['notifications']['withdrawal_approved']['header_image_url'] = trim((string) data_get(
            $state,
            'notifications.withdrawal_approved.header_image_url',
            $default['notifications']['withdrawal_approved']['header_image_url']
        ));
        $normalized['notifications']['withdrawal_approved']['parameters'] = self::normalizeTemplateParameters(
            data_get(
                $state,
                'notifications.withdrawal_approved.parameters',
                $default['notifications']['withdrawal_approved']['parameters']
            )
        );

        $normalized['notifications']['withdrawal_rejected']['enabled'] = (bool) data_get(
            $state,
            'notifications.withdrawal_rejected.enabled',
            $default['notifications']['withdrawal_rejected']['enabled']
        );
        $normalized['notifications']['withdrawal_rejected']['template_id'] = trim((string) data_get(
            $state,
            'notifications.withdrawal_rejected.template_id',
            $default['notifications']['withdrawal_rejected']['template_id']
        ));
        $normalized['notifications']['withdrawal_rejected']['parameters'] = self::normalizeTemplateParameters(
            data_get(
                $state,
                'notifications.withdrawal_rejected.parameters',
                $default['notifications']['withdrawal_rejected']['parameters']
            )
        );

        $normalized['broadcast']['default_template_id'] = trim((string) data_get(
            $state,
            'broadcast.default_template_id',
            $default['broadcast']['default_template_id']
        ));
        $normalized['broadcast']['header_image_url'] = trim((string) data_get(
            $state,
            'broadcast.header_image_url',
            $default['broadcast']['header_image_url']
        ));
        $normalized['broadcast']['bulk_retry_attempts'] = max(1, (int) data_get(
            $state,
            'broadcast.bulk_retry_attempts',
            $default['broadcast']['bulk_retry_attempts']
        ));
        $normalized['broadcast']['bulk_retry_buffer_seconds'] = max(0, (int) data_get(
            $state,
            'broadcast.bulk_retry_buffer_seconds',
            $default['broadcast']['bulk_retry_buffer_seconds']
        ));

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private static function settingPaths(): array
    {
        return [
            'connection.base_url',
            'connection.api_token',
            'connection.channel_integration_id',
            'connection.timeout',
            'notifications.withdrawal_approved.enabled',
            'notifications.withdrawal_approved.template_id',
            'notifications.withdrawal_approved.header_image_url',
            'notifications.withdrawal_approved.parameters',
            'notifications.withdrawal_rejected.enabled',
            'notifications.withdrawal_rejected.template_id',
            'notifications.withdrawal_rejected.parameters',
            'broadcast.default_template_id',
            'broadcast.header_image_url',
            'broadcast.bulk_retry_attempts',
            'broadcast.bulk_retry_buffer_seconds',
        ];
    }

    /**
     * @return list<string>
     */
    private static function candidateKeys(string $path): array
    {
        return match ($path) {
            'connection.base_url' => ['qontak.connection.base_url', 'qontak.base_url'],
            'connection.api_token' => ['qontak.connection.api_token', 'qontak.api_token'],
            'connection.channel_integration_id' => ['qontak.connection.channel_integration_id', 'qontak.channel_integration_id'],
            'connection.timeout' => ['qontak.connection.timeout', 'qontak.timeout'],
            'notifications.withdrawal_approved.enabled' => ['qontak.notifications.withdrawal_approved.enabled', 'qontak.wd_approved_enabled'],
            'notifications.withdrawal_approved.template_id' => ['qontak.notifications.withdrawal_approved.template_id', 'qontak.wd_approved_template_id'],
            'notifications.withdrawal_approved.header_image_url' => ['qontak.notifications.withdrawal_approved.header_image_url', 'qontak.wd_approved_header_image_url'],
            'notifications.withdrawal_approved.parameters' => ['qontak.notifications.withdrawal_approved.parameters', 'qontak.wd_approved_parameters'],
            'notifications.withdrawal_rejected.enabled' => ['qontak.notifications.withdrawal_rejected.enabled', 'qontak.wd_rejected_enabled'],
            'notifications.withdrawal_rejected.template_id' => ['qontak.notifications.withdrawal_rejected.template_id', 'qontak.wd_rejected_template_id'],
            'notifications.withdrawal_rejected.parameters' => ['qontak.notifications.withdrawal_rejected.parameters', 'qontak.wd_rejected_parameters'],
            'broadcast.default_template_id' => ['qontak.broadcast.default_template_id', 'qontak.broadcast_template_id'],
            'broadcast.header_image_url' => ['qontak.broadcast.header_image_url', 'qontak.broadcast_header_image_url'],
            'broadcast.bulk_retry_attempts' => ['qontak.broadcast.bulk_retry_attempts', 'qontak.bulk_retry_attempts'],
            'broadcast.bulk_retry_buffer_seconds' => ['qontak.broadcast.bulk_retry_buffer_seconds', 'qontak.bulk_retry_buffer_seconds'],
            default => ['qontak.'.$path],
        };
    }

    /**
     * @return list<string>
     */
    private static function allCandidateKeys(): array
    {
        return collect(self::settingPaths())
            ->flatMap(static fn (string $path): array => self::candidateKeys($path))
            ->unique()
            ->values()
            ->all();
    }

    private static function resolveExistingKeyForWrite(string $path): string
    {
        try {
            $existing = Setting::query()
                ->whereIn('key', self::candidateKeys($path))
                ->value('key');
        } catch (\Throwable) {
            return self::candidateKeys($path)[0];
        }

        return is_string($existing) && $existing !== '' ? $existing : self::candidateKeys($path)[0];
    }

    private static function firstStoredValue(array $rows, string $path): ?string
    {
        foreach (self::candidateKeys($path) as $key) {
            $value = $rows[$key] ?? null;

            if ($value !== null) {
                return is_string($value) ? $value : (string) $value;
            }
        }

        return null;
    }

    private static function isSecretPath(string $path): bool
    {
        return $path === 'connection.api_token';
    }

    private static function serializeValueForStorage(string $path, mixed $value): string
    {
        if (self::isSecretPath($path)) {
            return Crypt::encryptString(trim((string) $value));
        }

        if (self::isJsonPath($path)) {
            $encoded = json_encode(self::normalizeTemplateParameters($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($encoded) ? $encoded : '[]';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    private static function parseValueFromStorage(string $path, string $value): mixed
    {
        if (self::isSecretPath($path)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        if (self::isJsonPath($path)) {
            $decoded = json_decode($value, true);

            return self::normalizeTemplateParameters($decoded);
        }

        return match ($path) {
            'connection.timeout',
            'broadcast.bulk_retry_attempts',
            'broadcast.bulk_retry_buffer_seconds' => (int) $value,
            'notifications.withdrawal_approved.enabled',
            'notifications.withdrawal_rejected.enabled' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                ?? ($value === '1'),
            default => $value,
        };
    }

    private static function isJsonPath(string $path): bool
    {
        return \in_array($path, [
            'notifications.withdrawal_approved.parameters',
            'notifications.withdrawal_rejected.parameters',
        ], true);
    }

    /**
     * @return list<array{key: string, value: string, source_table: string, source_column: string}>
     */
    private static function normalizeTemplateParameters(mixed $parameters): array
    {
        if (! is_array($parameters)) {
            return [];
        }

        $items = [];

        foreach ($parameters as $parameter) {
            if (! is_array($parameter)) {
                continue;
            }

            $key = trim((string) ($parameter['key'] ?? ''));
            $value = trim((string) ($parameter['value'] ?? ''));

            if ($key === '' && $value === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
                'source_table' => trim((string) ($parameter['source_table'] ?? '')),
                'source_column' => trim((string) ($parameter['source_column'] ?? '')),
            ];
        }

        return array_values($items);
    }

    private static function cacheKey(): string
    {
        return 'qontak_whatsapp_settings';
    }
}
