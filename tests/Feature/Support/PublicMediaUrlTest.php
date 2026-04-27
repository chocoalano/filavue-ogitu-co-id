<?php

use App\Support\Media\PublicMediaUrl;

it('normalizes public storage relative path from multiple optimized image formats', function (): void {
    expect(PublicMediaUrl::extractPublicStorageRelativePath('/storage/products/media/optimized.webp'))
        ->toBe('products/media/optimized.webp')
        ->and(PublicMediaUrl::extractPublicStorageRelativePath('storage/products/media/optimized.webp'))
        ->toBe('products/media/optimized.webp')
        ->and(PublicMediaUrl::extractPublicStorageRelativePath('public/products/media/optimized.webp'))
        ->toBe('products/media/optimized.webp')
        ->and(PublicMediaUrl::extractPublicStorageRelativePath('/storage/public/products/media/optimized.webp'))
        ->toBe('products/media/optimized.webp')
        ->and(PublicMediaUrl::extractPublicStorageRelativePath('https://app.test/storage/public/products/media/optimized.webp'))
        ->toBe('products/media/optimized.webp');
});

it('resolves optimized image paths into valid public URLs for inertia payloads', function (): void {
    $expected = '/media/public/products/media/optimized.webp';

    expect(PublicMediaUrl::resolve('products/media/optimized.webp'))
        ->toBe($expected)
        ->and(PublicMediaUrl::resolve('/storage/products/media/optimized.webp'))
        ->toBe($expected)
        ->and(PublicMediaUrl::resolve('storage/products/media/optimized.webp'))
        ->toBe($expected)
        ->and(PublicMediaUrl::resolve('public/products/media/optimized.webp'))
        ->toBe($expected)
        ->and(PublicMediaUrl::resolve('/storage/public/products/media/optimized.webp'))
        ->toBe($expected)
        ->and(PublicMediaUrl::resolve('https://app.test/storage/public/products/media/optimized.webp'))
        ->toBe($expected);
});

it('keeps external and data image URLs unchanged', function (): void {
    $externalUrl = 'https://cdn.example.com/products/media/optimized.webp';
    $dataUrl = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBYAAAAwAQCdASoQABAAPmEkkkKhIAgAgA2JaQAA3AA/vuUAAA==';

    expect(PublicMediaUrl::resolve($externalUrl))
        ->toBe($externalUrl)
        ->and(PublicMediaUrl::resolve($dataUrl))
        ->toBe($dataUrl)
        ->and(PublicMediaUrl::resolve('/images/logo.webp'))
        ->toBe(asset('images/logo.webp'))
        ->and(PublicMediaUrl::resolve(''))
        ->toBeNull()
        ->and(PublicMediaUrl::resolve(null))
        ->toBeNull();
});
