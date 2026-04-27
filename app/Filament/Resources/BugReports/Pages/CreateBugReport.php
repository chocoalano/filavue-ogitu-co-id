<?php

namespace App\Filament\Resources\BugReports\Pages;

use App\Enums\BugReporterType;
use App\Enums\BugStatus;
use App\Filament\Resources\BugReports\BugReportResource;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Models\BugReport;
use App\Services\Telegram\BugReportTelegramNotificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateBugReport extends CreateRecord
{
    protected static string $resource = BugReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BugReportResource::makeTelegramTestAction(),
            Action::make('autofillTesting')
                ->label('Autofill Pengujian')
                ->icon('heroicon-m-beaker')
                ->color('gray')
                ->action(function (): void {
                    $this->form->fill(BugReportForm::testingAutofillData());

                    Notification::make()
                        ->success()
                        ->title('Form berhasil diisi dengan data contoh pengujian.')
                        ->send();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($duplicateReport = BugReportForm::findDuplicateTitleReport((string) ($data['title'] ?? ''))) {
            throw ValidationException::withMessages([
                'data.title' => BugReportForm::duplicateTitleMessage($duplicateReport),
            ]);
        }

        $data['status'] = BugStatus::Open->value;
        $data['resolution_note'] = null;
        $data['resolved_at'] = null;
        $data['closed_at'] = null;

        if (
            ($data['reporter_type'] ?? null) === BugReporterType::User->value
            && blank($data['reporter_id'])
        ) {
            $data['reporter_id'] = BugReportForm::currentUserId();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof BugReport) {
            return;
        }

        /** @var BugReportTelegramNotificationService $telegramService */
        $telegramService = app(BugReportTelegramNotificationService::class);

        if (! $telegramService->isConfigured()) {
            Notification::make()
                ->warning()
                ->title('Laporan berhasil dibuat, tetapi notifikasi Telegram belum dikirim.')
                ->body($telegramService->configurationErrorMessage())
                ->persistent()
                ->send();

            return;
        }

        try {
            $telegramService->sendCreatedNotification($this->record);
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->warning()
                ->title('Laporan berhasil dibuat, tetapi pengiriman Telegram gagal.')
                ->body('Periksa chat ID, token bot Telegram, atau koneksi server ke Telegram.')
                ->persistent()
                ->send();
        }
    }
}
