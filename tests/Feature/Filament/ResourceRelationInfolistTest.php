<?php

use App\Filament\Resources\Carts\CartResource;
use App\Filament\Resources\Carts\Schemas\CartInfolist;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Schemas\CategoryInfolist;
use App\Filament\Resources\ContentCategories\ContentCategoryResource;
use App\Filament\Resources\ContentCategories\Schemas\ContentCategoryInfolist;
use App\Filament\Resources\CustomerStockists\CustomerStockistResource;
use App\Filament\Resources\CustomerStockists\Schemas\CustomerStockistInfolist;
use App\Filament\Resources\ProductReviews\ProductReviewResource;
use App\Filament\Resources\ProductReviews\Schemas\ProductReviewInfolist;
use App\Filament\Resources\Products\Livewire\ProductOrderItemsTable;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Schemas\PromotionInfolist;
use App\Filament\Resources\WhatsAppBroadcasts\Schemas\WhatsAppBroadcastInfolist;
use App\Filament\Resources\WhatsAppBroadcasts\WhatsAppBroadcastResource;
use App\Filament\Resources\Wishlists\Schemas\WishlistInfolist;
use App\Filament\Resources\Wishlists\WishlistResource;

it('removes relation managers from impacted resources', function (string $resourceClass): void {
    expect($resourceClass::getRelations())->toBe([]);
})->with([
    CartResource::class,
    CategoryResource::class,
    ContentCategoryResource::class,
    CustomerStockistResource::class,
    ProductReviewResource::class,
    ProductResource::class,
    PromotionResource::class,
    WhatsAppBroadcastResource::class,
    WishlistResource::class,
]);

it('stores relation details in infolist schema definitions', function (string $schemaClass, array $snippets): void {
    $reflection = new ReflectionClass($schemaClass);
    $filePath = $reflection->getFileName();
    $source = is_string($filePath) ? file_get_contents($filePath) : false;

    expect($source)->toBeString();

    foreach ($snippets as $snippet) {
        expect($source)->toContain($snippet);
    }
})->with([
    [CartInfolist::class, [
        "TextEntry::make('items_count')",
        "RepeatableEntry::make('items')",
    ]],
    [CategoryInfolist::class, [
        "RepeatableEntry::make('children')",
        "RepeatableEntry::make('products')",
    ]],
    [ContentCategoryInfolist::class, [
        "RepeatableEntry::make('children')",
        "RepeatableEntry::make('contents')",
    ]],
    [CustomerStockistInfolist::class, [
        'BaseCustomerInfolist::configure($schema)',
    ]],
    [ProductReviewInfolist::class, [
        "TextEntry::make('customer.email')",
        "TextEntry::make('orderItem.order.order_no')",
    ]],
    [ProductInfolist::class, [
        "RepeatableEntry::make('promotions')",
        "RepeatableEntry::make('reviews')",
        "Livewire::make('filament.products.order-items-table')",
        "RepeatableEntry::make('cartItems')",
    ]],
    [PromotionInfolist::class, [
        "TextEntry::make('products_count')",
        "RepeatableEntry::make('products')",
    ]],
    [WhatsAppBroadcastInfolist::class, [
        "TextEntry::make('recipients_count')",
        "RepeatableEntry::make('recipients')",
    ]],
    [WishlistInfolist::class, [
        "TextEntry::make('items_count')",
        "RepeatableEntry::make('items')",
    ]],
]);

it('removes relation manager directories from filament resources', function (): void {
    $directories = glob(app_path('Filament/Resources/*/RelationManagers'));

    expect($directories)->toBeArray()->toBeEmpty();
});

it('paginates product order items infolist table', function (): void {
    $reflection = new ReflectionClass(ProductOrderItemsTable::class);
    $filePath = $reflection->getFileName();
    $source = is_string($filePath) ? file_get_contents($filePath) : false;

    expect($source)->toBeString()
        ->and($source)->toContain('->paginated([10, 25, 50])')
        ->and($source)->toContain('->defaultPaginationPageOption(10)');
});
