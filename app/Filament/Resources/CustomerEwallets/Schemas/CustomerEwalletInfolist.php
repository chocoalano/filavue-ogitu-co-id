<?php

namespace App\Filament\Resources\CustomerEwallets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerEwalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')
                ->label('Nama Customer')
                ->placeholder('-'),

            TextEntry::make('ref_code')
                ->label('Kode Referral')
                ->placeholder('-'),

            TextEntry::make('username')
                ->label('Username')
                ->placeholder('-'),

            TextEntry::make('email')
                ->label('Email')
                ->placeholder('-'),

            TextEntry::make('phone')
                ->label('No. Telepon')
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Customer')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => match ((int) $state) {
                    2 => 'warning',
                    3 => 'success',
                    default => 'gray',
                }),

            TextEntry::make('ewallet_id')
                ->label('ID E-Wallet')
                ->placeholder('Belum terdaftar')
                ->copyable(),

            TextEntry::make('ewallet_saldo')
                ->label('Saldo E-Wallet')
                ->money('IDR'),

            TextEntry::make('bonus_pending')
                ->label('Bonus Pending')
                ->money('IDR'),

            TextEntry::make('bonus_processed')
                ->label('Bonus Diproses')
                ->money('IDR'),

            TextEntry::make('bank_name')
                ->label('Nama Bank')
                ->placeholder('-'),

            TextEntry::make('bank_account')
                ->label('No. Rekening')
                ->placeholder('-')
                ->copyable(),

            TextEntry::make('created_at')
                ->label('Bergabung')
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
            1 => 'Prospek',
            2 => 'Pasif',
            3 => 'Aktif',
        ];
    }
}
