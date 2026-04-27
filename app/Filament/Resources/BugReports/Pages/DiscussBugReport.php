<?php

namespace App\Filament\Resources\BugReports\Pages;

use App\Enums\BugCommentType;
use App\Enums\BugReporterType;
use App\Enums\BugStatus;
use App\Filament\Resources\BugReports\BugReportResource;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Models\BugReport;
use App\Models\BugReportComment;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class DiscussBugReport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BugReportResource::class;

    protected string $view = 'filament.resources.bug-reports.pages.discuss-bug-report';

    protected static ?string $navigationLabel = 'Percakapan';

    protected static ?string $breadcrumb = 'Percakapan';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?string $messageBody = null;

    public int $lastSeenCommentId = 0;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
        $this->loadRecordRelations();

        $this->lastSeenCommentId = BugReportComment::query()
            ->whereBelongsTo($this->bugReport())
            ->where('type', BugCommentType::Comment->value)
            ->latest('id')
            ->value('id') ?? 0;
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        if (! ($parameters['record'] ?? null) instanceof BugReport) {
            return false;
        }

        return parent::shouldRegisterNavigation($parameters)
            && static::getResource()::canView($parameters['record']);
    }

    public function getSubNavigation(): array
    {
        return [];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function pollMessages(): void
    {
        $latestId = BugReportComment::query()
            ->whereBelongsTo($this->bugReport())
            ->where('type', BugCommentType::Comment->value)
            ->latest('id')
            ->value('id') ?? 0;

        if ($latestId > $this->lastSeenCommentId) {
            $this->lastSeenCommentId = $latestId;
            $this->dispatch('bug-chat-scroll');
        }
    }

    public function sendMessage(): void
    {
        if (! $this->canSendMessage()) {
            Notification::make()
                ->warning()
                ->title('Pesan belum dapat dikirim.')
                ->body($this->composerHelperText())
                ->send();

            return;
        }

        $this->messageBody = trim((string) $this->messageBody);

        $validated = $this->validate(
            [
                'messageBody' => ['required', 'string', 'max:5000'],
            ],
            [
                'messageBody.required' => 'Pesan chat wajib diisi agar pihak lain mengetahui informasi yang ingin Anda sampaikan.',
                'messageBody.max' => 'Pesan chat terlalu panjang. Ringkas isi pesan maksimal 5000 karakter.',
            ]
        );

        $comment = $this->bugReport()->comments()->create([
            'user_id' => BugReportForm::currentUserId(),
            'type' => BugCommentType::Comment,
            'body' => trim((string) $validated['messageBody']),
        ]);

        $this->lastSeenCommentId = $comment->id;
        $this->messageBody = null;
        $this->resetValidation('messageBody');
        $this->loadRecordRelations();
        $this->dispatch('bug-chat-scroll');
    }

    /**
     * @return EloquentCollection<int, BugReportComment>
     */
    public function getChatComments(): EloquentCollection
    {
        return BugReportComment::query()
            ->whereBelongsTo($this->bugReport())
            ->where('type', BugCommentType::Comment->value)
            ->orderBy('created_at')
            ->orderBy('id')
            ->with('author')
            ->get();
    }

    public function canSendMessage(): bool
    {
        $record = $this->bugReport();

        if (! $this->supportsTwoWayChat()) {
            return false;
        }

        if ($record->status === BugStatus::Closed) {
            return false;
        }

        // reporter_type=user tapi reporter_id null → laporan dari user internal tanpa identitas spesifik,
        // siapapun yang login ke panel boleh berkomentar.
        if ($record->reporter_type === BugReporterType::User && ! filled($record->reporter_id)) {
            return filled(BugReportForm::currentUserId());
        }

        return BugReportForm::isReporterUser($record) || BugReportForm::isAssignedUser($record);
    }

    public function composerHelperText(): string
    {
        $record = $this->bugReport();

        if (! $this->supportsTwoWayChat()) {
            return 'Chat dua arah baru tersedia jika pelapor berasal dari user internal yang bisa login ke panel.';
        }

        if ($record->status === BugStatus::Closed) {
            return 'Percakapan dikunci karena laporan ini sudah dikonfirmasi selesai dan berstatus Closed.';
        }

        if ($record->reporter_type === BugReporterType::User && ! filled($record->reporter_id)) {
            return 'Gunakan chat ini untuk klarifikasi, update singkat, atau konfirmasi hasil perbaikan. Tekan Enter untuk kirim lebih cepat.';
        }

        if (! filled($record->assigned_to)) {
            return 'Laporan ini belum ditugaskan. Pelapor masih dapat meninggalkan pesan, dan user yang ditugaskan dapat membalas setelah penugasan dilakukan.';
        }

        if (! (BugReportForm::isReporterUser($record) || BugReportForm::isAssignedUser($record))) {
            return 'Hanya pelapor dan user yang sedang ditugaskan yang dapat mengirim pesan di halaman ini.';
        }

        return 'Gunakan chat ini untuk klarifikasi, update singkat, atau konfirmasi hasil perbaikan. Tekan Enter untuk kirim lebih cepat.';
    }

    public function reporterDisplayName(): string
    {
        $record = $this->bugReport();

        return (string) (
            $record->reporterUser?->name
            ?? $record->reporterCustomer?->name
            ?? $record->reporter_name
            ?? $record->reporter_email
            ?? 'Pelapor tidak diketahui'
        );
    }

    public function assigneeDisplayName(): string
    {
        return (string) ($this->bugReport()->assignee?->name ?? 'Belum ditugaskan');
    }

    public function senderName(BugReportComment $comment): string
    {
        return (string) ($comment->author?->name ?? 'Pengguna tidak diketahui');
    }

    public function senderInitials(BugReportComment $comment): string
    {
        $name = $this->senderName($comment);

        $initials = Str::of($name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => Str::upper(Str::substr($segment, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : '??';
    }

    public function canPinMessage(): bool
    {
        return BugReportForm::isAssignedUser($this->bugReport());
    }

    public function togglePinComment(int $commentId): void
    {
        if (! $this->canPinMessage()) {
            return;
        }

        $comment = BugReportComment::query()
            ->whereBelongsTo($this->bugReport())
            ->where('type', BugCommentType::Comment->value)
            ->find($commentId);

        if (! $comment) {
            return;
        }

        $comment->update(['is_pinned' => ! $comment->is_pinned]);
    }

    public function senderRole(BugReportComment $comment): string
    {
        $record = $this->bugReport();

        if ((int) $comment->user_id === (int) $record->reporter_id) {
            return 'Pelapor';
        }

        if ((int) $comment->user_id === (int) $record->assigned_to) {
            return 'Penanggung Jawab';
        }

        return 'Tim Internal';
    }

    public function isOwnMessage(BugReportComment $comment): bool
    {
        return (int) $comment->user_id === BugReportForm::currentUserId();
    }

    public function supportsTwoWayChat(): bool
    {
        return $this->bugReport()->reporter_type === BugReporterType::User;
    }

    protected function bugReport(): BugReport
    {
        /** @var BugReport $record */
        $record = $this->getRecord();

        return $record;
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canView($this->bugReport()), 403);
    }

    protected function loadRecordRelations(): void
    {
        $this->bugReport()->loadMissing([
            'reporterUser',
            'reporterCustomer',
            'assignee',
        ]);
    }
}
