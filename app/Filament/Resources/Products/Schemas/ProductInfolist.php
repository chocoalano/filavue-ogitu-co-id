<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // =========================================================
                // IDENTITAS PRODUK
                // =========================================================
                Section::make('Identitas Produk')
                    ->description('Informasi dasar produk untuk identifikasi dan penelusuran.')
                    ->schema([
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->placeholder('-'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->placeholder('-'),

                        TextEntry::make('name')
                            ->label('Nama Produk')
                            ->placeholder('-'),

                        TextEntry::make('brand')
                            ->label('Merek')
                            ->placeholder('-'),

                        IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])->columnSpan(1),

                // =========================================================
                // HARGA & STOK
                // =========================================================
                Section::make('Harga & Stok')
                    ->description('Harga dasar, mata uang, dan ketersediaan stok.')
                    ->schema([
                        TextEntry::make('base_price')
                            ->label('Harga Dasar')
                            ->money('IDR')
                            ->placeholder('-'),

                        TextEntry::make('discount')
                            ->label('Diskon')
                            ->money('IDR')
                            ->placeholder('-'),

                        TextEntry::make('currency')
                            ->label('Mata Uang')
                            ->placeholder('-'),

                        TextEntry::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('warranty_months')
                            ->label('Garansi (Bulan)')
                            ->numeric()
                            ->placeholder('-'),
                    ])->columnSpan(2),

                // =========================================================
                // DESKRIPSI
                // =========================================================
                Section::make('Deskripsi')
                    ->description('Ringkasan dan deskripsi lengkap produk.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('short_desc')
                            ->label('Deskripsi Singkat')
                            ->placeholder('-')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make('long_desc')
                            ->label('Deskripsi Lengkap')
                            ->placeholder('-')
                            ->html()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                // =========================================================
                // RELASI VARIAN / KATEGORI & GAMBAR
                // =========================================================
                Section::make('Varian & Gambar Terkait')
                    ->description('Relasi varian/kategori dan media gambar produk.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('categories.name')
                            ->label('Varian / Kategori')
                            ->badge()
                            ->listWithLineBreaks()
                            ->limitList(8)
                            ->expandableLimitedList()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        RepeatableEntry::make('primaryMedia')
                            ->label('Gambar Utama')
                            ->placeholder('Belum ada gambar utama.')
                            ->contained(false)
                            ->columns(2)
                            ->schema([
                                ImageEntry::make('url')
                                    ->label('Preview')
                                    ->disk('public')
                                    ->imageHeight(180)
                                    ->square(),
                                TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge(),
                                TextEntry::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('-'),
                                TextEntry::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->placeholder('-'),
                            ])
                            ->columnSpan(1),

                        RepeatableEntry::make('media')
                            ->label('Galeri Media')
                            ->placeholder('Belum ada media tambahan.')
                            ->contained(false)
                            ->schema([
                                ImageEntry::make('url')
                                    ->label('Preview')
                                    ->disk('public')
                                    ->imageHeight(80)
                                    ->square(),
                                TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge(),
                                TextEntry::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('-')
                                    ->limit(40),
                                TextEntry::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->placeholder('-'),
                                IconEntry::make('is_primary')
                                    ->label('Utama')
                                    ->boolean(),
                            ])
                            ->table([
                                TableColumn::make('Preview')->width('92px'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Alt Text')->wrapHeader(),
                                TableColumn::make('Urutan'),
                                TableColumn::make('Utama'),
                            ])
                            ->columnSpan(2),
                    ])->columnSpanFull(),

                // =========================================================
                // RELASI PENJUALAN / PROMOSI
                // =========================================================
                Section::make('Relasi Penjualan & Promosi')
                    ->description('Data relasi pengganti tab RelationManager.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reviews_count')
                            ->label('Total Review')
                            ->state(fn ($record): int => $record->reviews()->count())
                            ->numeric(),

                        TextEntry::make('average_rating')
                            ->label('Rata-rata Rating')
                            ->state(function ($record): string {
                                $value = $record->reviews()->avg('rating');

                                return $value === null ? '-' : number_format((float) $value, 2);
                            }),

                        TextEntry::make('order_items_count')
                            ->label('Total Item Pesanan')
                            ->state(fn ($record): int => $record->orderItems()->count())
                            ->numeric(),

                        TextEntry::make('cart_items_count')
                            ->label('Total Item Keranjang')
                            ->state(fn ($record): int => $record->cartItems()->count())
                            ->numeric(),

                        TextEntry::make('promotions_count')
                            ->label('Total Promosi Aktif/Terkait')
                            ->state(fn ($record): int => $record->promotions()->count())
                            ->numeric(),

                        RepeatableEntry::make('promotions')
                            ->label('Promosi Terkait')
                            ->contained(false)
                            ->table([
                                TableColumn::make('Kode'),
                                TableColumn::make('Nama'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Min Qty'),
                                TableColumn::make('Diskon Nominal'),
                                TableColumn::make('Diskon %'),
                                TableColumn::make('Harga Bundle'),
                            ])
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Kode')
                                    ->placeholder('-'),
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->placeholder('-'),
                                TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('pivot.min_qty')
                                    ->label('Min Qty')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('pivot.discount_value')
                                    ->label('Diskon Nominal')
                                    ->money('IDR')
                                    ->placeholder('-'),
                                TextEntry::make('pivot.discount_percent')
                                    ->label('Diskon %')
                                    ->suffix('%')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('pivot.bundle_price')
                                    ->label('Harga Bundle')
                                    ->money('IDR')
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),

                        RepeatableEntry::make('reviews')
                            ->label('Review Produk')
                            ->contained(false)
                            ->table([
                                TableColumn::make('Customer'),
                                TableColumn::make('Rating'),
                                TableColumn::make('Judul'),
                                TableColumn::make('Disetujui'),
                                TableColumn::make('Terverifikasi'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->placeholder('-'),
                                TextEntry::make('rating')
                                    ->label('Rating')
                                    ->numeric(),
                                TextEntry::make('title')
                                    ->label('Judul')
                                    ->placeholder('-'),
                                IconEntry::make('is_approved')
                                    ->label('Disetujui')
                                    ->boolean(),
                                IconEntry::make('is_verified_purchase')
                                    ->label('Terverifikasi')
                                    ->boolean(),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),

                        Livewire::make('filament.products.order-items-table')
                            ->key('product-order-items-table')
                            ->lazy()
                            ->columnSpanFull(),

                        RepeatableEntry::make('cartItems')
                            ->label('Item Keranjang')
                            ->contained(false)
                            ->table([
                                TableColumn::make('Customer'),
                                TableColumn::make('Produk Snapshot'),
                                TableColumn::make('SKU'),
                                TableColumn::make('Qty'),
                                TableColumn::make('Harga'),
                                TableColumn::make('Total'),
                            ])
                            ->schema([
                                TextEntry::make('cart.customer.name')
                                    ->label('Customer')
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
                                    ->money('IDR')
                                    ->placeholder('-'),
                                TextEntry::make('row_total')
                                    ->label('Total')
                                    ->money('IDR')
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // =========================================================
                // DIMENSI & BERAT
                // =========================================================
                Section::make('Dimensi & Berat')
                    ->description('Ukuran fisik untuk kebutuhan pengiriman (opsional).')
                    ->schema([
                        TextEntry::make('weight_gram')
                            ->label('Berat (gram)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('length_mm')
                            ->label('Panjang (mm)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('width_mm')
                            ->label('Lebar (mm)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('height_mm')
                            ->label('Tinggi (mm)')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                // =========================================================
                // BONUS / INSENTIF (MLM / REWARD)
                // =========================================================
                Section::make('Bonus & Insentif')
                    ->description('Nilai bonus/insentif yang melekat pada produk.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('pv')
                            ->label('PV')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_sponsor')
                            ->label('Bonus Sponsor')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_royalty')
                            ->label('Bonus Royalty')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_cashback')
                            ->label('Bonus Cashback')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                // =========================================================
                // INFORMASI SISTEM
                // =========================================================
                Section::make('Informasi Sistem')
                    ->description('Metadata pembuatan dan pembaruan data.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ])
            ->columns(3);
    }
}
