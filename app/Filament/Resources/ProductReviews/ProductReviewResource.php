<?php

namespace App\Filament\Resources\ProductReviews;

use App\Filament\Resources\ProductReviews\Pages\CreateProductReview;
use App\Filament\Resources\ProductReviews\Pages\EditProductReview;
use App\Filament\Resources\ProductReviews\Pages\ListProductReviews;
use App\Filament\Resources\ProductReviews\Pages\ViewProductReview;
use App\Filament\Resources\ProductReviews\Schemas\ProductReviewForm;
use App\Filament\Resources\ProductReviews\Schemas\ProductReviewInfolist;
use App\Filament\Resources\ProductReviews\Tables\ProductReviewsTable;
use App\Models\ProductReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $modelLabel = 'Review Produk';
    protected static ?string $pluralModelLabel = 'Review Produk';
    protected static ?string $navigationLabel = 'Review Produk';
    protected static string | UnitEnum | null $navigationGroup = 'Toko';

    public static function form(Schema $schema): Schema
    {
        return ProductReviewForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductReviewInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductReviews::route('/'),
            'create' => CreateProductReview::route('/create'),
            'view' => ViewProductReview::route('/{record}'),
            'edit' => EditProductReview::route('/{record}/edit'),
        ];
    }
}
