<?php

namespace App\Filament\Resources\CustomerBonusRewards\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member Penerima')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member penerima reward...')
                        ->helperText('Bonus reward akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('reward_type')
                        ->label('Tipe Reward')
                        ->native(false)
                        ->options(self::rewardTypeOptions())
                        ->placeholder('Pilih tipe reward...')
                        ->helperText('Promotion = periode tertentu, Lifetime = selamanya.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('reward')
                        ->label('Nama Reward')
                        ->required()
                        ->maxLength(225)
                        ->placeholder('Contoh: Gold Achievement Award')
                        ->helperText('Nama reward yang diberikan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('bv')
                        ->label('Business Volume (BV)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Contoh: 5000')
                        ->helperText('Jumlah BV syarat pencapaian reward.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Reward')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 500000')
                        ->helperText('Nominal reward dalam bentuk uang.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('index_value')
                        ->label('Nilai Index')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Contoh: 1.5')
                        ->helperText('Nilai index perhitungan reward.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Select::make('status')
                        ->label('Status Reward')
                        ->required()
                        ->native(false)
                        ->options(self::statusOptions())
                        ->default(0)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->helperText('Status reward: menunggu pencairan atau sudah dirilis.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4)
                        ->placeholder('Contoh: Reward pencapaian BV Q1 2026')
                        ->helperText('Catatan tambahan untuk audit reward.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
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

    private static function rewardTypeOptions(): array
    {
        return [
            'promotion' => 'Promotion',
            'lifetime' => 'Lifetime',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
