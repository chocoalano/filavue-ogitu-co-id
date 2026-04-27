<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class AppInfoWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.app-info-widget';

    /**
     * @return array<string, string|null>
     */
    public function getAppData(): array
    {
        return [
            'app_name'       => config('app.name'),
            'app_env'        => config('app.env'),
            'laravel_version' => app()->version(),
            'php_version'    => PHP_VERSION,
            'hostname'       => gethostname() ?: 'N/A',
            'os'             => PHP_OS_FAMILY,
            'cache_driver'   => config('cache.default'),
            'queue_driver'   => config('queue.default'),
            'session_driver' => config('session.driver'),
            'octane_server'  => config('octane.server'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSystemStats(): array
    {
        $memUsed  = memory_get_usage(true);
        $memPeak  = memory_get_peak_usage(true);
        $memLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

        return [
            'memory_used'    => $memUsed,
            'memory_peak'    => $memPeak,
            'memory_limit'   => $memLimit,
            'memory_percent' => $memLimit > 0 ? (int) round(($memUsed / $memLimit) * 100) : 0,
            'db_status'      => $this->checkDatabase(),
            'db_driver'      => $this->getDatabaseDriver(),
            'redis_status'   => $this->checkRedis(),
        ];
    }

    public function clearCache(): void
    {
        Artisan::call('optimize:clear');

        Notification::make()
            ->title('Cache Dibersihkan')
            ->body('Semua cache, config, route, dan view berhasil dibersihkan.')
            ->success()
            ->send();
    }

    public function recache(): void
    {
        Artisan::call('optimize');

        Notification::make()
            ->title('Cache Diperbarui')
            ->body('Config, route, dan view berhasil di-cache ulang.')
            ->success()
            ->send();
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function getDatabaseDriver(): string
    {
        try {
            return strtoupper(DB::connection()->getDriverName());
        } catch (Throwable) {
            return 'N/A';
        }
    }

    private function checkRedis(): bool
    {
        try {
            Redis::ping();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return 0;
        }

        $unit  = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g'     => $value * 1024 * 1024 * 1024,
            'm'     => $value * 1024 * 1024,
            'k'     => $value * 1024,
            default => $value,
        };
    }
}
