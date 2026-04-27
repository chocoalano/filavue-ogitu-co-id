<?php

use App\Support\Pages\PageBlockParser;

it('normalizes all filament page builder block types', function (): void {
    $parser = new PageBlockParser;

    $rawBlocks = [
        [
            'type' => 'hero',
            'data' => [
                'headline' => 'Hero Headline',
                'subheadline' => 'Hero Subheadline',
                'image' => 'pages/hero.png',
            ],
        ],
        [
            'type' => 'section_rich',
            'data' => [
                'title' => 'Section Rich',
                'content' => '<p>Konten <strong>rich</strong></p>',
                'container' => 'lg',
                'with_divider' => true,
            ],
        ],
        [
            'type' => 'features',
            'data' => [
                'columns' => 3,
                'items' => [
                    ['title' => 'Fitur 1', 'description' => 'Desc 1'],
                    ['title' => 'Fitur 2', 'description' => 'Desc 2'],
                ],
            ],
        ],
        [
            'type' => 'cta',
            'data' => [
                'title' => 'CTA',
                'button_label' => 'Mulai',
                'button_url' => '/register',
                'style' => 'primary',
            ],
        ],
        [
            'type' => 'faq',
            'data' => [
                'title' => 'FAQ',
                'items' => [
                    ['q' => 'Apa ini?', 'a' => 'Ini contoh.'],
                ],
            ],
        ],
        [
            'type' => 'testimonials',
            'data' => [
                'items' => [
                    ['name' => 'User A', 'quote' => 'Bagus'],
                ],
            ],
        ],
        [
            'type' => 'divider',
            'data' => [],
        ],
        [
            'type' => 'spacer',
            'data' => ['size' => 'md'],
        ],
        [
            'type' => 'custom_html',
            'data' => ['html' => '<div>HTML custom</div>'],
        ],
    ];

    $normalized = $parser->normalizeBlocks($rawBlocks);
    $types = collect($normalized)->pluck('type')->all();

    expect($types)->toBe([
        'hero',
        'section_rich',
        'features',
        'cta',
        'faq',
        'testimonials',
        'divider',
        'spacer',
        'custom_html',
    ]);

    expect($normalized[0]['data']['image'])->toBe('/media/public/pages/hero.png')
        ->and($parser->extractFirstImage($normalized))->toBe('/media/public/pages/hero.png');

    $summary = $parser->extractSummary($normalized, null);

    expect($summary)->toContain('Hero Headline')
        ->toContain('Section Rich');
});

it('normalizes uuid keyed block arrays from filament builder', function (): void {
    $parser = new PageBlockParser;

    $rawBlocks = [
        'uuid-1' => [
            'type' => 'faq',
            'data' => [
                'items' => [
                    'item-1' => ['q' => 'Q1', 'a' => 'A1'],
                ],
            ],
        ],
    ];

    $normalized = $parser->normalizeBlocks($rawBlocks);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['type'])->toBe('faq')
        ->and($normalized[0]['data']['items'])->toBeArray()
        ->and($normalized[0]['data']['items'][0]['q'])->toBe('Q1');
});

it('normalizes legacy heading block data from content field', function (): void {
    $parser = new PageBlockParser;

    $normalized = $parser->normalizeBlocks([
        [
            'type' => 'heading',
            'content' => [
                'level' => 3,
                'text' => 'Heading Legacy',
            ],
        ],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['type'])->toBe('heading')
        ->and($normalized[0]['data']['level'])->toBe(3)
        ->and($normalized[0]['data']['text'])->toBe('Heading Legacy');
});

it('normalizes tiptap richtext json content into html', function (): void {
    $parser = new PageBlockParser;

    $tiptapDoc = [
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
                        'text' => 'Konten ',
                    ],
                    [
                        'type' => 'text',
                        'text' => 'penting',
                        'marks' => [
                            ['type' => 'bold'],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $normalized = $parser->normalizeBlocks([
        [
            'type' => 'section_rich',
            'data' => [
                'content' => $tiptapDoc,
            ],
        ],
        [
            'type' => 'rich_text',
            'data' => [
                'content' => json_encode($tiptapDoc),
            ],
        ],
    ]);

    expect($normalized)->toHaveCount(2)
        ->and($normalized[0]['data']['content'])->toContain('<h2>Judul Tiptap</h2>')
        ->and($normalized[0]['data']['content'])->toContain('<strong>penting</strong>')
        ->and($normalized[1]['data']['content'])->toContain('<h2>Judul Tiptap</h2>');
});
