<?php

namespace App\Filament\Resources\ReturnOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReturnOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.order_no')
                    ->label('Nomor Pesanan')
                    ->placeholder('-'),

                TextEntry::make('status')
                    ->label('Status Retur')
                    ->placeholder('-'),

                TextEntry::make('reason')
                    ->label('Alasan Retur')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('requested_at')
                    ->label('Waktu Pengajuan')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('processed_at')
                    ->label('Waktu Diproses')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
