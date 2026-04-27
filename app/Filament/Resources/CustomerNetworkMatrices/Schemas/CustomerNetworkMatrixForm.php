<?php

namespace App\Filament\Resources\CustomerNetworkMatrices\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerNetworkMatrixForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->name . ($record->ref_code ? ' (' . $record->ref_code . ')' : ''))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('sponsor_id')
                        ->label('Sponsor')
                        ->relationship('sponsor', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->name . ($record->ref_code ? ' (' . $record->ref_code . ')' : ''))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->placeholder('Pilih sponsor (opsional)...')
                        ->helperText('Kosongkan jika data ini tidak memiliki sponsor langsung.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('level')
                        ->label('Level Matrix')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(3)
                        ->placeholder('Catatan tambahan matrix sponsor...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
