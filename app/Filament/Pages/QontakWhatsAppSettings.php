<?php

namespace App\Filament\Pages;

use App\Services\QontactService;
use App\Support\QontakWhatsAppSettings as QontakWhatsAppSettingsStore;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class QontakWhatsAppSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.qontak-whats-app-settings';

    protected static ?string $title = 'Pengaturan WhatsApp Qontak';

    protected ?string $subheading = 'Kelola koneksi Qontak, template notifikasi WhatsApp, dan default pengiriman bulk/test.';

    protected static ?string $navigationLabel = 'WA Qontak';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-m-chat-bubble-left-right';

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
        $this->form->fill($this->getStoredState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('qontak-whatsapp-settings-tabs')
                    ->id('qontak-whatsapp-settings-tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Koneksi')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Section::make('Kredensial Qontak')
                                    ->description('Konfigurasi koneksi utama untuk seluruh pengiriman WhatsApp melalui Qontak.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('connection.base_url')
                                            ->label('Base URL API')
                                            ->required()
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Select::make('connection.channel_integration_id')
                                            ->label('Default Channel Integration ID')
                                            ->helperText('Dipakai sebagai channel default untuk notifikasi, broadcast, dan halaman test jika tidak dioverride.')
                                            ->options(fn (): array => $this->getIntegrationOptions())
                                            ->searchable()
                                            ->native(false)
                                            ->placeholder('Pilih channel integration...')
                                            ->columnSpanFull(),
                                        TextInput::make('connection.timeout')
                                            ->label('HTTP Timeout (detik)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(300),
                                        TextEntry::make('api_token_status')
                                            ->label('Status API Token')
                                            ->state(fn (): string => QontakWhatsAppSettingsStore::hasConfiguredApiToken()
                                                ? 'API token sudah tersimpan. Kosongkan field token jika tidak ingin mengganti.'
                                                : 'API token belum tersimpan. Isi token baru untuk mulai menggunakan Qontak.'),
                                        TextInput::make('connection.api_token')
                                            ->label('API Token')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(2048)
                                            ->helperText('Kosongkan jika ingin mempertahankan token yang saat ini tersimpan.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Notifikasi')
                            ->icon('heroicon-m-bell-alert')
                            ->schema([
                                Section::make('Withdrawal Approved')
                                    ->description('Template dan flag aktif untuk notifikasi saat withdrawal disetujui.')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('notifications.withdrawal_approved.enabled')
                                            ->label('Aktifkan notifikasi approval')
                                            ->default(true)
                                            ->columnSpanFull(),
                                        Select::make('notifications.withdrawal_approved.template_id')
                                            ->label('Template ID Approval')
                                            ->options(fn (): array => $this->getTemplateOptions())
                                            ->searchable()
                                            ->native(false)
                                            ->live()
                                            ->placeholder('Pilih template approval...')
                                            ->helperText('Daftar template diambil langsung dari API Qontak `/v1/templates/whatsapp`.')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                $this->synchronizeTemplateMappings('withdrawal_approved', $set, $get, $state);
                                            })
                                            ->columnSpanFull(),
                                        TextInput::make('notifications.withdrawal_approved.header_image_url')
                                            ->label('Header Image URL Approval')
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Repeater::make('notifications.withdrawal_approved.parameters')
                                            ->label('Mapping Parameter Template Approval')
                                            ->helperText('Baris dibuat mengikuti parameter template. Pilih source table dan source column untuk parsing data dari list Penarikan E-Wallet.')
                                            ->schema($this->parameterRepeaterSchema())
                                            ->columns(2)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Withdrawal Rejected')
                                    ->description('Template dan flag aktif untuk notifikasi saat withdrawal ditolak.')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('notifications.withdrawal_rejected.enabled')
                                            ->label('Aktifkan notifikasi penolakan')
                                            ->default(true)
                                            ->columnSpanFull(),
                                        Select::make('notifications.withdrawal_rejected.template_id')
                                            ->label('Template ID Penolakan')
                                            ->options(fn (): array => $this->getTemplateOptions())
                                            ->searchable()
                                            ->native(false)
                                            ->live()
                                            ->placeholder('Pilih template penolakan...')
                                            ->helperText('Daftar template diambil langsung dari API Qontak `/v1/templates/whatsapp`.')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                $this->synchronizeTemplateMappings('withdrawal_rejected', $set, $get, $state);
                                            })
                                            ->columnSpanFull(),
                                        Repeater::make('notifications.withdrawal_rejected.parameters')
                                            ->label('Mapping Parameter Template Penolakan')
                                            ->helperText('Source value memakai table/column yang dipilih dari data Penarikan E-Wallet.')
                                            ->schema($this->parameterRepeaterSchema())
                                            ->columns(2)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Bulk & Test')
                            ->icon('heroicon-m-paper-airplane')
                            ->schema([
                                Section::make('Default Broadcast dan Test Message')
                                    ->description('Dipakai sebagai default di halaman broadcast dan pengujian WhatsApp.')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('broadcast.default_template_id')
                                            ->label('Default Template ID Broadcast/Test')
                                            ->options(fn (): array => $this->getTemplateOptions())
                                            ->searchable()
                                            ->native(false)
                                            ->placeholder('Pilih template default...')
                                            ->columnSpanFull(),
                                        TextInput::make('broadcast.header_image_url')
                                            ->label('Default Header Image URL')
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('broadcast.bulk_retry_attempts')
                                            ->label('Retry Attempt Bulk')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(10),
                                        TextInput::make('broadcast.bulk_retry_buffer_seconds')
                                            ->label('Retry Buffer (detik)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->maxValue(300),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        QontakWhatsAppSettingsStore::writeState($this->form->getState());

        Notification::make()
            ->success()
            ->title('Tersimpan')
            ->body('Pengaturan WhatsApp Qontak berhasil diperbarui.')
            ->send();

        $this->form->fill($this->getStoredState());
    }

    public function resetForm(): void
    {
        $this->form->fill($this->getStoredState());

        Notification::make()
            ->info()
            ->title('Di-reset')
            ->body('Form dikembalikan ke konfigurasi WhatsApp Qontak yang terakhir tersimpan.')
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStoredState(): array
    {
        $state = QontakWhatsAppSettingsStore::getState();
        $qontactService = app(QontactService::class);
        $state['connection']['api_token'] = '';
        $state['notifications']['withdrawal_approved']['parameters'] = $qontactService->syncWithdrawalTemplateMappings(
            'withdrawal_approved',
            (string) data_get($state, 'notifications.withdrawal_approved.template_id', ''),
            (array) data_get($state, 'notifications.withdrawal_approved.parameters', []),
        );
        $state['notifications']['withdrawal_rejected']['parameters'] = $qontactService->syncWithdrawalTemplateMappings(
            'withdrawal_rejected',
            (string) data_get($state, 'notifications.withdrawal_rejected.template_id', ''),
            (array) data_get($state, 'notifications.withdrawal_rejected.parameters', []),
        );

        return $state;
    }

    /**
     * @return array<int, mixed>
     */
    protected function parameterRepeaterSchema(): array
    {
        return [
            TextInput::make('key')
                ->label('Key')
                ->readOnly()
                ->required(),
            TextInput::make('value')
                ->label('Variabel Template')
                ->readOnly()
                ->required(),
            Select::make('source_table')
                ->label('Source Table')
                ->options(fn (): array => app(QontactService::class)->getWithdrawalTemplateSourceTables())
                ->searchable()
                ->native(false)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('source_column', null);
                }),
            Select::make('source_column')
                ->label('Source Column')
                ->options(fn (Get $get): array => app(QontactService::class)->getWithdrawalTemplateSourceColumns(
                    filled($get('source_table')) ? (string) $get('source_table') : null
                ))
                ->searchable()
                ->native(false)
                ->required()
                ->disabled(fn (Get $get): bool => blank($get('source_table'))),
        ];
    }

    private function synchronizeTemplateMappings(
        string $notificationKey,
        Set $set,
        Get $get,
        ?string $templateId,
    ): void {
        $set(
            "notifications.{$notificationKey}.parameters",
            app(QontactService::class)->syncWithdrawalTemplateMappings(
                $notificationKey,
                (string) ($templateId ?? ''),
                (array) $get("notifications.{$notificationKey}.parameters"),
            ),
        );
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
}
