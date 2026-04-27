<?php

namespace App\Filament\Widgets\WhatsAppBroadcasts;

use App\Models\CustomerWhatsAppConfirmation;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CustomerWhatsAppConfirmationWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Nomor WA Terkonfirmasi';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $description = 'Customer yang sudah mengirim pesan WA ke sistem dan dapat menerima notifikasi.';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CustomerWhatsAppConfirmation::query()
                ->with('customer:id,name,username,phone')
                ->latest('confirmed_at')
            )
            ->columns([
                TextColumn::make('customer.username')
                    ->label('Username')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Nama Customer')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Nomor WA')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('confirmed_at')
                    ->label('Pertama Konfirmasi')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('last_received_at')
                    ->label('Pesan Terakhir Diterima')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('unlinked')
                    ->label('Belum Terhubung ke Customer')
                    ->query(fn (Builder $query): Builder => $query->whereNull('customer_id'))
                    ->indicateUsing(fn (): array => [Indicator::make('Belum terhubung ke customer')]),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Nomor ini akan dihapus dari daftar konfirmasi. Sistem tidak akan bisa mengirim WA ke nomor ini sampai mereka mengirim pesan kembali.'),
            ])
            ->toolbarActions([]);
    }
}
