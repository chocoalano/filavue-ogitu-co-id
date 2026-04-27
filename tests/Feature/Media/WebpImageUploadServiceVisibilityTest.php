<?php

use App\Services\Media\WebpImageUploadService;

it('normalizes upload visibility to supported values', function (): void {
    $service = new WebpImageUploadService;
    $method = new ReflectionMethod(WebpImageUploadService::class, 'normalizeVisibility');
    $method->setAccessible(true);

    expect($method->invoke($service, 'public'))
        ->toBe('public')
        ->and($method->invoke($service, 'private'))
        ->toBe('private')
        ->and($method->invoke($service, null))
        ->toBe('public')
        ->and($method->invoke($service, 'unknown'))
        ->toBe('public');
});
