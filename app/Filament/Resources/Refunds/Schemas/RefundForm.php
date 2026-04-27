<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Refunds\RefundOptionLabelFormatter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('order_id')
                        ->label('Pesanan')
                        ->relationship('order', 'order_no', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Order $record): string => RefundOptionLabelFormatter::formatOrder($record))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Cari nomor pesanan...')
                        ->helperText('Pilih pesanan yang akan diproses refund.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('payment_id')
                        ->label('Pembayaran')
                        ->relationship('payment', 'id', fn (Builder $query): Builder => $query->with(['method:id,name'])->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Payment $record): string => RefundOptionLabelFormatter::formatPayment($record))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Pilih pembayaran...')
                        ->helperText('Gunakan transaksi pembayaran yang terkait dengan pesanan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('status')
                        ->label('Status Refund')
                        ->required()
                        ->default('pending')
                        ->options(self::statusOptions())
                        ->helperText('Status proses refund dari pengajuan sampai selesai.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('amount')
                        ->label('Jumlah Refund')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 150000')
                        ->helperText('Nominal dana yang dikembalikan ke pelanggan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 8,
                        ]),

                    Textarea::make('reason')
                        ->label('Alasan Refund')
                        ->rows(4)
                        ->placeholder('Contoh: pesanan dibatalkan, duplikasi pembayaran, atau barang rusak.')
                        ->helperText('Opsional, namun direkomendasikan untuk audit.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
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
            'processed' => 'Diproses',
            'refunded' => 'Refund Selesai',
        ];
    }
}
