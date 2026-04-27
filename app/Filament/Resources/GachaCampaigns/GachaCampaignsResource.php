<?php

namespace App\Filament\Resources\GachaCampaigns;

use App\Filament\Resources\GachaCampaigns\Pages\ManageGachaCampaigns;
use App\Models\GachaCampaign;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class GachaCampaignsResource extends Resource
{
    protected static ?string $model = GachaCampaign::class;

    protected static ?string $navigationLabel = 'Gacha Campaigns';

    protected static ?string $modelLabel = 'Gacha Campaign';

    protected static ?string $pluralModelLabel = 'Gacha Campaigns';

    protected static ?string $recordTitleAttribute = 'name';
    protected static string | UnitEnum | null $navigationGroup = 'Gatcha System';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Campaign')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kode Campaign')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: GCH-2026-001'),

                                TextInput::make('name')
                                    ->label('Nama Campaign')
                                    ->required()
                                    ->maxLength(150)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set): void {
                                        if (blank($state)) {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(180)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('contoh-gacha-campaign'),

                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->default(GachaCampaign::STATUS_DRAFT)
                                    ->options([
                                        GachaCampaign::STATUS_DRAFT => 'Draft',
                                        GachaCampaign::STATUS_SCHEDULED => 'Scheduled',
                                        GachaCampaign::STATUS_ACTIVE => 'Active',
                                        GachaCampaign::STATUS_PAUSED => 'Paused',
                                        GachaCampaign::STATUS_ENDED => 'Ended',
                                        GachaCampaign::STATUS_ARCHIVED => 'Archived',
                                    ])
                                    ->native(false),

                                Select::make('gacha_model')
                                    ->label('Model Gacha')
                                    ->required()
                                    ->default('balloon_pop')
                                    ->options([
                                        'balloon_pop' => 'Balloon Pop',
                                        'random_draw' => 'Random Draw',
                                        'spin' => 'Spin',
                                    ])
                                    ->native(false),

                                TextInput::make('point_cost_per_draw')
                                    ->label('Biaya Point per Draw')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Contoh: 100'),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Limit & Aturan Draw')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_draw_per_customer_per_day')
                                    ->label('Maks. Draw per Customer per Hari')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Kosongkan jika tanpa limit harian'),

                                TextInput::make('max_draw_per_customer_total')
                                    ->label('Maks. Total Draw per Customer')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Kosongkan jika tanpa limit total'),

                                Toggle::make('requires_manual_pick')
                                    ->label('Customer Pilih Slot Manual')
                                    ->default(true)
                                    ->helperText('Aktifkan jika customer harus memilih balon/slot sendiri.'),

                                Toggle::make('guaranteed_prize')
                                    ->label('Pasti Dapat Hadiah')
                                    ->default(true)
                                    ->helperText('Aktifkan jika setiap draw wajib menghasilkan reward.'),
                            ]),
                    ]),

                Section::make('Jadwal Campaign')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_at')
                                    ->label('Mulai')
                                    ->seconds(false),

                                DateTimePicker::make('end_at')
                                    ->label('Berakhir')
                                    ->seconds(false)
                                    ->after('start_at'),
                            ]),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('banner_image')
                            ->label('Banner Campaign')
                            ->image()
                            ->directory('gacha-campaigns')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Terms & Metadata')
                    ->schema([
                        KeyValue::make('terms_json')
                            ->label('Terms JSON')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable(),

                        KeyValue::make('metadata_json')
                            ->label('Metadata JSON')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Audit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Created By')
                                    ->relationship('creator', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('updated_by')
                                    ->label('Updated By')
                                    ->relationship('updater', 'name')
                                    ->searchable()
                                    ->preload(),
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
                Section::make('Informasi Campaign')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Kode Campaign')
                                    ->copyable(),

                                TextEntry::make('name')
                                    ->label('Nama Campaign'),

                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->copyable(),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        GachaCampaign::STATUS_DRAFT => 'gray',
                                        GachaCampaign::STATUS_SCHEDULED => 'info',
                                        GachaCampaign::STATUS_ACTIVE => 'success',
                                        GachaCampaign::STATUS_PAUSED => 'warning',
                                        GachaCampaign::STATUS_ENDED => 'danger',
                                        GachaCampaign::STATUS_ARCHIVED => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('gacha_model')
                                    ->label('Model Gacha')
                                    ->badge(),

                                TextEntry::make('point_cost_per_draw')
                                    ->label('Biaya Point per Draw')
                                    ->numeric(),

                                TextEntry::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Limit & Aturan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('max_draw_per_customer_per_day')
                                    ->label('Maks. Draw per Hari')
                                    ->placeholder('Tanpa limit'),

                                TextEntry::make('max_draw_per_customer_total')
                                    ->label('Maks. Total Draw')
                                    ->placeholder('Tanpa limit'),

                                IconEntry::make('requires_manual_pick')
                                    ->label('Manual Pick')
                                    ->boolean(),

                                IconEntry::make('guaranteed_prize')
                                    ->label('Guaranteed Prize')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Jadwal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('start_at')
                                    ->label('Mulai')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('end_at')
                                    ->label('Berakhir')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Media')
                    ->schema([
                        ImageEntry::make('banner_image')
                            ->label('Banner')
                            ->placeholder('-'),
                    ])
                    ->collapsible(),

                Section::make('Relasi Data')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reward_rules_count')
                                    ->label('Reward Rules')
                                    ->state(fn (GachaCampaign $record): int => $record->rewardRules()->count()),

                                TextEntry::make('boards_count')
                                    ->label('Boards')
                                    ->state(fn (GachaCampaign $record): int => $record->boards()->count()),

                                TextEntry::make('draws_count')
                                    ->label('Draws')
                                    ->state(fn (GachaCampaign $record): int => $record->draws()->count()),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Terms & Metadata')
                    ->schema([
                        TextEntry::make('terms_json')
                            ->label('Terms JSON')
                            ->formatStateUsing(fn ($state): string => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-'
                            )
                            ->columnSpanFull(),

                        TextEntry::make('metadata_json')
                            ->label('Metadata JSON')
                            ->formatStateUsing(fn ($state): string => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-'
                            )
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Audit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->placeholder('-'),

                                TextEntry::make('updater.name')
                                    ->label('Updated By')
                                    ->placeholder('-'),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('banner_image')
                    ->label('Banner')
                    ->width(70)
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Nama Campaign')
                    ->searchable()
                    ->sortable()
                    ->description(fn (GachaCampaign $record): ?string => $record->slug),

                TextColumn::make('gacha_model')
                    ->label('Model')
                    ->badge()
                    ->sortable(),

                TextColumn::make('point_cost_per_draw')
                    ->label('Point / Draw')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        GachaCampaign::STATUS_DRAFT => 'gray',
                        GachaCampaign::STATUS_SCHEDULED => 'info',
                        GachaCampaign::STATUS_ACTIVE => 'success',
                        GachaCampaign::STATUS_PAUSED => 'warning',
                        GachaCampaign::STATUS_ENDED => 'danger',
                        GachaCampaign::STATUS_ARCHIVED => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('requires_manual_pick')
                    ->label('Manual')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('guaranteed_prize')
                    ->label('Prize')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('boards_count')
                    ->label('Boards')
                    ->counts('boards')
                    ->sortable(),

                TextColumn::make('draws_count')
                    ->label('Draws')
                    ->counts('draws')
                    ->sortable(),

                TextColumn::make('start_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('end_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('creator.name')
                    ->label('Created By')
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
                        GachaCampaign::STATUS_DRAFT => 'Draft',
                        GachaCampaign::STATUS_SCHEDULED => 'Scheduled',
                        GachaCampaign::STATUS_ACTIVE => 'Active',
                        GachaCampaign::STATUS_PAUSED => 'Paused',
                        GachaCampaign::STATUS_ENDED => 'Ended',
                        GachaCampaign::STATUS_ARCHIVED => 'Archived',
                    ]),

                SelectFilter::make('gacha_model')
                    ->label('Model Gacha')
                    ->options([
                        'balloon_pop' => 'Balloon Pop',
                        'random_draw' => 'Random Draw',
                        'spin' => 'Spin',
                    ]),
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
            'index' => ManageGachaCampaigns::route('/'),
        ];
    }
}
