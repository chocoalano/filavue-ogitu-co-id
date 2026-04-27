<?php

namespace App\Filament\Resources\CommodityCodes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CommodityCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Komoditas')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('Contoh: HS-0101')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('name')
                        ->label('Nama Komoditas')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Produk Herbal')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 8,
                        ]),

                    Toggle::make('dangerous_good')
                        ->label('Barang Berbahaya')
                        ->default(true)
                        ->required()
                        ->helperText('Aktif jika komoditas termasuk barang berbahaya.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Toggle::make('is_quarantine')
                        ->label('Wajib Karantina')
                        ->default(true)
                        ->required()
                        ->helperText('Aktif jika komoditas membutuhkan proses karantina.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
