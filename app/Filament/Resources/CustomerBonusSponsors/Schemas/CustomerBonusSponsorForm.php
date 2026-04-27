<?php

namespace App\Filament\Resources\CustomerBonusSponsors\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusSponsorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member Penerima Bonus')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member penerima bonus...')
                        ->helperText('Bonus sponsor akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('from_member_id')
                        ->label('Sumber Bonus (Downline)')
                        ->relationship('fromMember', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->different('member_id')
                        ->placeholder('Pilih member sumber bonus...')
                        ->helperText('Member yang memicu bonus sponsor (tidak boleh sama dengan penerima).')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Bonus')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 50000')
                        ->helperText('Nilai bonus sponsor yang diterima member.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('index_value')
                        ->label('Nilai Index')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 1.5')
                        ->helperText('Nilai index/perhitungan bonus sponsor.')
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
                        ->helperText('Menentukan apakah bonus masih pending atau sudah dirilis.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(5)
                        ->placeholder('Contoh: Sponsor Plan A - Placement')
                        ->helperText('Catatan tambahan untuk kebutuhan audit bonus.')
                        ->columnSpanFull(),
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
