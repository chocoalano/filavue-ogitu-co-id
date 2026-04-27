<?php

namespace App\Filament\Resources\CustomerBonusCashbacks\Schemas;

use App\Models\Customer;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusCashbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member Penerima Cashback')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member penerima cashback...')
                        ->helperText('Bonus cashback akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('order_id')
                        ->label('Order Pemicu')
                        ->relationship('order', 'id', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Order $record): string => '#' . $record->id)
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih order...')
                        ->helperText('Order yang memicu bonus cashback ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Cashback')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 25000')
                        ->helperText('Nominal bonus cashback yang diterima member.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('index_value')
                        ->label('Nilai Index')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Contoh: 1.2')
                        ->helperText('Nilai index perhitungan bonus cashback.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Select::make('status')
                        ->label('Status Bonus')
                        ->required()
                        ->native(false)
                        ->options(self::statusOptions())
                        ->default(0)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->helperText('Status bonus: menunggu pencairan atau sudah dirilis.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4)
                        ->placeholder('Contoh: Cashback pembelian produk X')
                        ->helperText('Catatan tambahan untuk audit bonus cashback.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 8,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function customerLabel(Customer $customer): string
    {
        $refCode = $customer->ref_code ? ' (' . $customer->ref_code . ')' : '';

        return $customer->name . $refCode;
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
