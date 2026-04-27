<?php

namespace App\Filament\Resources\CommodityCodes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CommodityCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('code')
                ->label('Kode Komoditas')
                ->placeholder('-'),

            TextEntry::make('name')
                ->label('Nama Komoditas')
                ->placeholder('-'),

            IconEntry::make('dangerous_good')
                ->label('Barang Berbahaya')
                ->boolean(),

            IconEntry::make('is_quarantine')
                ->label('Wajib Karantina')
                ->boolean(),

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
