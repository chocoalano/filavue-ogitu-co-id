<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Tables;

use App\Jobs\ProcessWhatsAppBroadcastJob;
use App\Models\WhatsAppBroadcast;
use App\Services\QontactService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsAppBroadcastTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(function (WhatsAppBroadcast $record): ?string {
                        if (! $record->template_id) {
                            return null;
                        }

                        $templates = app(QontactService::class)->getWhatsAppTemplates();
                        $name = $templates[$record->template_id] ?? $record->template_id;

                        return "Template: {$name}";
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'sent' => 'success',
                        'partial' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                // ✅ FIX: progress dibuat string langsung, description ambil dari record
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (WhatsAppBroadcast $r): string {
                        $total = max(0, (int) $r->total_recipients);
                        $ok = max(0, (int) $r->success_recipients);

                        if ($total === 0) {
                            return '0/0 (0%)';
                        }

                        $pct = (int) round(($ok / $total) * 100);

                        return "{$ok}/{$total} ({$pct}%)";
                    })
                    ->description(function (WhatsAppBroadcast $r): ?string {
                        $fail = max(0, (int) $r->failed_recipients);

                        return $fail > 0 ? "Gagal: {$fail}" : null;
                    })
                    ->sortable(query: function ($query, string $direction) {
                        $query->orderBy('success_recipients', $direction);
                    }),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sent_at')
                    ->label('Terkirim')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'processing' => 'Processing',
                        'sent' => 'Sent',
                        'partial' => 'Partial',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon(Heroicon::OutlinedArrowRightEndOnRectangle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Ulang Broadcast')
                    ->modalDescription('Broadcast ini akan diproses ulang dan status penerima akan di-reset ke antrean.')
                    ->modalSubmitActionLabel('Ya, kirim ulang')
                    ->visible(fn (WhatsAppBroadcast $record): bool => $record->status !== 'processing')
                    ->action(function (WhatsAppBroadcast $record): void {
                        try {
                            self::scheduleResend($record);

                            Notification::make()
                                ->title('Broadcast dijadwalkan ulang')
                                ->body('Proses resend WhatsApp berhasil dimasukkan ke queue.')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            $record->update([
                                'status' => 'failed',
                                'last_error' => $exception->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Gagal melakukan resend')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (WhatsAppBroadcast $record) => in_array($record->status, ['draft', 'failed'], true))
                    ->tooltip('Hanya Draft/Failed yang boleh diubah.'),

                DeleteAction::make()
                    ->visible(fn (WhatsAppBroadcast $record) => $record->status === 'draft')
                    ->tooltip('Hanya Draft yang boleh dihapus.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    private static function scheduleResend(WhatsAppBroadcast $record): void
    {
        $record->update([
            'status' => 'processing',
            'total_recipients' => 0,
            'success_recipients' => 0,
            'failed_recipients' => 0,
            'last_error' => null,
            'sent_at' => null,
        ]);

        $record->recipients()->update([
            'status' => 'queued',
            'response_message' => null,
            'sent_at' => null,
        ]);

        ProcessWhatsAppBroadcastJob::dispatch((int) $record->id);
    }
}
