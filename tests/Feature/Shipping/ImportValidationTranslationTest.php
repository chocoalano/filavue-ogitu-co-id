<?php

it('has localized max file validation message in indonesian locale', function (): void {
    app()->setLocale('id');

    $message = trans('validation.max.file', [
        'attribute' => 'file',
        'max' => 10240,
    ]);

    expect($message)->not->toBe('validation.max.file')
        ->and($message)->toContain('Ukuran file')
        ->and($message)->toContain('10240 KB');
});
