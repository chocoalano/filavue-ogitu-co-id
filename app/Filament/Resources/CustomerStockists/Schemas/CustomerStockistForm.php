<?php

namespace App\Filament\Resources\CustomerStockists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerStockistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([
                Section::make('Status Stockist')
                    ->description('Atur apakah customer ini beroperasi sebagai stockist.')
                    ->schema([
                        Select::make('id')
                            ->label('Pilih Customer')
                            ->options(function () {
                                // Ambil daftar customer yang belum menjadi stockist
                                return \App\Models\Customer::query()
                                    ->where('is_stockist', false)
                                    ->pluck('username', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required(),
                        Toggle::make('is_stockist')
                            ->label('Aktif sebagai Stockist')
                            ->default(true)
                            ->helperText('Nonaktifkan jika customer bukan stockist.')
                            ->inline(false),
                    ])
                    ->compact()
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 4,
                    ]),

                Section::make('Wilayah Stockist')
                    ->description('Lengkapi data wilayah operasional stockist (provinsi & kabupaten).')
                    ->schema([
                        Grid::make(12)->schema([
                            TextInput::make('stockist_province_id')
                                ->label('ID Provinsi')
                                ->numeric()
                                ->placeholder('Contoh: 31')
                                ->maxLength(10)
                                ->helperText('Kode wilayah provinsi (angka).')
                                ->required(fn ($get) => (bool) $get('is_stockist'))
                                ->disabled(fn ($get) => ! (bool) $get('is_stockist'))
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 4,
                                ]),

                            TextInput::make('stockist_province_name')
                                ->label('Nama Provinsi')
                                ->placeholder('Contoh: DKI Jakarta')
                                ->maxLength(100)
                                ->helperText('Nama provinsi wilayah stockist.')
                                ->required(fn ($get) => (bool) $get('is_stockist'))
                                ->disabled(fn ($get) => ! (bool) $get('is_stockist'))
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 8,
                                ]),

                            TextInput::make('stockist_kabupaten_id')
                                ->label('ID Kabupaten')
                                ->numeric()
                                ->placeholder('Contoh: 3171')
                                ->maxLength(10)
                                ->helperText('Kode wilayah kabupaten/kota (angka).')
                                ->required(fn ($get) => (bool) $get('is_stockist'))
                                ->disabled(fn ($get) => ! (bool) $get('is_stockist'))
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 4,
                                ]),

                            TextInput::make('stockist_kabupaten_name')
                                ->label('Nama Kabupaten/Kota')
                                ->placeholder('Contoh: Jakarta Selatan')
                                ->maxLength(100)
                                ->helperText('Nama kabupaten/kota wilayah stockist.')
                                ->required(fn ($get) => (bool) $get('is_stockist'))
                                ->disabled(fn ($get) => ! (bool) $get('is_stockist'))
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 8,
                                ]),
                        ]),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 8,
                    ]),
            ])
            ->columnSpanFull(),
        ]);
    }
}
