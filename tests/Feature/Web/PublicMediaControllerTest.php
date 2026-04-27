<?php

use Illuminate\Support\Facades\Storage;

it('serves files from public disk via media route', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('categories/sample.webp', 'webp-binary');

    $response = $this->get('/media/public/categories/sample.webp');

    $response
        ->assertOk()
        ->assertHeader('x-content-type-options', 'nosniff');

    expect((string) $response->headers->get('cache-control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

it('returns not found when public media file does not exist', function (): void {
    Storage::fake('public');

    $this->get('/media/public/categories/missing.webp')->assertNotFound();
});

it('blocks path traversal attempts for public media route', function (): void {
    Storage::fake('public');

    $this->get('/media/public/../../.env')->assertNotFound();
});
