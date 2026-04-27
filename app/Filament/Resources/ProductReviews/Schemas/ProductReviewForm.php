<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use App\Models\OrderItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)->schema([
                    Section::make('Data Review')
                        ->description('Informasi customer, produk, dan isi ulasan.')
                        ->columns(12)
                        ->schema([
                            Select::make('customer_id')
                                ->label('Customer')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            Select::make('product_id')
                                ->label('Produk')
                                ->relationship('product', 'name', fn (Builder $query): Builder => $query->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            Select::make('order_item_id')
                                ->label('Order Item')
                                ->relationship('orderItem', 'name', fn (Builder $query): Builder => $query
                                    ->with('order:id,order_no')
                                    ->latest('id'))
                                ->searchable(['name', 'sku'])
                                ->getOptionLabelFromRecordUsing(function (OrderItem $record): string {
                                    $orderNoPrefix = filled($record->order?->order_no)
                                        ? '[' . $record->order?->order_no . '] '
                                        : '';

                                    return trim($orderNoPrefix . $record->name . ' (x' . $record->qty . ')');
                                })
                                ->preload()
                                ->placeholder('Tanpa order item')
                                ->helperText('Opsional. Isi jika review berasal dari item pesanan tertentu.')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 8,
                                ]),

                            Select::make('rating')
                                ->label('Rating')
                                ->options([
                                    1 => '1 - Sangat Buruk',
                                    2 => '2 - Buruk',
                                    3 => '3 - Cukup',
                                    4 => '4 - Bagus',
                                    5 => '5 - Sangat Bagus',
                                ])
                                ->native(false)
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 4,
                                ]),

                            TextInput::make('title')
                                ->label('Judul Review')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Textarea::make('comment')
                                ->label('Komentar')
                                ->rows(6)
                                ->placeholder('Tulis pengalaman customer menggunakan produk ini...')
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 8,
                        ]),

                    Section::make('Moderasi')
                        ->description('Status verifikasi pembelian dan persetujuan review.')
                        ->schema([
                            Toggle::make('is_verified_purchase')
                                ->label('Pembelian Terverifikasi')
                                ->default(false)
                                ->required(),

                            Toggle::make('is_approved')
                                ->label('Disetujui Tayang')
                                ->default(false)
                                ->required(),
                        ])
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 4,
                        ]),
                ])->columnSpanFull(),
            ]);
    }
}
