<?php

use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Filament\Resources\Articles\Schemas\ArticleInfolist;

it('normalizes legacy rich text builder nodes into a valid tiptap doc', function (): void {
    $legacyBuilderState = [
        [
            'type' => 'rich_text',
            'data' => [
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Konten legacy',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $normalized = invokeProtectedStatic(ArticleForm::class, 'normalizeBuilderState', [$legacyBuilderState]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['type'])->toBe('rich_text')
        ->and($normalized[0]['data']['content']['type'])->toBe('doc')
        ->and($normalized[0]['data']['content']['content'])->toBeArray()
        ->and($normalized[0]['data']['content']['content'][0]['type'])->toBe('paragraph');

    $encoded = invokeProtectedStatic(ArticleForm::class, 'encodeBuilderStateForStorage', [$legacyBuilderState]);
    $decoded = json_decode($encoded, true);

    expect($decoded)->toBeArray()
        ->and($decoded[0]['data']['content']['type'] ?? null)->toBe('doc')
        ->and($decoded[0]['data']['content']['content'] ?? null)->toBeArray();
});

it('fills default heading level for legacy tiptap heading nodes', function (): void {
    $legacyBuilderState = [
        [
            'type' => 'rich_text',
            'data' => [
                'content' => [
                    [
                        'type' => 'heading',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Judul tanpa level',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $normalized = invokeProtectedStatic(ArticleForm::class, 'normalizeBuilderState', [$legacyBuilderState]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['data']['content']['type'])->toBe('doc')
        ->and($normalized[0]['data']['content']['content'][0]['type'])->toBe('heading')
        ->and($normalized[0]['data']['content']['content'][0]['attrs']['level'] ?? null)->toBe(2);
});

it('renders legacy rich text node lists safely in article infolist', function (): void {
    $html = invokeProtectedStatic(ArticleInfolist::class, 'renderRichTextBlock', [[
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Konten aman',
                    ],
                ],
            ],
        ],
    ]]);

    expect($html)->toBeString()->toContain('Konten aman');
});

it('renders legacy heading node without attrs level safely in article infolist', function (): void {
    $html = invokeProtectedStatic(ArticleInfolist::class, 'renderRichTextBlock', [[
        'content' => [
            [
                'type' => 'heading',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Judul aman',
                    ],
                ],
            ],
        ],
    ]]);

    expect($html)->toBeString()->toContain('Judul aman');
});

function invokeProtectedStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
