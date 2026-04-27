<?php

namespace App\Filament\Resources\GachaBoards;

use App\Filament\Resources\GachaBoards\Pages\ManageGachaBoards;
use App\Models\GachaBoard;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GachaBoardsResource extends Resource
{
    protected static ?string $model = GachaBoard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Gatcha System';

    protected static ?string $navigationLabel = 'Gacha Boards';

    protected static ?string $modelLabel = 'Gacha Board';

    protected static ?string $pluralModelLabel = 'Gacha Boards';

    protected static ?string $recordTitleAttribute = 'board_code';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Board')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('campaign_id')
                                    ->label('Campaign')
                                    ->relationship('campaign', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('board_code')
                                    ->label('Kode Board')
                                    ->required()
                                    ->maxLength(60)
                                    ->placeholder('Contoh: BOARD-001'),

                                TextInput::make('title')
                                    ->label('Judul Board')
                                    ->maxLength(150)
                                    ->placeholder('Contoh: Board Gacha Utama'),

                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->default(GachaBoard::STATUS_DRAFT)
                                    ->options([
                                        GachaBoard::STATUS_DRAFT => 'Draft',
                                        GachaBoard::STATUS_GENERATED => 'Generated',
                                        GachaBoard::STATUS_ACTIVE => 'Active',
                                        GachaBoard::STATUS_LOCKED => 'Locked',
                                        GachaBoard::STATUS_EXHAUSTED => 'Exhausted',
                                        GachaBoard::STATUS_CLOSED => 'Closed',
                                    ])
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Konfigurasi Slot')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('rows')
                                    ->label('Rows')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $rows = (int) ($state ?: 0);
                                        $cols = (int) ($get('cols') ?: 0);
                                        $total = $rows * $cols;

                                        if ($total > 0) {
                                            $set('total_slots', $total);

                                            if (blank($get('available_slots'))) {
                                                $set('available_slots', $total);
                                            }
                                        }
                                    }),

                                TextInput::make('cols')
                                    ->label('Columns')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $rows = (int) ($get('rows') ?: 0);
                                        $cols = (int) ($state ?: 0);
                                        $total = $rows * $cols;

                                        if ($total > 0) {
                                            $set('total_slots', $total);

                                            if (blank($get('available_slots'))) {
                                                $set('available_slots', $total);
                                            }
                                        }
                                    }),

                                TextInput::make('total_slots')
                                    ->label('Total Slot')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Otomatis dari Rows x Columns, tetap bisa disesuaikan manual.'),

                                TextInput::make('available_slots')
                                    ->label('Available Slots')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),

                                TextInput::make('popped_slots')
                                    ->label('Popped Slots')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                    ]),

                Section::make('Waktu & Generator')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('generated_by')
                                    ->label('Generated By')
                                    ->relationship('generator', 'name')
                                    ->searchable()
                                    ->preload(),

                                DateTimePicker::make('generated_at')
                                    ->label('Generated At')
                                    ->seconds(false),

                                DateTimePicker::make('activated_at')
                                    ->label('Activated At')
                                    ->seconds(false),

                                DateTimePicker::make('closed_at')
                                    ->label('Closed At')
                                    ->seconds(false),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Board')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('board_code')
                                    ->label('Kode Board')
                                    ->copyable(),

                                TextEntry::make('title')
                                    ->label('Judul Board')
                                    ->placeholder('-'),

                                TextEntry::make('campaign.name')
                                    ->label('Campaign'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        GachaBoard::STATUS_DRAFT => 'gray',
                                        GachaBoard::STATUS_GENERATED => 'info',
                                        GachaBoard::STATUS_ACTIVE => 'success',
                                        GachaBoard::STATUS_LOCKED => 'warning',
                                        GachaBoard::STATUS_EXHAUSTED => 'danger',
                                        GachaBoard::STATUS_CLOSED => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Section::make('Statistik Slot')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('rows')
                                    ->label('Rows')
                                    ->numeric(),

                                TextEntry::make('cols')
                                    ->label('Columns')
                                    ->numeric(),

                                TextEntry::make('total_slots')
                                    ->label('Total Slot')
                                    ->numeric(),

                                TextEntry::make('available_slots')
                                    ->label('Available Slots')
                                    ->numeric(),

                                TextEntry::make('popped_slots')
                                    ->label('Popped Slots')
                                    ->numeric(),

                                TextEntry::make('slots_count')
                                    ->label('Slot Records')
                                    ->state(fn (GachaBoard $record): int => $record->slots()->count()),
                            ]),
                    ]),

                Section::make('Draw & Waktu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draws_count')
                                    ->label('Total Draw')
                                    ->state(fn (GachaBoard $record): int => $record->draws()->count()),

                                TextEntry::make('generator.name')
                                    ->label('Generated By')
                                    ->placeholder('-'),

                                TextEntry::make('generated_at')
                                    ->label('Generated At')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('activated_at')
                                    ->label('Activated At')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('closed_at')
                                    ->label('Closed At')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('board_code')
            ->columns([
                TextColumn::make('board_code')
                    ->label('Kode Board')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        GachaBoard::STATUS_DRAFT => 'gray',
                        GachaBoard::STATUS_GENERATED => 'info',
                        GachaBoard::STATUS_ACTIVE => 'success',
                        GachaBoard::STATUS_LOCKED => 'warning',
                        GachaBoard::STATUS_EXHAUSTED => 'danger',
                        GachaBoard::STATUS_CLOSED => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('rows')
                    ->label('Rows')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('cols')
                    ->label('Cols')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_slots')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('available_slots')
                    ->label('Available')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('popped_slots')
                    ->label('Popped')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('slots_count')
                    ->label('Slot Records')
                    ->counts('slots')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('draws_count')
                    ->label('Draws')
                    ->counts('draws')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('generator.name')
                    ->label('Generated By')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('generated_at')
                    ->label('Generated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('activated_at')
                    ->label('Activated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('closed_at')
                    ->label('Closed')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        GachaBoard::STATUS_DRAFT => 'Draft',
                        GachaBoard::STATUS_GENERATED => 'Generated',
                        GachaBoard::STATUS_ACTIVE => 'Active',
                        GachaBoard::STATUS_LOCKED => 'Locked',
                        GachaBoard::STATUS_EXHAUSTED => 'Exhausted',
                        GachaBoard::STATUS_CLOSED => 'Closed',
                    ]),

                SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('generated_by')
                    ->label('Generated By')
                    ->relationship('generator', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGachaBoards::route('/'),
        ];
    }
}
