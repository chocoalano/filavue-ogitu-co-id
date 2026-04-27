<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Schemas;

use App\Services\QontactService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;

class WhatsAppBroadcastForm
{
    public static function configure($schema): mixed
    {
        return $schema->components([
            Section::make('Template Pesan')
                ->description('Pilih template WhatsApp yang sudah disetujui (Approved) di Qontak.')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->schema([
                    Select::make('channel_integration_id')
                        ->label('Channel Integration')
                        ->required()
                        ->searchable()
                        ->options(fn (): array => app(QontactService::class)->getWhatsAppIntegrations())
                        ->placeholder('Pilih channel integration...')
                        ->helperText('Channel WhatsApp Business yang akan digunakan untuk mengirim broadcast ini.')
                        ->columnSpanFull(),

                    Select::make('template_id')
                        ->label('Template WhatsApp Qontak')
                        ->required()
                        ->searchable()
                        ->live()
                        ->options(fn (): array => app(QontactService::class)->getWhatsAppTemplates())
                        ->placeholder('Pilih template...')
                        ->helperText('Template yang tampil sudah difilter dari Qontak. [N var] menunjukkan jumlah variabel.')
                        ->columnSpanFull()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if (! $state) {
                                $set('_template_hint', null);
                                $set('body_params', []);

                                return;
                            }

                            $qontactService = app(QontactService::class);
                            $params = $qontactService->getWhatsAppTemplateParams($state);

                            $set('_template_hint', $params !== [] ? $qontactService->buildParamHintHtml($params) : null);

                            $columnMap = $qontactService->getCustomerColumnMap();
                            $set('body_params', array_map(
                                fn (array $param): array => [
                                    'value' => $param['value'],
                                    'value_text' => $columnMap[$param['value']]['column'] ?? '',
                                ],
                                $params,
                            ));
                        }),

                    TextEntry::make('_template_hint_view')
                        ->label('Pemetaan Variabel Template')
                        ->html()
                        ->state(fn (callable $get): string => $get('_template_hint') ?: 'Pilih template untuk melihat pemetaan variabel.')
                        ->hidden(fn (callable $get): bool => blank($get('template_id')))
                        ->columnSpanFull(),
                ]),

            Section::make('Konten Broadcast')
                ->description('Informasi kampanye dan pemetaan variabel template ke kolom data customer.')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    TextInput::make('title')
                        ->label('Nama Kampanye')
                        ->placeholder('Misal: Promo Ramadhan 2026')
                        ->helperText('Hanya untuk keperluan arsip internal, tidak dikirim ke penerima.')
                        ->required()
                        ->columnSpanFull()
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn ($state, $set) => $set('title', trim((string) $state))),

                    Repeater::make('body_params')
                        ->label('Pemetaan Variabel Template')
                        ->helperText('Setiap variabel template dipetakan ke kolom data customer. Pilih template di atas untuk mengisi otomatis.')
                        ->schema([
                            TextInput::make('value')
                                ->label('Parameter')
                                ->placeholder('Contoh: full_name')
                                ->required(),
                            Select::make('value_text')
                                ->label('Kolom Customer')
                                ->options(fn (): array => collect(app(QontactService::class)->getCustomerColumnMap())
                                    ->mapWithKeys(fn (array $info): array => [
                                        $info['column'] => "{$info['column']} — {$info['label']}",
                                    ])
                                    ->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Parameter')
                        ->reorderable(false)
                        ->columnSpanFull(),

                    Hidden::make('created_by')
                        ->default(fn () => auth()->id())
                        ->dehydrated(),
                ]),
        ]);
    }
}
