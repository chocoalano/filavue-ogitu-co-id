<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Resources\Contents\Pages\CreateContent;
use App\Filament\Resources\Contents\Pages\EditContent;
use App\Filament\Resources\Contents\Pages\ListContents;
use App\Filament\Resources\Contents\Pages\ViewContent;
use App\Filament\Resources\Contents\Schemas\ContentForm;
use App\Filament\Resources\Contents\Schemas\ContentInfolist;
use App\Filament\Resources\Contents\Tables\ContentsTable;
use App\Models\Content;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $navigationLabel = 'Modul / Konten';

    protected static ?string $modelLabel = 'Modul';

    protected static ?string $pluralModelLabel = 'Modul & Konten';

    protected static string|UnitEnum|null $navigationGroup = 'Ogitu Club';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'view' => ViewContent::route('/{record}'),
            'edit' => EditContent::route('/{record}/edit'),
        ];
    }
}
