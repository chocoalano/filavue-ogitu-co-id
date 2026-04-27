<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Storage;

it('returns null when category image path points to missing public file', function (): void {
    Storage::fake('public');

    $middleware = new HandleInertiaRequests;
    $method = new ReflectionMethod(HandleInertiaRequests::class, 'resolveExistingCategoryImageUrl');
    $method->setAccessible(true);

    expect($method->invoke($middleware, 'categories/missing.webp'))->toBeNull();
});

it('returns media route url when category image exists on public disk', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('categories/existing.webp', 'webp-binary');

    $middleware = new HandleInertiaRequests;
    $method = new ReflectionMethod(HandleInertiaRequests::class, 'resolveExistingCategoryImageUrl');
    $method->setAccessible(true);

    expect($method->invoke($middleware, 'categories/existing.webp'))
        ->toBe('/media/public/categories/existing.webp');
});
