<?php

namespace App\Filament\Resources\CustomerBonusCashbacks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerBonusCashbackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('member.name')
                ->label('Member Penerima')
                ->placeholder('-'),

            TextEntry::make('member.ref_code')
                ->label('Ref Penerima')
                ->placeholder('-'),

            TextEntry::make('order.id')
                ->label('Order')
                ->formatStateUsing(fn (mixed $state): string => '#' . $state)
                ->placeholder('-'),

            TextEntry::make('amount')
                ->label('Nominal Cashback')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('index_value')
                ->label('Nilai Index')
                ->numeric(decimalPlaces: 2)
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Bonus')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'warning'),

            TextEntry::make('description')
                ->label('Keterangan')
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
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
