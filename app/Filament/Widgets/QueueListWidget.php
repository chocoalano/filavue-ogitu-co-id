<?php

namespace App\Filament\Widgets;

use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class QueueListWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Queue Failed Jobs';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '36rem';

    public static function canView(): bool
    {
        $failedDriver = (string) config('queue.failed.driver');

        if (! in_array($failedDriver, ['database', 'database-uuids'], true)) {
            return false;
        }

        $connection = (string) (config('queue.failed.database') ?: config('database.default'));
        $table = (string) config('queue.failed.table', 'failed_jobs');

        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->failedJobsQuery())
            ->defaultSort('failed_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('queue')
                    ->label('Queue')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payload')
                    ->label('Job')
                    ->state(fn (FailedJob $record): string => $this->resolveJobName($record->payload))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('payload', 'like', '%'.$search.'%'))
                    ->wrap(),

                TextColumn::make('exception')
                    ->label('Error')
                    ->formatStateUsing(fn (?string $state): string => Str::of((string) $state)->squish()->limit(100)->toString())
                    ->tooltip(fn (?string $state): string => (string) $state)
                    ->wrap(),

                TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('retry_all')
                    ->label('Retry All')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        try {
                            $retried = $this->retryAllFailedJobs();

                            Notification::make()
                                ->success()
                                ->title('Retry Queue Berhasil')
                                ->body($retried.' failed job dimasukkan kembali ke queue.')
                                ->send();
                        } catch (Throwable $exception) {
                            $this->notifyActionFailure($exception);
                        }
                    }),
                Action::make('delete_all')
                    ->label('Delete All')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        try {
                            $deleted = $this->deleteAllFailedJobs();

                            Notification::make()
                                ->success()
                                ->title('Delete Queue Berhasil')
                                ->body($deleted.' failed job dihapus.')
                                ->send();
                        } catch (Throwable $exception) {
                            $this->notifyActionFailure($exception);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        try {
                            $retried = $this->retryFailedJobs(collect([$record]));

                            Notification::make()
                                ->success()
                                ->title('Retry Queue Berhasil')
                                ->body($retried.' failed job dimasukkan kembali ke queue.')
                                ->send();
                        } catch (Throwable $exception) {
                            $this->notifyActionFailure($exception);
                        }
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        try {
                            $deleted = $this->deleteFailedJobs(collect([$record]));

                            Notification::make()
                                ->success()
                                ->title('Delete Queue Berhasil')
                                ->body($deleted.' failed job dihapus.')
                                ->send();
                        } catch (Throwable $exception) {
                            $this->notifyActionFailure($exception);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry_selected')
                        ->label('Retry Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            try {
                                $retried = $this->retryFailedJobs($records);

                                Notification::make()
                                    ->success()
                                    ->title('Retry Queue Berhasil')
                                    ->body($retried.' failed job dimasukkan kembali ke queue.')
                                    ->send();
                            } catch (Throwable $exception) {
                                $this->notifyActionFailure($exception);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('delete_selected')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            try {
                                $deleted = $this->deleteFailedJobs($records);

                                Notification::make()
                                    ->success()
                                    ->title('Delete Queue Berhasil')
                                    ->body($deleted.' failed job dihapus.')
                                    ->send();
                            } catch (Throwable $exception) {
                                $this->notifyActionFailure($exception);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /** @return Builder<FailedJob> */
    private function failedJobsQuery(): Builder
    {
        $model = new FailedJob;
        $model->setConnection($this->failedJobsConnectionName());
        $model->setTable($this->failedJobsTableName());

        return $model->newQuery();
    }

    /** @param Collection<int, FailedJob> $records */
    private function retryFailedJobs(Collection $records): int
    {
        $jobIds = $records
            ->pluck('id')
            ->filter()
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();

        if ($jobIds === []) {
            return 0;
        }

        $exitCode = Artisan::call('queue:retry', [
            'id' => $jobIds,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('Perintah retry queue gagal dijalankan.');
        }

        return count($jobIds);
    }

    private function retryAllFailedJobs(): int
    {
        $totalFailedJobs = (int) $this->failedJobsQuery()->count();

        if ($totalFailedJobs === 0) {
            return 0;
        }

        $exitCode = Artisan::call('queue:retry', [
            'id' => ['all'],
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('Perintah retry all queue gagal dijalankan.');
        }

        return $totalFailedJobs;
    }

    /** @param Collection<int, FailedJob> $records */
    private function deleteFailedJobs(Collection $records): int
    {
        $jobIds = $records
            ->pluck('id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        if ($jobIds === []) {
            return 0;
        }

        return (int) $this->failedJobsQuery()
            ->whereKey($jobIds)
            ->delete();
    }

    private function deleteAllFailedJobs(): int
    {
        return (int) $this->failedJobsQuery()->delete();
    }

    private function failedJobsConnectionName(): string
    {
        return (string) (config('queue.failed.database') ?: config('database.default'));
    }

    private function failedJobsTableName(): string
    {
        return (string) config('queue.failed.table', 'failed_jobs');
    }

    private function resolveJobName(?string $payload): string
    {
        if ($payload === null || trim($payload) === '') {
            return '-';
        }

        $decodedPayload = json_decode($payload, true);

        if (! is_array($decodedPayload)) {
            return '-';
        }

        $displayName = $decodedPayload['displayName'] ?? null;

        if (is_string($displayName) && $displayName !== '') {
            return class_basename($displayName);
        }

        $job = $decodedPayload['job'] ?? null;

        if (is_string($job) && $job !== '') {
            return $job;
        }

        return '-';
    }

    private function notifyActionFailure(Throwable $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Aksi Queue Gagal')
            ->body($exception->getMessage())
            ->send();
    }
}
