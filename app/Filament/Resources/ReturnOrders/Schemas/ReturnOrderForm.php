<?php

namespace App\Filament\Resources\ReturnOrders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ReturnOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('order_id')
                        ->label('Pesanan')
                        ->relationship('order', 'order_no')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Cari nomor pesanan...')
                        ->helperText('Pilih nomor pesanan yang mengajukan retur.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('status')
                        ->label('Status Retur')
                        ->required()
                        ->default('pending')
                        ->options(self::statusOptions())
                        ->helperText('Gunakan status untuk mengontrol alur retur dari pengajuan sampai selesai.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Textarea::make('reason')
                        ->label('Alasan Retur')
                        ->rows(4)
                        ->placeholder('Contoh: barang rusak, salah kirim, ukuran tidak sesuai, dll.')
                        ->helperText('Jelaskan alasan retur secara singkat dan jelas.')
                        ->columnSpanFull(),

                    DateTimePicker::make('requested_at')
                        ->label('Waktu Pengajuan')
                        ->seconds(false)
                        ->default(now())
                        ->helperText('Waktu ketika retur diajukan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    DateTimePicker::make('processed_at')
                        ->label('Waktu Diproses')
                        ->seconds(false)
                        ->helperText('Isi ketika retur mulai diproses / selesai diproses.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'received' => 'Barang Diterima',
            'inspected' => 'Dicek',
            'completed' => 'Selesai',
        ];
    }
}
