<?php

namespace App\Filament\Resources\Rewards\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('code')
                ->label('Kode Reward')
                ->placeholder('-'),

            TextEntry::make('name')
                ->label('Nama Reward')
                ->placeholder('-'),

            TextEntry::make('reward')
                ->label('Hadiah')
                ->placeholder('-'),

            TextEntry::make('value')
                ->label('Nilai Reward')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('bv')
                ->label('Business Volume (BV)')
                ->numeric(decimalPlaces: 2)
                ->placeholder('-'),

            TextEntry::make('type')
                ->label('Tipe Reward')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::typeOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'info'),

            TextEntry::make('status')
                ->label('Status Reward')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'danger'),

            TextEntry::make('start')
                ->label('Periode Mulai')
                ->date()
                ->placeholder('-'),

            TextEntry::make('end')
                ->label('Periode Selesai')
                ->date()
                ->placeholder('-'),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->placeholder('-'),
        ]);
    }

    private static function typeOptions(): array
    {
        return [
            0 => 'Periode',
            1 => 'Permanen',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Tidak Aktif',
            1 => 'Aktif',
        ];
    }
}
