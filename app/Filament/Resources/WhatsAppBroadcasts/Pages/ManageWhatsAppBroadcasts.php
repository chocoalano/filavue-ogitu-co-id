<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Pages;

use App\Filament\Resources\WhatsAppBroadcasts\WhatsAppBroadcastResource;
use App\Filament\Widgets\WhatsAppBroadcasts\CustomerWhatsAppConfirmationWidget;
use App\Filament\Widgets\WhatsAppBroadcasts\WhatsAppOutboundLogWidget;
use App\Models\WhatsAppBroadcast;
use App\Services\QontactService;
use App\Services\WhatsApp\WhatsAppBroadcastService;
use App\Support\QontakWhatsAppSettings;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ManageWhatsAppBroadcasts extends ManageRecords
{
    protected static string $resource = WhatsAppBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_wa')
                ->label('Test Kirim Pesan Whatsapp')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->modalHeading('Test Pengiriman WhatsApp')
                ->modalDescription('Gunakan untuk menguji koneksi gateway Qontak dan validasi template sebelum broadcast massal.')
                ->modalSubmitActionLabel('Kirim ke Semua Nomor')
                ->schema([
                    Select::make('channel_integration_id')
                        ->label('Channel Integration')
                        ->required()
                        ->searchable()
                        ->options(fn (): array => app(QontactService::class)->getWhatsAppIntegrations())
                        ->placeholder('Pilih channel integration...')
                        ->default((string) QontakWhatsAppSettings::get('connection.channel_integration_id', ''))
                        ->helperText('Channel WhatsApp Business yang akan digunakan untuk mengirim pesan test ini.')
                        ->columnSpanFull(),

                    Select::make('template_id')
                        ->label('Template WhatsApp Qontak')
                        ->required()
                        ->searchable()
                        ->live()
                        ->options(fn (): array => app(QontactService::class)->getWhatsAppTemplates())
                        ->placeholder('Pilih template...')
                        ->default((string) QontakWhatsAppSettings::get('broadcast.default_template_id', config('services.qontak.broadcast_template_id', '')))
                        ->helperText('Pilih template yang sudah disetujui (Approved) di Qontak. [N var] menunjukkan jumlah variabel.')
                        ->columnSpanFull()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if (! $state) {
                                $set('body_params', []);

                                return;
                            }

                            $params = app(QontactService::class)->getWhatsAppTemplateParams($state);
                            if ($params === []) {
                                $set('body_params', []);

                                return;
                            }

                            $set('body_params', array_map(
                                fn (array $param): array => ['value' => $param['value'], 'value_text' => ''],
                                $params,
                            ));
                        }),

                    Repeater::make('body_params')
                        ->label('Parameter Body (Variabel Template)')
                        ->helperText('Kosongkan jika template tidak memiliki variabel. Nama variabel harus sesuai yang terdaftar di Qontak, contoh: full_name.')
                        ->schema([
                            TextInput::make('value')
                                ->label('Nama Variabel')
                                ->placeholder('Contoh: full_name')
                                ->required(),
                            TextInput::make('value_text')
                                ->label('Nilai')
                                ->placeholder('Contoh: Burhanudin Hakim')
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Variabel')
                        ->reorderable(false)
                        ->columnSpanFull(),

                    Repeater::make('recipients')
                        ->label('Daftar Penerima')
                        ->helperText('Tambahkan satu atau lebih nomor tujuan. Nomor duplikat akan dilewati otomatis.')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->placeholder('Tester Admin')
                                ->maxLength(120),
                            TextInput::make('phone')
                                ->label('Nomor WhatsApp')
                                ->required()
                                ->tel()
                                ->placeholder('081234567890')
                                ->helperText('Format 08xxx atau 62xxx'),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->minItems(1)
                        ->addActionLabel('Tambah Nomor')
                        ->reorderable(false)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $qontactService = app(QontactService::class);
                    $templateId = trim((string) ($data['template_id'] ?? ''));
                    $channelIntegrationId = trim((string) ($data['channel_integration_id'] ?? '')) ?: null;
                    $rawBodyParams = self::buildRawBodyParams($data['body_params'] ?? []);

                    $seenPhones = [];
                    $successList = [];
                    $failedList = [];

                    foreach (array_values((array) ($data['recipients'] ?? [])) as $recipient) {
                        $rawPhone = trim((string) ($recipient['phone'] ?? ''));
                        $name = trim((string) ($recipient['name'] ?? '')) ?: 'Tester Admin';

                        if ($rawPhone === '') {
                            continue;
                        }

                        $normalizedPhone = $qontactService->normalizePhoneNumber($rawPhone);

                        if ($normalizedPhone === '' || isset($seenPhones[$normalizedPhone])) {
                            continue;
                        }

                        $seenPhones[$normalizedPhone] = true;

                        try {
                            $result = $qontactService->sendWhatsAppWithFormattedParams(
                                $name,
                                $normalizedPhone,
                                $templateId,
                                $rawBodyParams,
                                'id',
                                [],
                                [],
                                null,
                                $channelIntegrationId,
                            );

                            if ((bool) ($result['success'] ?? false)) {
                                $successList[] = $normalizedPhone;
                            } else {
                                $error = trim((string) ($result['error'] ?? 'Gagal mengirim.'));
                                $failedList[] = "{$normalizedPhone}: {$error}";
                            }
                        } catch (\Throwable $exception) {
                            $failedList[] = "{$normalizedPhone}: {$exception->getMessage()}";
                        }
                    }

                    $total = \count($successList) + \count($failedList);

                    if ($total === 0) {
                        Notification::make()
                            ->title('Tidak ada nomor valid')
                            ->body('Tidak ada nomor penerima yang dapat diproses.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($successList !== []) {
                        Notification::make()
                            ->title(\count($successList).' pesan test berhasil terkirim')
                            ->body(implode(', ', $successList))
                            ->success()
                            ->send();
                    }

                    if ($failedList !== []) {
                        Notification::make()
                            ->title(\count($failedList).' nomor gagal dikirim')
                            ->body(implode("\n", $failedList))
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make()
                ->after(function (WhatsAppBroadcast $record): void {
                    try {
                        $record->update([
                            'status' => 'processing',
                            'total_recipients' => 0,
                            'success_recipients' => 0,
                            'failed_recipients' => 0,
                            'last_error' => null,
                            'sent_at' => null,
                        ]);

                        set_time_limit(0);
                        ignore_user_abort(true);

                        app(WhatsAppBroadcastService::class)->process((int) $record->id);

                        $record->refresh();

                        $notification = Notification::make()
                            ->body("Terkirim: {$record->success_recipients}, Gagal: {$record->failed_recipients} dari {$record->total_recipients} penerima.");

                        if ($record->status === 'failed') {
                            $notification
                                ->title('Broadcast bulk gagal dibuat')
                                ->body((string) ($record->last_error ?: 'Qontak menolak proses bulk broadcast.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($record->status === 'partial') {
                            $notification
                                ->title('Broadcast bulk diproses sebagian')
                                ->warning()
                                ->send();

                            return;
                        }

                        $notification
                            ->title('Broadcast bulk berhasil dibuat di Qontak')
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        $record->update([
                            'status' => 'failed',
                            'last_error' => $exception->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Broadcast gagal diproses')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Syarat Pengiriman Pesan WhatsApp')
                ->warning()
                ->description(
                    'Pesan broadcast/informasi lainya hanya akan terkirim ke penerima yang sebelumnya sudah pernah mengirim pesan WhatsApp ke nomor gateway yang terdaftar di Qontak. '
                    .'Ini adalah ketentuan dari WhatsApp Business API — nomor yang belum pernah memulai percakapan tidak dapat menerima pesan outgoing dari sistem. '
                    .'Pastikan penerima sudah terdaftar di tab "Nomor WA Terkonfirmasi" sebelum menjalankan broadcast.'
                ),

            Tabs::make()
                ->contained(false)
                ->tabs([
                    Tab::make('WA Broadcasts')
                        ->icon('bi-whatsapp')
                        ->schema([
                            $this->getTabsContentComponent(),
                            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                            EmbeddedTable::make(),
                            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
                        ]),

                    Tab::make('Nomor WA Terkonfirmasi')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Livewire::make(CustomerWhatsAppConfirmationWidget::class),
                        ]),

                    Tab::make('Log Outbound')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->schema([
                            Livewire::make(WhatsAppOutboundLogWidget::class),
                        ]),
                ]),
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $bodyParamsInput
     * @return list<array{key: string, value: string, value_text: string}>
     */
    private static function buildRawBodyParams(array $bodyParamsInput): array
    {
        $rawBodyParams = [];

        foreach (array_values($bodyParamsInput) as $index => $item) {
            $varName = trim((string) ($item['value'] ?? ''));
            $varText = trim((string) ($item['value_text'] ?? ''));

            if ($varName === '' || $varText === '') {
                continue;
            }

            $rawBodyParams[] = [
                'key' => (string) ($index + 1),
                'value' => $varName,
                'value_text' => $varText,
            ];
        }

        return $rawBodyParams;
    }
}
