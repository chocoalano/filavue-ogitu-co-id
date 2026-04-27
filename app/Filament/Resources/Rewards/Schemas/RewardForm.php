<?php

namespace App\Filament\Resources\Rewards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Reward')
                        ->maxLength(10)
                        ->placeholder('Contoh: SR2026')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('name')
                        ->label('Nama Reward')
                        ->required()
                        ->maxLength(225)
                        ->placeholder('Contoh: STAR RACER')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 8,
                        ]),

                    TextInput::make('reward')
                        ->label('Hadiah')
                        ->maxLength(225)
                        ->placeholder('Contoh: MOTOR / CASH Rp. 15 Juta')
                        ->columnSpanFull(),

                    TextInput::make('value')
                        ->label('Nilai Reward')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 315000000')
                        ->helperText('Nilai target reward yang digunakan dalam sistem.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('bv')
                        ->label('Business Volume (BV)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 315000000')
                        ->helperText('Syarat BV untuk mencapai reward.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Select::make('type')
                        ->label('Tipe Reward')
                        ->required()
                        ->native(false)
                        ->live()
                        ->options(self::typeOptions())
                        ->default(0)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->helperText('Periode = terbatas tanggal. Permanen = tanpa batas waktu.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    DatePicker::make('start')
                        ->label('Periode Mulai')
                        ->native(false)
                        ->visible(fn (Get $get): bool => (int) $get('type') === 0)
                        ->required(fn (Get $get): bool => (int) $get('type') === 0)
                        ->placeholder('Pilih tanggal mulai')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    DatePicker::make('end')
                        ->label('Periode Selesai')
                        ->native(false)
                        ->visible(fn (Get $get): bool => (int) $get('type') === 0)
                        ->required(fn (Get $get): bool => (int) $get('type') === 0)
                        ->afterOrEqual('start')
                        ->placeholder('Pilih tanggal selesai')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Select::make('status')
                        ->label('Status Reward')
                        ->required()
                        ->native(false)
                        ->options(self::statusOptions())
                        ->default(1)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),
                ])
                ->columnSpanFull(),
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
