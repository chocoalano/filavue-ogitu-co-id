<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\Media\PublicMediaUrl;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([
                Section::make('Informasi Kategori')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent.name')
                            ->label('Parent')
                            ->placeholder('-'),
                        TextEntry::make('slug')
                            ->label('Slug')
                            ->placeholder('-'),
                        TextEntry::make('name')
                            ->label('Nama')
                            ->placeholder('-'),
                        TextEntry::make('sort_order')
                            ->label('Urutan')
                            ->numeric(),
                        IconEntry::make('is_active')
                            ->label('Aktif')
                            ->boolean(),
                        ImageEntry::make('image')
                            ->label('Gambar')
                            ->state(function (object $record): ?string {
                                $imageUrl = PublicMediaUrl::resolve($record->image);

                                if (! filled($imageUrl)) {
                                    return null;
                                }

                                if (
                                    str_starts_with($imageUrl, 'http://')
                                    || str_starts_with($imageUrl, 'https://')
                                    || str_starts_with($imageUrl, 'data:')
                                ) {
                                    return $imageUrl;
                                }

                                return url($imageUrl);
                            })
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 8,
                    ]),

                Section::make('Ringkasan Relasi')
                    ->schema([
                        TextEntry::make('children_count')
                            ->label('Total Sub Kategori')
                            ->state(fn ($record): int => $record->children()->count())
                            ->numeric(),
                        TextEntry::make('products_count')
                            ->label('Total Produk')
                            ->state(fn ($record): int => $record->products()->count())
                            ->numeric(),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 4,
                    ]),

                Section::make('Sub Kategori')
                    ->description('Daftar sub kategori yang berada di bawah kategori ini.')
                    ->schema([
                        RepeatableEntry::make('children')
                            ->label('')
                            ->contained(false)
                            ->table([
                                TableColumn::make('Nama'),
                                TableColumn::make('Slug'),
                                TableColumn::make('Urutan'),
                                TableColumn::make('Aktif'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->placeholder('-'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->placeholder('-'),
                                TextEntry::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric(),
                                IconEntry::make('is_active')
                                    ->label('Aktif')
                                    ->boolean(),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpan(12),

                Section::make('Produk Terkait')
                    ->description('Daftar produk yang terhubung ke kategori ini.')
                    ->schema([
                        RepeatableEntry::make('products')
                            ->label('')
                            ->contained(false)
                            ->table([
                                TableColumn::make('SKU'),
                                TableColumn::make('Nama'),
                                TableColumn::make('Harga Dasar'),
                                TableColumn::make('Stok'),
                                TableColumn::make('Aktif'),
                            ])
                            ->schema([
                                TextEntry::make('sku')
                                    ->label('SKU')
                                    ->placeholder('-'),
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->placeholder('-'),
                                TextEntry::make('base_price')
                                    ->label('Harga Dasar')
                                    ->money('IDR')
                                    ->placeholder('-'),
                                TextEntry::make('stock')
                                    ->label('Stok')
                                    ->numeric(),
                                IconEntry::make('is_active')
                                    ->label('Aktif')
                                    ->boolean(),
                            ]),
                    ])
                    ->columnSpan(12),

                Section::make('Metadata')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columnSpan(12),
            ])->columnSpanFull(),
        ]);
    }
}
