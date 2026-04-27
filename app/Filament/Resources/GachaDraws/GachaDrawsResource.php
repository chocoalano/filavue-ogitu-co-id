<?php

namespace App\Filament\Resources\GachaDraws;

use App\Filament\Resources\GachaDraws\Pages\ManageGachaDraws;
use App\Models\GachaDraw;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
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

class GachaDrawsResource extends Resource
{
    protected static ?string $model = GachaDraw::class;

    protected static ?string $navigationLabel = 'Riwayat Gacha Draw';

    protected static ?string $modelLabel = 'Gacha Draw';

    protected static ?string $pluralModelLabel = 'Riwayat Gacha Draw';

    protected static ?string $recordTitleAttribute = 'draw_no';
    protected static string | UnitEnum | null $navigationGroup = 'Gatcha System';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Draw')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('draw_no')
                                    ->label('Draw No')
                                    ->required()
                                    ->maxLength(60)
                                    ->unique(ignoreRecord: true),

                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->default('confirmed')
                                    ->options([
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'cancelled' => 'Cancelled',
                                        'failed' => 'Failed',
                                    ])
                                    ->native(false),

                                Select::make('campaign_id')
                                    ->label('Campaign')
                                    ->relationship('campaign', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('board_id')
                                    ->label('Board')
                                    ->relationship('board', 'board_code')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('slot_id')
                                    ->label('Slot / Balloon')
                                    ->relationship('slot', 'balloon_code')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('points_spent')
                                    ->label('Points Spent')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),

                                Select::make('channel')
                                    ->label('Channel')
                                    ->required()
                                    ->default('web')
                                    ->options([
                                        'web' => 'Web',
                                        'mobile' => 'Mobile',
                                        'admin' => 'Admin',
                                        'system' => 'System',
                                    ])
                                    ->native(false),

                                DateTimePicker::make('drawn_at')
                                    ->label('Drawn At')
                                    ->required()
                                    ->seconds(false)
                                    ->default(now()),
                            ]),
                    ]),

                Section::make('Reward & Point Ledger')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('point_account_id')
                                    ->label('Point Account')
                                    ->relationship('pointAccount', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('point_ledger_id')
                                    ->label('Point Ledger')
                                    ->relationship('pointLedger', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('reward_item_id')
                                    ->label('Reward Item')
                                    ->relationship('rewardItem', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('reward_instance_id')
                                    ->label('Reward Instance')
                                    ->relationship('rewardInstance', 'id')
                                    ->searchable()
                                    ->preload(),

                                Select::make('handled_by_user_id')
                                    ->label('Handled By')
                                    ->relationship('handledBy', 'name')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('idempotency_key')
                                    ->label('Idempotency Key')
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true),
                            ]),
                    ]),

                Section::make('Result Snapshot')
                    ->schema([
                        KeyValue::make('result_snapshot_json')
                            ->label('Result Snapshot JSON')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Draw')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draw_no')
                                    ->label('Draw No'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),

                                TextEntry::make('campaign.name')
                                    ->label('Campaign'),

                                TextEntry::make('board.board_code')
                                    ->label('Board'),

                                TextEntry::make('slot.balloon_code')
                                    ->label('Slot / Balloon'),

                                TextEntry::make('customer.name')
                                    ->label('Customer'),

                                TextEntry::make('points_spent')
                                    ->label('Points Spent')
                                    ->numeric(),

                                TextEntry::make('channel')
                                    ->label('Channel')
                                    ->badge(),

                                TextEntry::make('drawn_at')
                                    ->label('Drawn At')
                                    ->dateTime('d M Y H:i'),

                                TextEntry::make('handledBy.name')
                                    ->label('Handled By')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Reward & Ledger')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('point_account_id')
                                    ->label('Point Account ID'),

                                TextEntry::make('point_ledger_id')
                                    ->label('Point Ledger ID'),

                                TextEntry::make('rewardItem.name')
                                    ->label('Reward Item'),

                                TextEntry::make('reward_instance_id')
                                    ->label('Reward Instance ID')
                                    ->placeholder('-'),

                                TextEntry::make('idempotency_key')
                                    ->label('Idempotency Key')
                                    ->placeholder('-'),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ]),

                Section::make('Result Snapshot')
                    ->schema([
                        TextEntry::make('result_snapshot_json')
                            ->label('Result Snapshot')
                            ->formatStateUsing(fn ($state): string => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-'
                            )
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('draw_no')
            ->columns([
                TextColumn::make('draw_no')
                    ->label('Draw No')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('board.board_code')
                    ->label('Board')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slot.balloon_code')
                    ->label('Slot')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rewardItem.name')
                    ->label('Reward')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('points_spent')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('handledBy.name')
                    ->label('Handled By')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('drawn_at')
                    ->label('Drawn At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('drawn_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('channel')
                    ->label('Channel')
                    ->options([
                        'web' => 'Web',
                        'mobile' => 'Mobile',
                        'admin' => 'Admin',
                        'system' => 'System',
                    ]),

                SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('reward_item_id')
                    ->label('Reward')
                    ->relationship('rewardItem', 'name')
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
            'index' => ManageGachaDraws::route('/'),
        ];
    }
}
