<?php

namespace App\Filament\Resources\BugReports;

use App\Filament\Resources\BugReports\Pages\CreateBugReport;
use App\Filament\Resources\BugReports\Pages\DiscussBugReport;
use App\Filament\Resources\BugReports\Pages\EditBugReport;
use App\Filament\Resources\BugReports\Pages\ListBugReports;
use App\Filament\Resources\BugReports\Pages\ViewBugReport;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Filament\Resources\BugReports\Schemas\BugReportInfolist;
use App\Filament\Resources\BugReports\Tables\BugReportsTable;
use App\Models\BugReport;
use App\Services\Telegram\BugReportTelegramNotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Throwable;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Laporan Bug';

    protected static ?string $modelLabel = 'Laporan Bug';

    protected static ?string $pluralModelLabel = 'Laporan Bug';

    public static function form(Schema $schema): Schema
    {
        return BugReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BugReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BugReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function makeAutoResolveCallout(): Callout
    {
        return Callout::make('Aturan Auto-Resolve Laporan Bug')
            ->description('Laporan akan otomatis diubah ke status Resolved oleh sistem dalam dua kondisi: (1) tidak ada komentar sama sekali selama 1 minggu sejak laporan dibuat, atau (2) ada komentar tetapi tidak ada tanggapan baru selama 3 hari sejak komentar terakhir. Tambahkan komentar dengan intruksi yang lengkap dan jelas untuk membantu proses perbaikan agar terus berlanjut hingga hasil yang diharapkan menjadi sesuai.')
            ->warning()
            ->icon('heroicon-o-clock')
            ->columnSpanFull();
    }

    public static function makeTelegramTestAction(): Action
    {
        return Action::make('testTelegram')
            ->label('Test Telegram')
            ->icon('heroicon-o-paper-airplane')
            ->color('gray')
            ->visible(fn (): bool => (bool) config('app.debug'))
            ->requiresConfirmation()
            ->modalHeading('Kirim Test Telegram')
            ->modalDescription('Gunakan tombol ini untuk menguji apakah notifikasi Telegram laporan bug dapat terkirim ke chat yang sudah dikonfigurasi.')
            ->action(function (): void {
                /** @var BugReportTelegramNotificationService $telegramService */
                $telegramService = app(BugReportTelegramNotificationService::class);

                if (! $telegramService->isConfigured()) {
                    Notification::make()
                        ->warning()
                        ->title('Test Telegram belum dapat dikirim.')
                        ->body($telegramService->configurationErrorMessage())
                        ->persistent()
                        ->send();

                    return;
                }

                try {
                    $senderName = Filament::auth()->user()?->name;

                    $telegramService->sendTestNotification(filled($senderName) ? (string) $senderName : null);

                    Notification::make()
                        ->success()
                        ->title('Pesan test Telegram berhasil dikirim.')
                        ->body('Periksa chat Telegram laporan bug untuk memastikan pesan sudah masuk.')
                        ->send();
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->danger()
                        ->title('Pesan test Telegram gagal dikirim.')
                        ->body($exception->getMessage())
                        ->persistent()
                        ->send();
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBugReports::route('/'),
            'create' => CreateBugReport::route('/create'),
            'view' => ViewBugReport::route('/{record}'),
            'chat' => DiscussBugReport::route('/{record}/chat'),
            'edit' => EditBugReport::route('/{record}/edit'),
        ];
    }
}
