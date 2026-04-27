<?php

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\Dashboard\DashboardService;
use Illuminate\Support\Facades\Storage;

it('normalizes public storage relative path from several product image formats', function (): void {
    $service = (new ReflectionClass(DashboardService::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(DashboardService::class, 'extractPublicStorageRelativePath');
    $method->setAccessible(true);

    expect($method->invoke($service, '/storage/products/media/available.webp'))
        ->toBe('products/media/available.webp')
        ->and($method->invoke($service, 'storage/products/media/available.webp'))
        ->toBe('products/media/available.webp')
        ->and($method->invoke($service, 'public/products/media/available.webp'))
        ->toBe('products/media/available.webp')
        ->and($method->invoke($service, 'https://localhost:8000/storage/products/media/available.webp'))
        ->toBe('products/media/available.webp');
});

it('detects whether a product media path is readable on public disk', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('products/media/available.webp', 'image-binary');

    $service = (new ReflectionClass(DashboardService::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(DashboardService::class, 'isProductMediaPathReadable');
    $method->setAccessible(true);

    expect($method->invoke($service, 'products/media/available.webp'))
        ->toBeTrue()
        ->and($method->invoke($service, '/storage/products/media/available.webp'))
        ->toBeTrue()
        ->and($method->invoke($service, 'products/media/missing.webp'))
        ->toBeFalse()
        ->and($method->invoke($service, 'https://cdn.example.com/image.webp'))
        ->toBeTrue();
});

it('prefers first readable product media path for dashboard order items', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('products/media/available.webp', 'image-binary');

    $product = new Product;
    $product->setRelation('primaryMedia', collect([
        new ProductMedia(['url' => 'products/media/missing.webp', 'sort_order' => 1]),
    ]));
    $product->setRelation('media', collect([
        new ProductMedia(['url' => 'products/media/available.webp', 'sort_order' => 2]),
    ]));

    $orderItem = new OrderItem;
    $orderItem->setRelation('product', $product);

    $service = (new ReflectionClass(DashboardService::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(DashboardService::class, 'resolveOrderItemProductImagePath');
    $method->setAccessible(true);

    expect($method->invoke($service, $orderItem))->toBe('products/media/available.webp');
});
