<?php

namespace App\Filament\Resources\ShippingTargets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ShippingTargetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('three_lc_code')
                ->label('3LC Code')
                ->placeholder('-'),

            TextEntry::make('country')
                ->label('Negara')
                ->placeholder('-'),

            TextEntry::make('province_id')
                ->label('Province ID')
                ->placeholder('-'),

            TextEntry::make('province')
                ->label('Provinsi')
                ->placeholder('-'),

            TextEntry::make('city_id')
                ->label('City ID')
                ->placeholder('-'),

            TextEntry::make('city')
                ->label('Kota / Kabupaten')
                ->placeholder('-'),

            TextEntry::make('district')
                ->label('Kecamatan')
                ->placeholder('-'),

            TextEntry::make('district_lion')
                ->label('Kecamatan (Lion)')
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
