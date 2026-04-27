<?php

namespace App\Filament\Resources\Carts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                TextInput::make('session_id'),
                TextInput::make('currency')
                    ->required()
                    ->default('IDR'),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('grand_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('applied_promos')
                    ->columnSpanFull(),
            ]);
    }
}
