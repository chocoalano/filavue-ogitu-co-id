<?php

namespace App\Filament\Resources\Carts\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(6)->schema([
                // =========================
                // Identitas (kiri)
                // =========================
                Section::make('Identitas')
                    ->description('Informasi customer, sesi, dan mata uang.')
                    ->schema([
                        Grid::make(6)->schema([
                            TextEntry::make('customer.name')
                                ->label('Customer')
                                ->placeholder('Guest')
                                ->columnSpan(6),

                            TextEntry::make('customer_id')
                                ->label('Customer ID')
                                ->numeric()
                                ->placeholder('-')
                                ->visible(fn ($record) => filled($record->customer_id))
                                ->columnSpan(3),

                            TextEntry::make('currency')
                                ->label('Mata Uang')
                                ->badge()
                                ->placeholder('-')
                                ->columnSpan(6),

                            TextEntry::make('session_id')
                                ->label('Session ID')
                                ->placeholder('-')
                                ->copyable()
                                ->columnSpan(6),
                        ]),
                    ])
                    ->columnSpan([
                        'default' => 6,
                        'lg' => 2,
                    ]),

                // =========================
                // Ringkasan Nilai (kanan)
                // =========================
                Section::make('Ringkasan Nilai')
                    ->description('Rincian biaya dan total keranjang.')
                    ->schema([
                        Grid::make(6)->schema([
                            TextEntry::make('items_count')
                                ->label('Jumlah Baris Item')
                                ->state(fn ($record): int => $record->items()->count())
                                ->numeric()
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('items_total_qty')
                                ->label('Total Qty Produk')
                                ->state(fn ($record): int => (int) $record->items()->sum('qty'))
                                ->numeric()
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('subtotal_amount')
                                ->label('Subtotal')
                                ->money(fn ($record) => $record->currency ?? 'IDR')
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('discount_amount')
                                ->label('Diskon')
                                ->money(fn ($record) => $record->currency ?? 'IDR')
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('shipping_amount')
                                ->label('Ongkir')
                                ->money(fn ($record) => $record->currency ?? 'IDR')
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('tax_amount')
                                ->label('Pajak')
                                ->money(fn ($record) => $record->currency ?? 'IDR')
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('grand_total')
                                ->label('Grand Total')
                                ->money(fn ($record) => $record->currency ?? 'IDR')
                                ->placeholder('-')
                                ->weight('bold')
                                ->size('lg')
                                ->columnSpan(6),

                            TextEntry::make('applied_promos')
                                ->label('Promo')
                                ->placeholder('—')
                                ->badge()
                                ->formatStateUsing(static function ($state): array|string {
                                    if (is_array($state)) {
                                        return array_values(array_filter($state, fn ($v) => filled($v)));
                                    }

                                    if (is_string($state)) {
                                        $decoded = json_decode($state, true);
                                        if (is_array($decoded)) {
                                            return array_values(array_filter($decoded, fn ($v) => filled($v)));
                                        }
                                        return $state !== '' ? $state : '—';
                                    }

                                    return '—';
                                })
                                ->columnSpan(6),
                        ]),
                    ])
                    ->columnSpan([
                        'default' => 6,
                        'lg' => 4,
                    ]),

                Section::make('Item Keranjang')
                    ->description('Detail produk yang saat ini ada di keranjang.')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->contained(false)
                            ->table([
                                TableColumn::make('Produk Master'),
                                TableColumn::make('Produk Snapshot'),
                                TableColumn::make('SKU'),
                                TableColumn::make('Qty'),
                                TableColumn::make('Harga'),
                                TableColumn::make('Total'),
                                TableColumn::make('Update Terakhir'),
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
                                TextEntry::make('qty')
                                    ->label('Qty')
                                    ->numeric(),
                                TextEntry::make('unit_price')
                                    ->label('Harga')
                                    ->money(fn ($record) => $record->currency ?? 'IDR')
                                    ->placeholder('-'),
                                TextEntry::make('row_total')
                                    ->label('Total')
                                    ->money(fn ($record) => $record->currency ?? 'IDR')
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->label('Update Terakhir')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpan(6),

                // =========================
                // Metadata (bawah full)
                // =========================
                Section::make('Metadata')
                    ->description('Waktu pembuatan dan pembaruan.')
                    ->schema([
                        Grid::make(6)->schema([
                            TextEntry::make('created_at')
                                ->label('Dibuat')
                                ->dateTime()
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),

                            TextEntry::make('updated_at')
                                ->label('Diperbarui')
                                ->dateTime()
                                ->placeholder('-')
                                ->columnSpan([
                                    'default' => 6,
                                    'md' => 3,
                                ]),
                        ]),
                    ])
                    ->columnSpan(6),
            ])->columnSpanFull(),
        ]);
    }
}
