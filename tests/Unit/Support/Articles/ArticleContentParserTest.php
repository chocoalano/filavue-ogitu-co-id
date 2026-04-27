<?php

use App\Support\Articles\ArticleContentParser;

it('normalizes legacy blocks correctly', function (): void {
    $parser = new ArticleContentParser;

    $blocks = $parser->normalizeBlocks([
        [
            'type' => 'heading',
            'content' => [
                'level' => 2,
                'text' => 'Judul Legacy',
            ],
        ],
        [
            'type' => 'paragraph',
            'content' => [
                'text' => '<p>Konten <strong>legacy</strong></p>',
            ],
        ],
        [
            'type' => 'list',
            'content' => [
                'ordered' => false,
                'items' => ['Item A', 'Item B'],
            ],
        ],
        [
            'type' => 'image',
            'content' => [
                'url' => '/storage/articles/legacy.jpg',
                'alt' => 'Legacy image',
            ],
        ],
    ]);

    expect($blocks)->toHaveCount(4)
        ->and($blocks[0]['type'])->toBe('heading')
        ->and($blocks[0]['text'])->toBe('Judul Legacy')
        ->and($blocks[1]['type'])->toBe('rich_text')
        ->and($blocks[2]['type'])->toBe('list')
        ->and($blocks[3]['type'])->toBe('image')
        ->and($parser->extractFirstImageFromBlocks($blocks))->toBe('/media/public/articles/legacy.jpg');
});

it('normalizes builder blocks correctly', function (): void {
    $parser = new ArticleContentParser;

    $blocks = $parser->normalizeBlocks([
        [
            'type' => 'heading',
            'data' => [
                'level' => '3',
                'content' => 'Heading Builder',
            ],
        ],
        [
            'type' => 'rich_text',
            'data' => [
                'content' => '<p>Builder text</p>',
            ],
        ],
        [
            'type' => 'quote',
            'data' => [
                'quote' => 'Builder quote',
                'cite' => 'Builder cite',
            ],
        ],
    ]);

    expect($blocks)->toHaveCount(3)
        ->and($blocks[0]['type'])->toBe('heading')
        ->and($blocks[0]['level'])->toBe(3)
        ->and($blocks[1]['type'])->toBe('rich_text')
        ->and($blocks[2]['type'])->toBe('quote');

    $plainText = $parser->extractPlainTextFromBlocks($blocks);

    expect($plainText)->toContain('Heading Builder')
        ->toContain('Builder text')
        ->toContain('Builder quote')
        ->and($parser->estimateReadTimeMinutes($plainText))->toBeGreaterThanOrEqual(1);
});

it('falls back to unknown block type safely', function (): void {
    $parser = new ArticleContentParser;

    $blocks = $parser->normalizeBlocks([
        [
            'type' => 'custom_hero',
            'content' => [
                'title' => 'Hero Block',
                'cta' => 'Klik',
            ],
        ],
    ]);

    expect($blocks)->toHaveCount(1)
        ->and($blocks[0]['type'])->toBe('unknown')
        ->and($blocks[0]['block_type'])->toBe('custom_hero')
        ->and($blocks[0]['data'])->toBeArray();
});

it('normalizes tiptap rich text json into html for article blocks', function (): void {
    $parser = new ArticleContentParser;

    $tiptapDocument = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 2,
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Judul Tiptap',
                    ],
                ],
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Konten TipTap',
                    ],
                ],
            ],
        ],
    ];

    $blocks = $parser->normalizeBlocks([
        [
            'type' => 'rich_text',
            'data' => [
                'content' => $tiptapDocument,
            ],
        ],
    ]);

    expect($blocks)->toHaveCount(1)
        ->and($blocks[0]['type'])->toBe('rich_text')
        ->and($blocks[0]['html'])->toContain('<h2>Judul Tiptap</h2>')
        ->and($blocks[0]['html'])->toContain('Konten TipTap');
});

it('handles legacy tiptap heading node without level attrs safely', function (): void {
    $parser = new ArticleContentParser;

    $blocks = $parser->normalizeBlocks([
        [
            'type' => 'rich_text',
            'data' => [
                'content' => [
                    [
                        'type' => 'heading',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Judul Legacy',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($blocks)->toHaveCount(1)
        ->and($blocks[0]['type'])->toBe('rich_text')
        ->and($blocks[0]['html'])->toContain('<h2>Judul Legacy</h2>');
});
