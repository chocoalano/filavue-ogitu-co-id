<?php

namespace App\Filament\Resources\CustomerWalletTransactions\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerWalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->name . ($record->ref_code ? ' (' . $record->ref_code . ')' : ''))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih customer...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('type')
                        ->label('Tipe Transaksi')
                        ->options(self::typeOptions())
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih tipe...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Select::make('status')
                        ->label('Status')
                        ->options(self::statusOptions())
                        ->required()
                        ->native(false)
                        ->default('pending')
                        ->placeholder('Pilih status...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->placeholder('0')
                        ->helperText('Nominal mutasi transaksi.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('balance_before')
                        ->label('Saldo Sebelum')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->placeholder('0')
                        ->helperText('Saldo wallet sebelum transaksi.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('balance_after')
                        ->label('Saldo Sesudah')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->placeholder('0')
                        ->helperText('Saldo wallet setelah transaksi.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->placeholder('Contoh: bank_transfer, qris')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('transaction_ref')
                        ->label('Referensi Transaksi')
                        ->placeholder('Contoh: TRX-20240101-001')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('midtrans_transaction_id')
                        ->label('ID Midtrans')
                        ->placeholder('ID transaksi dari Midtrans')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    DateTimePicker::make('completed_at')
                        ->label('Waktu Selesai')
                        ->seconds(false)
                        ->placeholder('Kosongkan jika belum selesai')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 5,
                        ]),

                    Toggle::make('is_system')
                        ->label('Transaksi Sistem')
                        ->helperText('Aktifkan jika transaksi ini dibuat otomatis oleh sistem.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 2,
                        ]),

                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->placeholder('Catatan tambahan atau info bank (JSON)...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                        ]),

                    TextInput::make('midtrans_signature_key')
                        ->label('Signature Key Midtrans')
                        ->placeholder('Signature key dari Midtrans')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function typeOptions(): array
    {
        return [
            'topup' => 'Topup',
            'withdrawal' => 'Withdrawal',
            'bonus' => 'Bonus',
            'purchase' => 'Purchase',
            'refund' => 'Refund',
            'tax' => 'Tax',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
    }
}
