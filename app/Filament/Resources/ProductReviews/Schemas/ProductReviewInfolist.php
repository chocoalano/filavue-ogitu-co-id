<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([
                Section::make('Relasi Customer')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer_id')
                            ->label('Customer ID')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('customer.name')
                            ->label('Nama Customer')
                            ->placeholder('-'),
                        TextEntry::make('customer.email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('customer.phone')
                            ->label('Telepon')
                            ->placeholder('-'),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 6,
                    ]),

                Section::make('Relasi Produk')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product_id')
                            ->label('Product ID')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('product.sku')
                            ->label('SKU')
                            ->placeholder('-'),
                        TextEntry::make('product.name')
                            ->label('Nama Produk')
                            ->placeholder('-'),
                        TextEntry::make('product.base_price')
                            ->label('Harga Dasar')
                            ->money('IDR')
                            ->placeholder('-'),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 6,
                    ]),

                Section::make('Relasi Order Item')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_item_id')
                            ->label('Order Item ID')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('orderItem.order.order_no')
                            ->label('No. Order')
                            ->placeholder('-'),
                        TextEntry::make('orderItem.sku')
                            ->label('SKU Item')
                            ->placeholder('-'),
                        TextEntry::make('orderItem.name')
                            ->label('Nama Item')
                            ->placeholder('-'),
                        TextEntry::make('orderItem.qty')
                            ->label('Qty')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('orderItem.row_total')
                            ->label('Total')
                            ->money('IDR')
                            ->placeholder('-'),
                    ])
                    ->columnSpan(12),

                Section::make('Detail Review')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rating')
                            ->label('Rating')
                            ->numeric(),
                        TextEntry::make('title')
                            ->label('Judul')
                            ->placeholder('-'),
                        TextEntry::make('comment')
                            ->label('Komentar')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        IconEntry::make('is_approved')
                            ->label('Disetujui')
                            ->boolean(),
                        IconEntry::make('is_verified_purchase')
                            ->label('Pembelian Terverifikasi')
                            ->boolean(),
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
