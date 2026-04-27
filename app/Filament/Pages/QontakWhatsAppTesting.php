<?php

namespace App\Filament\Pages;

use App\Models\CustomerWalletTransaction;
use App\Services\QontactService;
use App\Support\QontakWhatsAppSettings as QontakWhatsAppSettingsStore;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use RuntimeException;
use UnitEnum;

class QontakWhatsAppTesting extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.qontak-whats-app-testing';

    protected static ?string $title = 'Testing WhatsApp Qontak';

    protected ?string $subheading = 'Uji kirim notifikasi WhatsApp ke nomor tujuan menggunakan template approval, rejection, atau template custom.';

    protected static ?string $navigationLabel = 'Testing WA';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-m-paper-airplane';

    public ?array $data = [];

    /**
     * @var array<string, string>|null
     */
    protected ?array $integrationOptions = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $templateOptions = null;

    public function mount(): void
    {
        $this->form->fill($this->defaultFormState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mode Test')
                    ->description('Pilih jenis notifikasi yang ingin diuji.')
                    ->schema([
                        Select::make('mode')
                            ->label('Jenis Pengujian')
                            ->options([
                                'withdrawal_approved' => 'Notifikasi Withdrawal Approved',
                                'withdrawal_rejected' => 'Notifikasi Withdrawal Rejected',
                                'custom_template' => 'Template Custom',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                    ]),
                Section::make('Sumber Data Penarikan E-Wallet')
                    ->description('Pilih data dari list Penarikan E-Wallet sebagai sumber parameter template. Nama dan nomor penerima akan terisi otomatis, tetapi masih bisa dioverride.')
                    ->visible(fn (Get $get): bool => \in_array($get('mode'), ['withdrawal_approved', 'withdrawal_rejected'], true))
                    ->schema([
                        Select::make('withdrawal_transaction_id')
                            ->label('Testing List Penarikan E-Wallet')
                            ->options(fn (): array => $this->getWithdrawalTestingOptions())
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->placeholder('Pilih transaksi penarikan...')
                            ->helperText('Menampilkan 100 transaksi penarikan terbaru untuk kebutuhan testing.')
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $this->fillRecipientFromWithdrawal($set, $state);
                            })
                            ->required(fn (Get $get): bool => \in_array($get('mode'), ['withdrawal_approved', 'withdrawal_rejected'], true))
                            ->columnSpanFull(),
                    ]),
                Section::make('Penerima')
                    ->description('Nomor tujuan akan dinormalisasi ke format 62xxxx sebelum dikirim ke Qontak. Untuk mode withdrawal, field ini otomatis terisi dari transaksi yang dipilih.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('phone_number')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->tel()
                            ->maxLength(30),
                        Select::make('channel_integration_id')
                            ->label('Override Channel Integration ID')
                            ->helperText('Kosongkan untuk memakai channel integration default yang tersimpan di pengaturan.')
                            ->options(fn (): array => $this->getIntegrationOptions())
                            ->searchable()
                            ->native(false)
                            ->placeholder('Gunakan default channel integration')
                            ->columnSpanFull(),
                    ]),
                Section::make('Parameter Approval')
                    ->description('Preview parameter diambil dari mapping template approval yang tersimpan pada halaman konfigurasi.')
                    ->visible(fn (Get $get): bool => $get('mode') === 'withdrawal_approved')
                    ->schema([
                        Placeholder::make('approval_template')
                            ->label('Template Approval Aktif')
                            ->content(fn (): string => (string) QontakWhatsAppSettingsStore::get(
                                'notifications.withdrawal_approved.template_id',
                                '-'
                            ) ?: '-')
                            ->columnSpanFull(),
                        Placeholder::make('approval_preview')
                            ->label('Preview Body Params')
                            ->content(fn (Get $get): string => $this->buildWithdrawalPreview('withdrawal_approved', $get))
                            ->columnSpanFull(),
                    ]),
                Section::make('Parameter Penolakan')
                    ->description('Preview parameter diambil dari mapping template penolakan yang tersimpan pada halaman konfigurasi.')
                    ->visible(fn (Get $get): bool => $get('mode') === 'withdrawal_rejected')
                    ->schema([
                        Placeholder::make('rejected_template')
                            ->label('Template Penolakan Aktif')
                            ->content(fn (): string => (string) QontakWhatsAppSettingsStore::get(
                                'notifications.withdrawal_rejected.template_id',
                                '-'
                            ) ?: '-')
                            ->columnSpanFull(),
                        Placeholder::make('rejected_preview')
                            ->label('Preview Body Params')
                            ->content(fn (Get $get): string => $this->buildWithdrawalPreview('withdrawal_rejected', $get))
                            ->columnSpanFull(),
                    ]),
                Section::make('Template Custom')
                    ->description('Uji template bebas menggunakan template ID dan parameter body sesuai urutan variabel template Qontak.')
                    ->visible(fn (Get $get): bool => $get('mode') === 'custom_template')
                    ->schema([
                        Select::make('custom.template_id')
                            ->label('Template ID')
                            ->required()
                            ->options(fn (): array => $this->getTemplateOptions())
                            ->searchable()
                            ->native(false),
                        TextInput::make('custom.header_image_url')
                            ->label('Header Image URL')
                            ->url()
                            ->maxLength(255),
                        Repeater::make('custom.body_params')
                            ->label('Body Params')
                            ->schema([
                                TextInput::make('value_text')
                                    ->label('Nilai Parameter')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Parameter')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $qontactService = app(QontactService::class);
        $mode = (string) ($data['mode'] ?? 'withdrawal_approved');
        $recipientName = trim((string) ($data['recipient_name'] ?? '')) ?: 'Tester Admin';
        $rawPhoneNumber = trim((string) ($data['phone_number'] ?? ''));
        $normalizedPhoneNumber = $qontactService->normalizePhoneNumber($rawPhoneNumber);
        $channelIntegrationId = trim((string) ($data['channel_integration_id'] ?? '')) ?: null;

        if ($normalizedPhoneNumber === '') {
            Notification::make()
                ->danger()
                ->title('Nomor WhatsApp tidak valid')
                ->body('Isi nomor tujuan dengan format 08xxxx atau 62xxxx.')
                ->send();

            return;
        }

        try {
            if ($mode === 'custom_template') {
                $result = $this->sendCustomTemplateTest(
                    $qontactService,
                    $recipientName,
                    $normalizedPhoneNumber,
                    $data,
                    $channelIntegrationId
                );

                if (! (bool) ($result['success'] ?? false)) {
                    throw new RuntimeException(trim((string) ($result['error'] ?? 'Pengiriman pesan test WhatsApp gagal.')));
                }
            } else {
                $sent = $mode === 'withdrawal_rejected'
                    ? $this->sendWithdrawalRejectedTest($qontactService, $recipientName, $normalizedPhoneNumber, $data, $channelIntegrationId)
                    : $this->sendWithdrawalApprovedTest($qontactService, $recipientName, $normalizedPhoneNumber, $data, $channelIntegrationId);

                if (! $sent) {
                    throw new RuntimeException('Pengiriman pesan test WhatsApp gagal.');
                }
            }

            Notification::make()
                ->success()
                ->title('Pesan test berhasil dikirim')
                ->body("Pesan berhasil dikirim ke {$normalizedPhoneNumber}.")
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Pengiriman pesan test gagal')
                ->body($exception->getMessage())
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->form->fill($this->defaultFormState());

        Notification::make()
            ->info()
            ->title('Di-reset')
            ->body('Form testing WhatsApp dikembalikan ke nilai default.')
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormState(): array
    {
        return [
            'mode' => 'withdrawal_approved',
            'withdrawal_transaction_id' => null,
            'recipient_name' => 'Tester Admin',
            'phone_number' => '',
            'channel_integration_id' => (string) QontakWhatsAppSettingsStore::get('connection.channel_integration_id', ''),
            'custom' => [
                'template_id' => (string) QontakWhatsAppSettingsStore::get('broadcast.default_template_id', ''),
                'header_image_url' => (string) QontakWhatsAppSettingsStore::get('broadcast.header_image_url', ''),
                'body_params' => [],
            ],
        ];
    }

    private function sendWithdrawalApprovedTest(
        QontactService $qontactService,
        string $recipientName,
        string $phoneNumber,
        array $data,
        ?string $channelIntegrationId,
    ): bool {
        $templateId = trim((string) QontakWhatsAppSettingsStore::get('notifications.withdrawal_approved.template_id', ''));

        if ($templateId === '') {
            throw new RuntimeException('Template approval withdrawal belum diatur pada halaman konfigurasi WhatsApp Qontak.');
        }

        $transaction = $this->findSelectedWithdrawalTransaction($data);

        return $qontactService->sendWithdrawalApprovedNotification(
            $transaction,
            $phoneNumber,
            $recipientName,
            $channelIntegrationId,
        );
    }

    private function sendWithdrawalRejectedTest(
        QontactService $qontactService,
        string $recipientName,
        string $phoneNumber,
        array $data,
        ?string $channelIntegrationId,
    ): bool {
        $templateId = trim((string) QontakWhatsAppSettingsStore::get('notifications.withdrawal_rejected.template_id', ''));

        if ($templateId === '') {
            throw new RuntimeException('Template penolakan withdrawal belum diatur pada halaman konfigurasi WhatsApp Qontak.');
        }

        $transaction = $this->findSelectedWithdrawalTransaction($data);

        return $qontactService->sendWithdrawalRejectedNotification(
            $transaction,
            $phoneNumber,
            $recipientName,
            $channelIntegrationId,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    private function sendCustomTemplateTest(
        QontactService $qontactService,
        string $recipientName,
        string $phoneNumber,
        array $data,
        ?string $channelIntegrationId,
    ): array {
        $templateId = trim((string) ($data['custom']['template_id'] ?? ''));

        if ($templateId === '') {
            throw new RuntimeException('Template ID custom wajib diisi untuk test WhatsApp.');
        }

        $bodyParams = collect((array) ($data['custom']['body_params'] ?? []))
            ->map(static fn (mixed $item): string => trim((string) (is_array($item) ? ($item['value_text'] ?? '') : '')))
            ->filter(static fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        return $qontactService->sendWhatsAppWithResultFromParams(
            $recipientName,
            $phoneNumber,
            $templateId,
            $bodyParams,
            'id',
            $this->nullableString($data['custom']['header_image_url'] ?? null),
            null,
            $channelIntegrationId,
        );
    }

    /**
     * @return array<string, string>
     */
    private function getWithdrawalTestingOptions(): array
    {
        return CustomerWalletTransaction::query()
            ->with('customer:id,name,phone')
            ->where('type', 'withdrawal')
            ->latest('id')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (CustomerWalletTransaction $transaction): array => [
                (string) $transaction->getKey() => $this->formatWithdrawalOptionLabel($transaction),
            ])
            ->all();
    }

    private function formatWithdrawalOptionLabel(CustomerWalletTransaction $transaction): string
    {
        $customerName = trim((string) ($transaction->customer?->name ?? 'Tanpa Nama'));
        $amount = number_format((int) round((float) ($transaction->amount ?? 0)), 0, ',', '.');
        $status = trim((string) ($transaction->status ?? '-'));
        $reference = trim((string) ($transaction->transaction_ref ?? ''));

        if ($reference !== '') {
            return "#{$transaction->id} — {$reference} — {$customerName} — Rp {$amount} — {$status}";
        }

        return "#{$transaction->id} — {$customerName} — Rp {$amount} — {$status}";
    }

    private function fillRecipientFromWithdrawal(Set $set, ?string $transactionId): void
    {
        $transaction = $this->findWithdrawalTransaction($transactionId);

        if (! $transaction) {
            return;
        }

        $set('recipient_name', trim((string) ($transaction->customer?->name ?? 'Tester Admin')) ?: 'Tester Admin');
        $set('phone_number', trim((string) ($transaction->customer?->phone ?? '')));
    }

    private function buildWithdrawalPreview(string $notificationKey, Get $get): string
    {
        $transaction = $this->findWithdrawalTransaction($get('withdrawal_transaction_id'));

        if (! $transaction) {
            return 'Pilih data Penarikan E-Wallet untuk melihat preview parameter.';
        }

        $params = app(QontactService::class)->buildWithdrawalNotificationBodyParams(
            $notificationKey,
            $transaction,
            trim((string) ($get('recipient_name') ?? '')),
        );

        if ($params === []) {
            return 'Template belum memiliki parameter body atau mapping belum tersedia.';
        }

        return collect($params)
            ->map(static function (array $param): string {
                $resolvedValue = trim((string) ($param['value_text'] ?? ''));

                return sprintf(
                    '{{%s}} %s = %s',
                    (string) ($param['key'] ?? '-'),
                    (string) ($param['value'] ?? '-'),
                    $resolvedValue !== '' ? $resolvedValue : '(kosong)'
                );
            })
            ->implode(' | ');
    }

    private function findSelectedWithdrawalTransaction(array $data): CustomerWalletTransaction
    {
        $transaction = $this->findWithdrawalTransaction($data['withdrawal_transaction_id'] ?? null);

        if (! $transaction) {
            throw new RuntimeException('Pilih data dari list Penarikan E-Wallet terlebih dahulu.');
        }

        return $transaction;
    }

    private function findWithdrawalTransaction(mixed $transactionId): ?CustomerWalletTransaction
    {
        $id = (int) $transactionId;

        if ($id < 1) {
            return null;
        }

        return CustomerWalletTransaction::query()
            ->with('customer')
            ->where('type', 'withdrawal')
            ->find($id);
    }

    /**
     * @return array<string, string>
     */
    private function getIntegrationOptions(): array
    {
        if ($this->integrationOptions !== null) {
            return $this->integrationOptions;
        }

        return $this->integrationOptions = app(QontactService::class)->getWhatsAppIntegrations();
    }

    /**
     * @return array<string, string>
     */
    private function getTemplateOptions(): array
    {
        if ($this->templateOptions !== null) {
            return $this->templateOptions;
        }

        return $this->templateOptions = app(QontactService::class)->getWhatsAppTemplates();
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
