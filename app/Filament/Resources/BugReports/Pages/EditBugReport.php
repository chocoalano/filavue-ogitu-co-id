<?php

namespace App\Filament\Resources\BugReports\Pages;

use App\Enums\BugStatus;
use App\Filament\Resources\BugReports\BugReportResource;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Models\BugReport;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditBugReport extends EditRecord
{
    protected static string $resource = BugReportResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewBugReport::class,
            DiscussBugReport::class,
            static::class,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('chat')
                ->label('Percakapan')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('gray')
                ->url(fn (): string => $this->getResourceUrl('chat')),
            Action::make('repair')
                ->label('Perbaikan')
                ->icon('heroicon-o-wrench-screwdriver')
                ->visible(fn (): bool => BugReportForm::canEditResolution($this->getRecord()) && $this->getRecord()->status !== BugStatus::Closed)
                ->modalHeading('Form Perbaikan')
                ->modalDescription('Isi hasil penanganan bug ini. Catatan resolusi dan waktu resolved hanya dapat diisi melalui dialog ini oleh user yang ditugaskan.')
                ->modalSubmitActionLabel('Simpan Perbaikan')
                ->fillForm(fn (): array => $this->repairFormDefaults($this->getRecord()))
                ->form([
                    Select::make('resolution_status')
                        ->label('Hasil Penanganan')
                        ->options([
                            BugStatus::Resolved->value => BugStatus::Resolved->getLabel(),
                            BugStatus::Rejected->value => BugStatus::Rejected->getLabel(),
                        ])
                        ->required()
                        ->live()
                        ->validationMessages([
                            'required' => 'Pilih hasil penanganan bug ini: Resolved atau Rejected.',
                        ]),
                    Textarea::make('resolution_note')
                        ->label('Catatan Resolusi')
                        ->required()
                        ->rows(4)
                        ->validationMessages([
                            'required' => 'Catatan resolusi wajib diisi oleh user yang ditugaskan.',
                        ])
                        ->placeholder('Jelaskan perbaikan yang dilakukan, hasil investigasi, atau alasan penolakan...'),
                    DateTimePicker::make('resolved_at')
                        ->label('Waktu Resolved')
                        ->seconds(false)
                        ->required(fn ($get): bool => $get('resolution_status') === BugStatus::Resolved->value)
                        ->validationMessages([
                            'required' => 'Waktu resolved wajib diisi saat hasil penanganan ditandai Resolved.',
                        ])
                        ->helperText('Isi waktu penyelesaian jika bug sudah benar-benar selesai diperbaiki.'),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    if (! BugReportForm::canEditResolution($record)) {
                        throw ValidationException::withMessages([
                            'data.assigned_to' => 'Dialog Perbaikan hanya dapat digunakan oleh user yang sedang ditugaskan menangani bug ini.',
                        ]);
                    }

                    $resolutionStatus = BugStatus::tryFrom((string) ($data['resolution_status'] ?? ''));

                    if (! in_array($resolutionStatus, [BugStatus::Resolved, BugStatus::Rejected], true)) {
                        throw ValidationException::withMessages([
                            'mountedActionsData.0.resolution_status' => 'Hasil penanganan bug harus berupa Resolved atau Rejected.',
                        ]);
                    }

                    $resolvedAt = $data['resolved_at'] ?? null;

                    if ($resolutionStatus === BugStatus::Resolved && blank($resolvedAt)) {
                        throw ValidationException::withMessages([
                            'mountedActionsData.0.resolved_at' => 'Waktu resolved wajib diisi saat hasil penanganan ditandai Resolved.',
                        ]);
                    }

                    $record->forceFill([
                        'status' => $resolutionStatus->value,
                        'resolution_note' => trim((string) ($data['resolution_note'] ?? '')),
                        'resolved_at' => filled($resolvedAt)
                            ? $resolvedAt
                            : ($resolutionStatus === BugStatus::Resolved ? ($record->resolved_at ?? now()) : $record->resolved_at),
                    ])->save();

                    $record->refresh();
                    $this->record = $record;
                    $this->fillForm();
                    $this->rememberData();

                    Notification::make()
                        ->success()
                        ->title('Data perbaikan berhasil disimpan.')
                        ->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($duplicateReport = BugReportForm::findDuplicateTitleReport(
            (string) ($data['title'] ?? $record->title),
            (int) $record->getKey(),
        )) {
            throw ValidationException::withMessages([
                'data.title' => BugReportForm::duplicateTitleMessage($duplicateReport),
            ]);
        }

        $currentStatus = $record->status;
        $requestedStatus = BugStatus::tryFrom((string) ($data['status'] ?? $currentStatus->value)) ?? $currentStatus;

        $isAssignedUser = BugReportForm::isAssignedUser($record);
        $isReporterUser = BugReportForm::isReporterUser($record);

        if (
            in_array($requestedStatus, [BugStatus::Resolved, BugStatus::Rejected], true)
            && $requestedStatus !== $currentStatus
        ) {
            throw ValidationException::withMessages([
                'data.status' => 'Status Resolved dan Rejected hanya dapat diubah melalui dialog Perbaikan oleh user yang sedang ditugaskan menangani bug ini.',
            ]);
        }

        if ($currentStatus === BugStatus::Closed && $requestedStatus !== BugStatus::Closed) {
            throw ValidationException::withMessages([
                'data.status' => 'Laporan yang sudah Closed tidak dapat diubah kembali melalui form edit.',
            ]);
        }

        if (
            $currentStatus === BugStatus::Resolved
            && ! in_array($requestedStatus, [BugStatus::Resolved, BugStatus::Closed], true)
        ) {
            throw ValidationException::withMessages([
                'data.status' => 'Laporan yang sudah Resolved hanya dapat dipertahankan sebagai Resolved atau dikonfirmasi menjadi Closed oleh pelapor.',
            ]);
        }

        if ($currentStatus === BugStatus::Rejected && $requestedStatus !== BugStatus::Rejected) {
            throw ValidationException::withMessages([
                'data.status' => 'Laporan yang sudah Rejected hanya dapat diperbarui kembali melalui dialog Perbaikan oleh user yang ditugaskan.',
            ]);
        }

        if ($requestedStatus === BugStatus::Closed) {
            if (! $isReporterUser) {
                throw ValidationException::withMessages([
                    'data.status' => 'Status Closed hanya dapat dipilih oleh user yang membuat laporan bug ini.',
                ]);
            }

            if (! in_array($currentStatus, [BugStatus::Resolved, BugStatus::Closed], true)) {
                throw ValidationException::withMessages([
                    'data.status' => 'Laporan bug hanya bisa ditutup setelah user yang ditugaskan menyatakannya Resolved terlebih dahulu.',
                ]);
            }

            if (blank($record->resolved_at) && blank($data['resolved_at'] ?? null)) {
                throw ValidationException::withMessages([
                    'data.closed_at' => 'Bug belum bisa ditutup karena waktu resolved dari user yang ditugaskan belum tersedia.',
                ]);
            }
        }

        $data['resolution_note'] = $record->resolution_note;
        $data['resolved_at'] = $record->resolved_at;

        if (! $isReporterUser) {
            $data['closed_at'] = $record->closed_at;
        }

        if ($isReporterUser && $requestedStatus === BugStatus::Closed) {
            $data['closed_at'] = $data['closed_at'] ?? $record->closed_at ?? now();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function repairFormDefaults(BugReport $record): array
    {
        $defaultStatus = in_array($record->status, [BugStatus::Resolved, BugStatus::Rejected], true)
            ? $record->status->value
            : BugStatus::Resolved->value;

        return [
            'resolution_status' => $defaultStatus,
            'resolution_note' => $record->resolution_note,
            'resolved_at' => $record->resolved_at ?? now(),
        ];
    }
}
