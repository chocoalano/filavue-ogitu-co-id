<?php

namespace App\Filament\Resources\CustomerEwallets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CustomerEwalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    TextInput::make('ewallet_id')
                        ->label('ID E-Wallet')
                        ->placeholder('Contoh: EW-12345678')
                        ->helperText('ID unik dompet elektronik customer.')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('bank_name')
                        ->label('Nama Bank')
                        ->placeholder('Contoh: BCA, BRI, Mandiri')
                        ->helperText('Bank untuk pencairan saldo e-wallet.')
                        ->maxLength(100)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('bank_account')
                        ->label('No. Rekening')
                        ->placeholder('Contoh: 1234567890')
                        ->helperText('Nomor rekening tujuan pencairan.')
                        ->maxLength(50)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
