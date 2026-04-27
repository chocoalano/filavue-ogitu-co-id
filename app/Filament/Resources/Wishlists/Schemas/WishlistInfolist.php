<?php

namespace App\Filament\Resources\Wishlists\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WishlistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Wishlist')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nama Wishlist'),
                    TextEntry::make('customer.name')
                        ->label('Customer')
                        ->placeholder('-'),
                    TextEntry::make('customer.email')
                        ->label('Email Customer')
                        ->placeholder('-'),
                    TextEntry::make('customer.phone')
                        ->label('Telepon Customer')
                        ->placeholder('-'),
                    TextEntry::make('items_count')
                        ->label('Total Item')
                        ->state(fn ($record): int => $record->items()->count())
                        ->numeric(),
                    TextEntry::make('created_at')
                        ->label('Dibuat')
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->label('Diperbarui')
                        ->dateTime()
                        ->placeholder('-'),
                ]),

            Section::make('Item Wishlist')
                ->description('Daftar item produk dalam wishlist.')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->contained(false)
                        ->table([
                            TableColumn::make('Produk Master'),
                            TableColumn::make('Produk Snapshot'),
                            TableColumn::make('SKU'),
                            TableColumn::make('Diperbarui'),
                        ])
                        ->schema([
                            TextEntry::make('product.name')
                                ->label('Produk Master')
                                ->placeholder('-'),
                            TextEntry::make('product_name')
                                ->label('Produk Snapshot')
                                ->placeholder('-'),
                            TextEntry::make('product_sku')
                                ->label('SKU')
                                ->placeholder('-'),
                            TextEntry::make('updated_at')
                                ->label('Diperbarui')
                                ->dateTime()
                                ->placeholder('-'),
                        ]),
                ]),
        ]);
    }
}
