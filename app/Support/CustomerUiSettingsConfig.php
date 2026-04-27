<?php

namespace App\Support;

use App\Models\Setting;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class CustomerUiSettingsConfig
{
    /**
     * @return array<string, string>
     */
    public static function tableColumnOptions(): array
    {
        return [
            'username' => 'Username',
            'ewallet_id' => 'Ewallet ID',
            'name' => 'Nama',
            'package_name' => 'Paket Member',
            'level' => 'Peringkat',
            'phone' => 'Telepon',
            'ewallet_saldo' => 'Saldo',
            'sponsor_name' => 'Sponsor',
            'status' => 'Status',
            'ref_code' => 'Ref Code',
            'nik' => 'NIK',
            'email' => 'Email',
            'gender' => 'Gender',
            'address' => 'Alamat',
            'city_id' => 'City ID',
            'province_id' => 'Province ID',
            'email_verified_at' => 'Email Verified',
            'bonus_pending' => 'Bonus Pending',
            'bonus_processed' => 'Bonus Processed',
            'bank_name' => 'Bank',
            'bank_account' => 'No Rekening',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tableFilterOptions(): array
    {
        return [
            'status' => 'Status',
            'package_id' => 'Paket',
            'level' => 'Level',
            'gender' => 'Jenis Kelamin',
            'is_stockist' => 'Stockist',
            'network_generated' => 'Network Generated',
            'email_verified_at' => 'Verifikasi Email',
            'created_at' => 'Tanggal Bergabung',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function formSectionOptions(): array
    {
        return [
            'profile_basic' => 'Profil Dasar',
            'addresses' => 'Buku Alamat',
            'network_affiliation' => 'Sponsor & Afiliasi',
            'financial_bank' => 'Data Rekening',
            'stockist_authority' => 'Otoritas Stockist',
            'admin_internal_log' => 'Admin Internal Log',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusColorOptions(): array
    {
        return [
            'gray' => 'Gray',
            'warning' => 'Warning',
            'success' => 'Success',
            'info' => 'Info',
            'primary' => 'Primary',
            'danger' => 'Danger',
        ];
    }

    /**
     * @return array{table: array{columns: array<string, array{enabled: bool, hidden_by_default: bool}>, filters: array<string, bool>}, form: array{sections: array<string, bool>}, status: array{labels: array<string, string>, colors: array<string, string>}}
     */
    public static function getState(): array
    {
        return Cache::remember(self::cacheKey(), now()->addMinutes(30), static function (): array {
            return self::loadStateFromDatabase();
        });
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function writeState(array $state): void
    {
        $normalized = self::normalizeState($state);

        /** @var array<string, string> $payload */
        $payload = [
            'table.columns' => json_encode($normalized['table']['columns'], JSON_UNESCAPED_UNICODE) ?: '{}',
            'table.filters' => json_encode($normalized['table']['filters'], JSON_UNESCAPED_UNICODE) ?: '{}',
            'form.sections' => json_encode($normalized['form']['sections'], JSON_UNESCAPED_UNICODE) ?: '{}',
            'status.labels' => json_encode($normalized['status']['labels'], JSON_UNESCAPED_UNICODE) ?: '{}',
            'status.colors' => json_encode($normalized['status']['colors'], JSON_UNESCAPED_UNICODE) ?: '{}',
        ];

        foreach ($payload as $path => $value) {
            $key = self::resolveExistingKeyForWrite($path);

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'text',
                    'group' => 'general',
                ],
            );
        }

        Cache::forget(self::cacheKey());
    }

    /**
     * @return array<string, array{enabled: bool, hidden_by_default: bool}>
     */
    public static function tableColumnSettings(): array
    {
        return self::getState()['table']['columns'];
    }

    /**
     * @return array<string, bool>
     */
    public static function tableFilterSettings(): array
    {
        return self::getState()['table']['filters'];
    }

    /**
     * @return array<string, bool>
     */
    public static function formSectionSettings(): array
    {
        return self::getState()['form']['sections'];
    }

    /**
     * @return array<int, string>
     */
    public static function statusLabels(): array
    {
        return collect(self::getState()['status']['labels'])
            ->mapWithKeys(static fn (string $label, string $status): array => [(int) $status => $label])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function statusColors(): array
    {
        return collect(self::getState()['status']['colors'])
            ->mapWithKeys(static fn (string $color, string $status): array => [(int) $status => $color])
            ->all();
    }

    /**
     * @return array{table: array{columns: array<string, array{enabled: bool, hidden_by_default: bool}>, filters: array<string, bool>}, form: array{sections: array<string, bool>}, status: array{labels: array<string, string>, colors: array<string, string>}}
     */
    public static function defaultState(): array
    {
        return [
            'table' => [
                'columns' => [
                    'username' => ['enabled' => true, 'hidden_by_default' => false],
                    'ewallet_id' => ['enabled' => true, 'hidden_by_default' => false],
                    'name' => ['enabled' => true, 'hidden_by_default' => false],
                    'package_name' => ['enabled' => true, 'hidden_by_default' => false],
                    'level' => ['enabled' => true, 'hidden_by_default' => false],
                    'phone' => ['enabled' => true, 'hidden_by_default' => false],
                    'ewallet_saldo' => ['enabled' => true, 'hidden_by_default' => false],
                    'sponsor_name' => ['enabled' => true, 'hidden_by_default' => false],
                    'status' => ['enabled' => true, 'hidden_by_default' => false],
                    'ref_code' => ['enabled' => true, 'hidden_by_default' => true],
                    'nik' => ['enabled' => true, 'hidden_by_default' => true],
                    'email' => ['enabled' => true, 'hidden_by_default' => true],
                    'gender' => ['enabled' => true, 'hidden_by_default' => true],
                    'address' => ['enabled' => true, 'hidden_by_default' => true],
                    'city_id' => ['enabled' => true, 'hidden_by_default' => true],
                    'province_id' => ['enabled' => true, 'hidden_by_default' => true],
                    'email_verified_at' => ['enabled' => true, 'hidden_by_default' => true],
                    'bonus_pending' => ['enabled' => true, 'hidden_by_default' => true],
                    'bonus_processed' => ['enabled' => true, 'hidden_by_default' => true],
                    'bank_name' => ['enabled' => true, 'hidden_by_default' => true],
                    'bank_account' => ['enabled' => true, 'hidden_by_default' => true],
                    'created_at' => ['enabled' => true, 'hidden_by_default' => true],
                    'updated_at' => ['enabled' => true, 'hidden_by_default' => true],
                ],
                'filters' => collect(self::tableFilterOptions())
                    ->map(static fn (): bool => true)
                    ->all(),
            ],
            'form' => [
                'sections' => collect(self::formSectionOptions())
                    ->map(static fn (): bool => true)
                    ->all(),
            ],
            'status' => [
                'labels' => [
                    '1' => 'Prospek',
                    '2' => 'Pasif',
                    '3' => 'Aktif',
                ],
                'colors' => [
                    '1' => 'gray',
                    '2' => 'warning',
                    '3' => 'success',
                ],
            ],
        ];
    }

    /**
     * @return array{table: array{columns: array<string, array{enabled: bool, hidden_by_default: bool}>, filters: array<string, bool>}, form: array{sections: array<string, bool>}, status: array{labels: array<string, string>, colors: array<string, string>}}
     */
    private static function loadStateFromDatabase(): array
    {
        $default = self::defaultState();
        $state = $default;

        $paths = self::settingPaths();
        $keys = collect($paths)
            ->flatMap(static fn (string $path): array => self::candidateKeys($path))
            ->unique()
            ->values()
            ->all();

        $rows = Setting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        foreach ($paths as $path) {
            $stored = self::firstStoredValue($rows->all(), $path);

            if ($stored === null) {
                continue;
            }

            $decoded = json_decode($stored, true);

            if (! is_array($decoded)) {
                continue;
            }

            Arr::set($state, $path, $decoded);
        }

        return self::normalizeState($state);
    }

    /**
     * @param  array<string, mixed>  $rows
     */
    private static function firstStoredValue(array $rows, string $path): ?string
    {
        foreach (self::candidateKeys($path) as $key) {
            if (array_key_exists($key, $rows)) {
                $value = $rows[$key];

                return is_string($value) ? $value : null;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array{table: array{columns: array<string, array{enabled: bool, hidden_by_default: bool}>, filters: array<string, bool>}, form: array{sections: array<string, bool>}, status: array{labels: array<string, string>, colors: array<string, string>}}
     */
    private static function normalizeState(array $state): array
    {
        $default = self::defaultState();

        return [
            'table' => [
                'columns' => self::normalizeColumns(
                    Arr::get($state, 'table.columns'),
                    $default['table']['columns'],
                ),
                'filters' => self::normalizeToggleMap(
                    Arr::get($state, 'table.filters'),
                    $default['table']['filters'],
                ),
            ],
            'form' => [
                'sections' => self::normalizeToggleMap(
                    Arr::get($state, 'form.sections'),
                    $default['form']['sections'],
                ),
            ],
            'status' => [
                'labels' => self::normalizeStatusLabels(
                    Arr::get($state, 'status.labels'),
                    $default['status']['labels'],
                ),
                'colors' => self::normalizeStatusColors(
                    Arr::get($state, 'status.colors'),
                    $default['status']['colors'],
                ),
            ],
        ];
    }

    /**
     * @param  array<string, array{enabled: bool, hidden_by_default: bool}>  $defaults
     * @return array<string, array{enabled: bool, hidden_by_default: bool}>
     */
    private static function normalizeColumns(mixed $value, array $defaults): array
    {
        if (! is_array($value)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($defaults as $key => $default) {
            $row = $value[$key] ?? [];

            if (! is_array($row)) {
                $row = [];
            }

            $normalized[$key] = [
                'enabled' => array_key_exists('enabled', $row)
                    ? (bool) $row['enabled']
                    : $default['enabled'],
                'hidden_by_default' => array_key_exists('hidden_by_default', $row)
                    ? (bool) $row['hidden_by_default']
                    : $default['hidden_by_default'],
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, bool>  $defaults
     * @return array<string, bool>
     */
    private static function normalizeToggleMap(mixed $value, array $defaults): array
    {
        if (! is_array($value)) {
            return $defaults;
        }

        if (array_is_list($value)) {
            $selectedKeys = collect($value)
                ->filter(static fn (mixed $item): bool => is_string($item) || is_int($item))
                ->map(static fn (int|string $item): string => (string) $item)
                ->all();

            return collect($defaults)
                ->mapWithKeys(static fn (bool $_, string $key): array => [$key => in_array($key, $selectedKeys, true)])
                ->all();
        }

        $normalized = [];

        foreach ($defaults as $key => $default) {
            $normalized[$key] = array_key_exists($key, $value)
                ? (bool) $value[$key]
                : $default;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $defaults
     * @return array<string, string>
     */
    private static function normalizeStatusLabels(mixed $value, array $defaults): array
    {
        if (! is_array($value)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($defaults as $status => $defaultLabel) {
            $raw = $value[$status] ?? null;
            $label = is_string($raw) ? trim($raw) : '';
            $normalized[$status] = $label !== '' ? $label : $defaultLabel;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $defaults
     * @return array<string, string>
     */
    private static function normalizeStatusColors(mixed $value, array $defaults): array
    {
        if (! is_array($value)) {
            return $defaults;
        }

        $allowedColors = array_keys(self::statusColorOptions());
        $normalized = [];

        foreach ($defaults as $status => $defaultColor) {
            $raw = $value[$status] ?? null;
            $color = is_string($raw) ? trim($raw) : '';

            $normalized[$status] = in_array($color, $allowedColors, true)
                ? $color
                : $defaultColor;
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private static function settingPaths(): array
    {
        return [
            'table.columns',
            'table.filters',
            'form.sections',
            'status.labels',
            'status.colors',
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function candidateKeys(string $path): array
    {
        $prefix = self::getKeyPrefix();

        if ($prefix === '') {
            return ['customers.'.$path];
        }

        return [$prefix.'customers.'.$path, 'customers.'.$path];
    }

    private static function resolveExistingKeyForWrite(string $path): string
    {
        $candidates = self::candidateKeys($path);
        $existing = Setting::query()->whereIn('key', $candidates)->value('key');

        return is_string($existing) && $existing !== '' ? $existing : $candidates[0];
    }

    private static function cacheKey(): string
    {
        return 'customer_ui_settings:'.self::getKeyPrefix();
    }

    private static function getKeyPrefix(): string
    {
        try {
            $tenant = Filament::getTenant();
        } catch (\Throwable) {
            $tenant = null;
        }

        return $tenant ? ('tenant:'.(string) $tenant->getKey().'.') : '';
    }
}
