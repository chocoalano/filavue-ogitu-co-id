<?php

namespace App\Filament\Resources\ContentCategories;

use App\Filament\Resources\ContentCategories\Pages\CreateContentCategory;
use App\Filament\Resources\ContentCategories\Pages\EditContentCategory;
use App\Filament\Resources\ContentCategories\Pages\ListContentCategories;
use App\Filament\Resources\ContentCategories\Pages\ViewContentCategory;
use App\Filament\Resources\ContentCategories\Schemas\ContentCategoryForm;
use App\Filament\Resources\ContentCategories\Schemas\ContentCategoryInfolist;
use App\Filament\Resources\ContentCategories\Tables\ContentCategoriesTable;
use App\Models\ContentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContentCategoryResource extends Resource
{
    protected static ?string $model = ContentCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Kursus / Kategori';

    protected static ?string $modelLabel = 'Kursus';

    protected static ?string $pluralModelLabel = 'Kursus & Kategori';

    protected static string|UnitEnum|null $navigationGroup = 'Ogitu Club';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ContentCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContentCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentCategories::route('/'),
            'create' => CreateContentCategory::route('/create'),
            'view' => ViewContentCategory::route('/{record}'),
            'edit' => EditContentCategory::route('/{record}/edit'),
        ];
    }
}
