<?php

use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\ContentCategories\Schemas\ContentCategoryForm;
use App\Filament\Resources\Contents\Schemas\ContentForm;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Filament\Resources\Promotions\Schemas\PromotionForm;

it('uses public disk for webp optimized uploads that are intended to be public', function (string $schemaClass, array $snippets): void {
    $reflection = new ReflectionClass($schemaClass);
    $filePath = $reflection->getFileName();
    $source = is_string($filePath) ? file_get_contents($filePath) : false;

    expect($source)->toBeString();

    foreach ($snippets as $snippet) {
        expect($source)->toContain($snippet);
    }
})->with([
    [CategoryForm::class, [
        "FileUpload::make('image')",
        "->optimize('webp')",
        "->disk('public')",
        "->visibility('public')",
    ]],
    [ContentCategoryForm::class, [
        "FileUpload::make('thumbnail_url')",
        "->optimize('webp')",
        "->disk('public')",
        "->visibility('public')",
    ]],
    [ContentForm::class, [
        "FileUpload::make('thumbnail_url')",
        "->optimize('webp')",
        "->disk('public')",
        "->visibility('public')",
    ]],
    [PromotionForm::class, [
        "FileUpload::make('image')",
        "->optimize('webp')",
        "->disk('public')",
        "->visibility('public')",
    ]],
    [PageForm::class, [
        "FileUpload::make('image')",
        "->optimize('webp')",
        "->disk('public')",
        "->visibility('public')",
        "FileUpload::make('avatar')",
        "->directory('pages/testimonials')",
    ]],
]);
