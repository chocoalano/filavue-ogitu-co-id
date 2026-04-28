<?php

namespace App\Filament\Resources\JneDestinations;

use App\Filament\Exports\JneDestinationExporter;
use App\Filament\Resources\JneDestinations\Pages\ManageJneDestinations;
use App\Models\JneDestination;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JneDestinationResource extends Resource
{
    protected static ?string $model = JneDestination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'JneDestination';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('country_name')
                    ->required(),
                TextInput::make('province_name')
                    ->required(),
                TextInput::make('city_name')
                    ->required(),
                TextInput::make('district_name')
                    ->required(),
                TextInput::make('subdistrict_name')
                    ->required(),
                TextInput::make('zip_code'),
                TextInput::make('tariff_code')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('country_name'),
                TextEntry::make('province_name'),
                TextEntry::make('city_name'),
                TextEntry::make('district_name'),
                TextEntry::make('subdistrict_name'),
                TextEntry::make('zip_code')
                    ->placeholder('-'),
                TextEntry::make('tariff_code'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('JneDestination')
            ->columns([
                TextColumn::make('country_name')
                    ->searchable(),
                TextColumn::make('province_name')
                    ->searchable(),
                TextColumn::make('city_name')
                    ->searchable(),
                TextColumn::make('district_name')
                    ->searchable(),
                TextColumn::make('subdistrict_name')
                    ->searchable(),
                TextColumn::make('zip_code')
                    ->searchable(),
                TextColumn::make('tariff_code')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(JneDestinationExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageJneDestinations::route('/'),
        ];
    }
}
