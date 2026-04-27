<?php

namespace App\Filament\Resources\CustomerNetworkMatrices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerNetworkMatrixInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('member.name')
                ->label('Member')
                ->placeholder('-'),

            TextEntry::make('member.ref_code')
                ->label('Kode Member')
                ->placeholder('-'),

            TextEntry::make('sponsor.name')
                ->label('Sponsor')
                ->placeholder('-'),

            TextEntry::make('sponsor.ref_code')
                ->label('Kode Sponsor')
                ->placeholder('-'),

            TextEntry::make('level')
                ->label('Level Matrix')
                ->numeric(decimalPlaces: 0),

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
}
