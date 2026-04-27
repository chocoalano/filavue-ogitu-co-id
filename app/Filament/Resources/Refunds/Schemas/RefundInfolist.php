<?php

namespace App\Filament\Resources\Refunds\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('order.order_no')
                ->label('Nomor Pesanan')
                ->placeholder('-'),

            TextEntry::make('payment.id')
                ->label('ID Pembayaran')
                ->placeholder('-'),

            TextEntry::make('payment.transaction_id')
                ->label('ID Transaksi')
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Refund')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? '-'),

            TextEntry::make('amount')
                ->label('Jumlah Refund')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('reason')
                ->label('Alasan Refund')
                ->placeholder('-')
                ->columnSpanFull(),

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

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'processed' => 'Diproses',
            'refunded' => 'Refund Selesai',
        ];
    }
}
