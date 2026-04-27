<?php

namespace App\Filament\Widgets;

use App\Models\FailedJob;
use App\Models\QueueJob;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Throwable;

class QueueSystemMonitorWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        $snapshot = $this->getQueueHealthSnapshot();

        return [
            Stat::make('Redis', $snapshot['redis_connected'] ? 'Connected' : 'Disconnected')
                ->description($snapshot['redis_connected'] ? 'Koneksi Redis aktif.' : 'Koneksi Redis gagal.')
                ->descriptionIcon($snapshot['redis_connected'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($snapshot['redis_connected'] ? 'success' : 'danger'),

            Stat::make('Queue Worker', $snapshot['worker_status_label'])
                ->description('Driver queue: '.$snapshot['queue_driver'])
                ->descriptionIcon($snapshot['worker_running'] === false ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-cog-6-tooth')
                ->color(match ($snapshot['worker_running']) {
                    true => 'success',
                    false => 'danger',
                    null => 'gray',
                }),

            Stat::make('Pending Jobs', $this->formatCount($snapshot['pending_jobs']))
                ->description('Menunggu proses queue')
                ->descriptionIcon('heroicon-m-clock')
                ->color($snapshot['pending_jobs'] === 0 ? 'success' : 'warning'),

            Stat::make('Failed Jobs', $this->formatCount($snapshot['failed_jobs']))
                ->description('Gunakan Queue List untuk retry/delete')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color(($snapshot['failed_jobs'] ?? 0) > 0 ? 'danger' : 'success'),
        ];
    }

    /**
     * @return array{
     *   queue_driver: string,
     *   failed_driver: string,
     *   redis_connected: bool,
     *   worker_running: bool|null,
     *   worker_status_label: string,
     *   pending_jobs: int|null,
     *   failed_jobs: int|null
     * }
     */
    public function getQueueHealthSnapshot(): array
    {
        $queueDriver = (string) config('queue.default');
        $failedDriver = (string) config('queue.failed.driver');
        $workerRunning = $this->resolveWorkerRunning($queueDriver);

        return [
            'queue_driver' => $queueDriver,
            'failed_driver' => $failedDriver,
            'redis_connected' => $this->checkRedisConnection(),
            'worker_running' => $workerRunning,
            'worker_status_label' => $this->workerStatusLabel($workerRunning),
            'pending_jobs' => $this->pendingJobsCount($queueDriver),
            'failed_jobs' => $this->failedJobsCount($failedDriver),
        ];
    }

    private function formatCount(?int $count): string
    {
        if ($count === null) {
            return 'N/A';
        }

        return number_format($count, 0, ',', '.');
    }

    private function workerStatusLabel(?bool $workerRunning): string
    {
        return match ($workerRunning) {
            true => 'Running',
            false => 'Stopped',
            null => 'Not Required',
        };
    }

    private function resolveWorkerRunning(string $queueDriver): ?bool
    {
        if (! $this->isWorkerRequired($queueDriver)) {
            return null;
        }

        return $this->detectWorkerProcess();
    }

    private function isWorkerRequired(string $queueDriver): bool
    {
        return in_array($queueDriver, ['database', 'redis', 'beanstalkd', 'sqs'], true);
    }

    private function detectWorkerProcess(): bool
    {
        try {
            $process = Process::fromShellCommandline('ps -axo command | grep -E "artisan (queue:work|queue:listen|horizon)" | grep -v grep');
            $process->setTimeout(2);
            $process->run();

            return trim($process->getOutput()) !== '';
        } catch (Throwable) {
            return false;
        }
    }

    private function checkRedisConnection(): bool
    {
        try {
            Redis::ping();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function pendingJobsCount(string $queueDriver): ?int
    {
        return match ($queueDriver) {
            'database' => $this->databasePendingJobsCount(),
            'redis' => $this->redisPendingJobsCount(),
            default => null,
        };
    }

    private function databasePendingJobsCount(): ?int
    {
        $connection = $this->queueDatabaseConnectionName();
        $table = $this->queueJobsTableName();

        try {
            if (! Schema::connection($connection)->hasTable($table)) {
                return null;
            }

            $model = new QueueJob;
            $model->setConnection($connection);
            $model->setTable($table);

            return (int) $model->newQuery()->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function redisPendingJobsCount(): ?int
    {
        try {
            $queueNames = explode(',', (string) config('queue.connections.redis.queue', 'default'));
            $redisConnection = (string) config('queue.connections.redis.connection', 'default');
            $redis = Redis::connection($redisConnection);
            $total = 0;

            foreach ($queueNames as $queueName) {
                $normalizedQueueName = trim($queueName);

                if ($normalizedQueueName === '') {
                    continue;
                }

                $total += (int) $redis->llen('queues:'.$normalizedQueueName);
            }

            return $total;
        } catch (Throwable) {
            return null;
        }
    }

    private function failedJobsCount(string $failedDriver): ?int
    {
        if (! in_array($failedDriver, ['database', 'database-uuids'], true)) {
            return null;
        }

        $connection = $this->failedJobsConnectionName();
        $table = $this->failedJobsTableName();

        try {
            if (! Schema::connection($connection)->hasTable($table)) {
                return null;
            }

            $model = new FailedJob;
            $model->setConnection($connection);
            $model->setTable($table);

            return (int) $model->newQuery()->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function queueDatabaseConnectionName(): string
    {
        return (string) (config('queue.connections.database.connection') ?: config('database.default'));
    }

    private function queueJobsTableName(): string
    {
        return (string) config('queue.connections.database.table', 'jobs');
    }

    private function failedJobsConnectionName(): string
    {
        return (string) (config('queue.failed.database') ?: config('database.default'));
    }

    private function failedJobsTableName(): string
    {
        return (string) config('queue.failed.table', 'failed_jobs');
    }
}
