<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppBroadcastInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->label('Judul'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (?string $state) => match ($state) {
                            'draft' => 'gray',
                            'processing' => 'warning',
                            'sent' => 'success',
                            'partial' => 'warning',
                            'failed' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('template_id')
                        ->label('Template ID Qontak')
                        ->placeholder('-')
                        ->copyable()
                        ->fontFamily('mono'),
                    TextEntry::make('creator.name')->label('Dibuat Oleh')->placeholder('-'),
                    TextEntry::make('sent_at')->label('Waktu Kirim')->dateTime()->placeholder('-'),
                    TextEntry::make('created_at')->label('Dibuat')->dateTime()->placeholder('-'),
                    TextEntry::make('updated_at')->label('Diubah')->dateTime()->placeholder('-'),
                ]),

            Section::make('Pesan')
                ->schema([
                    TextEntry::make('message')
                        ->label('Nilai Variabel ke-2 ({{2}})')
                        ->helperText('Nilai ini dikirim sebagai parameter kedua pada template. Variabel pertama ({{1}}) diisi otomatis dari nama customer.')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('Statistik Penerima')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('total_recipients')->label('Total')->numeric(),
                        TextEntry::make('success_recipients')->label('Berhasil')->numeric(),
                        TextEntry::make('failed_recipients')->label('Gagal')->numeric(),
                    ]),
                    TextEntry::make('last_error')->label('Error Terakhir')->placeholder('-')->columnSpanFull(),
                ]),

            Section::make('Daftar Penerima')
                ->description('Relasi penerima broadcast yang sebelumnya ada di RelationManager.')
                ->schema([
                    TextEntry::make('recipients_count')
                        ->label('Total Baris Penerima')
                        ->state(fn ($record): int => $record->recipients()->count())
                        ->numeric(),

                    RepeatableEntry::make('recipients')
                        ->label('')
                        ->contained(false)
                        ->table([
                            TableColumn::make('Customer'),
                            TableColumn::make('Phone'),
                            TableColumn::make('Normalized'),
                            TableColumn::make('Status'),
                            TableColumn::make('Sent At'),
                            TableColumn::make('Response'),
                        ])
                        ->schema([
                            TextEntry::make('customer_name')
                                ->label('Customer')
                                ->placeholder('-'),
                            TextEntry::make('phone')
                                ->label('Phone')
                                ->placeholder('-'),
                            TextEntry::make('normalized_phone')
                                ->label('Normalized')
                                ->placeholder('-'),
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn (?string $state) => match ($state) {
                                    'queued' => 'gray',
                                    'processing' => 'warning',
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    default => 'gray',
                                }),
                            TextEntry::make('sent_at')
                                ->label('Sent At')
                                ->dateTime()
                                ->placeholder('-'),
                            TextEntry::make('response_message')
                                ->label('Response')
                                ->placeholder('-'),
                        ]),
                ]),
        ]);
    }
}
