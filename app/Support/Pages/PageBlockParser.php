<?php

namespace App\Support\Pages;

use App\Support\Media\PublicMediaUrl;
use Illuminate\Support\Str;

class PageBlockParser
{
    /**
     * @return array<int, array{type:string,data:array<string,mixed>}>
     */
    public function normalizeBlocks(mixed $rawBlocks): array
    {
        $decodedBlocks = $this->decodeValue($rawBlocks);

        if (! is_array($decodedBlocks)) {
            return [];
        }

        $blocks = $this->normalizeToList($decodedBlocks);

        return collect($blocks)
            ->map(function (mixed $block): ?array {
                $normalizedBlock = $this->decodeValue($block);

                if (! is_array($normalizedBlock) || ! isset($normalizedBlock['type'])) {
                    return null;
                }

                $type = $this->normalizeBlockType((string) $normalizedBlock['type']);
                $decodedData = $this->decodeValue($normalizedBlock['data'] ?? ($normalizedBlock['content'] ?? []));
                $data = is_array($decodedData) ? $decodedData : [];

                return [
                    'type' => $type,
                    'data' => $this->normalizeBlockData($type, $data),
                ];
            })
            ->filter(fn (?array $block): bool => is_array($block))
            ->values()
            ->all();
    }

    public function sanitizeHtml(string $html): string
    {
        $withoutScript = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $withoutStyle = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', is_string($withoutScript) ? $withoutScript : $html);

        return trim(is_string($withoutStyle) ? $withoutStyle : $html);
    }

    public function extractFirstImage(array $blocks): ?string
    {
        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? null) !== 'hero') {
                continue;
            }

            $heroData = is_array($block['data'] ?? null) ? $block['data'] : [];
            $image = $heroData['image'] ?? null;

            if (is_string($image) && $image !== '') {
                return $image;
            }
        }

        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? null) !== 'testimonials') {
                continue;
            }

            $testimonialItems = is_array($block['data']['items'] ?? null)
                ? $block['data']['items']
                : [];

            foreach ($testimonialItems as $item) {
                $avatar = is_array($item) ? ($item['avatar'] ?? null) : null;

                if (is_string($avatar) && $avatar !== '') {
                    return $avatar;
                }
            }
        }

        return null;
    }

    public function extractSummary(array $blocks, ?string $fallbackHtml): string
    {
        $parts = collect($blocks)
            ->map(function (array $block): string {
                $type = (string) ($block['type'] ?? '');
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];

                return match ($type) {
                    'hero' => trim((string) ($data['headline'] ?? '')).' '.trim((string) ($data['subheadline'] ?? '')),
                    'section_rich' => trim((string) ($data['title'] ?? '')).' '.strip_tags((string) ($data['content'] ?? '')),
                    'features' => trim((string) ($data['title'] ?? '')).' '.collect((array) ($data['items'] ?? []))
                        ->map(fn (mixed $item): string => is_array($item) ? trim((string) ($item['description'] ?? '')) : '')
                        ->implode(' '),
                    'cta' => trim((string) ($data['title'] ?? '')).' '.trim((string) ($data['description'] ?? '')),
                    'faq' => trim((string) ($data['title'] ?? '')).' '.collect((array) ($data['items'] ?? []))
                        ->map(fn (mixed $item): string => is_array($item) ? trim((string) ($item['q'] ?? '')) : '')
                        ->implode(' '),
                    'testimonials' => trim((string) ($data['title'] ?? '')).' '.collect((array) ($data['items'] ?? []))
                        ->map(fn (mixed $item): string => is_array($item) ? trim((string) ($item['quote'] ?? '')) : '')
                        ->implode(' '),
                    'heading' => trim((string) ($data['text'] ?? '')),
                    'rich_text', 'paragraph' => strip_tags((string) ($data['content'] ?? $data['text'] ?? '')),
                    'list' => collect((array) ($data['items'] ?? []))
                        ->map(fn (mixed $item): string => is_string($item) ? strip_tags($item) : '')
                        ->implode(' '),
                    'quote' => trim((string) ($data['quote'] ?? '')),
                    'custom_html' => strip_tags((string) ($data['html'] ?? '')),
                    default => '',
                };
            })
            ->filter(fn (string $text): bool => $text !== '')
            ->implode(' ');

        $summary = trim((string) preg_replace('/\s+/', ' ', $parts));

        if ($summary !== '') {
            return Str::limit($summary, 190);
        }

        if (is_string($fallbackHtml) && trim($fallbackHtml) !== '') {
            return Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags($fallbackHtml))), 190);
        }

        return 'Halaman informasi resmi dari '.config('app.name').'.';
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeBlockData(string $type, array $data): array
    {
        return match ($type) {
            'hero' => $this->normalizeHeroBlock($data),
            'section_rich' => $this->normalizeSectionRichBlock($data),
            'features' => $this->normalizeFeaturesBlock($data),
            'cta' => $this->normalizeCtaBlock($data),
            'faq' => $this->normalizeFaqBlock($data),
            'testimonials' => $this->normalizeTestimonialsBlock($data),
            'heading' => $this->normalizeHeadingBlock($data),
            'rich_text', 'paragraph', 'richtext' => $this->normalizeRichTextBlock($data),
            'image' => $this->normalizeImageBlock($data),
            'list' => $this->normalizeListBlock($data),
            'quote' => $this->normalizeQuoteBlock($data),
            'spacer' => $this->normalizeSpacerBlock($data),
            'custom_html' => $this->normalizeCustomHtmlBlock($data),
            'divider' => [],
            default => $data,
        };
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeHeroBlock(array $data): array
    {
        $align = trim((string) ($data['align'] ?? 'left'));
        $variant = trim((string) ($data['variant'] ?? 'image-right'));
        $rawImage = $this->normalizeSingleFileValue($data['image'] ?? null);

        return [
            'headline' => trim((string) ($data['headline'] ?? '')),
            'subheadline' => trim((string) ($data['subheadline'] ?? '')),
            'primary_cta_label' => trim((string) ($data['primary_cta_label'] ?? '')),
            'primary_cta_url' => trim((string) ($data['primary_cta_url'] ?? '')),
            'secondary_cta_label' => trim((string) ($data['secondary_cta_label'] ?? '')),
            'secondary_cta_url' => trim((string) ($data['secondary_cta_url'] ?? '')),
            'align' => in_array($align, ['left', 'center'], true) ? $align : 'left',
            'variant' => in_array($variant, ['image-right', 'image-left', 'image-bg', 'text-only'], true) ? $variant : 'image-right',
            'image' => is_string($rawImage) && $rawImage !== '' ? $this->normalizeImageUrl($rawImage) : null,
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeSectionRichBlock(array $data): array
    {
        $container = trim((string) ($data['container'] ?? 'lg'));

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'content' => $this->normalizeRichContentValue($data['content'] ?? ''),
            'container' => in_array($container, ['sm', 'md', 'lg', 'xl'], true) ? $container : 'lg',
            'with_divider' => (bool) ($data['with_divider'] ?? false),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeFeaturesBlock(array $data): array
    {
        $columns = (int) ($data['columns'] ?? 3);
        $items = $this->normalizeItems($data['items'] ?? []);

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'subtitle' => trim((string) ($data['subtitle'] ?? '')),
            'columns' => max(2, min(4, $columns)),
            'iconed' => (bool) ($data['iconed'] ?? true),
            'carded' => (bool) ($data['carded'] ?? true),
            'items' => collect($items)
                ->map(fn (array $item): array => [
                    'title' => trim((string) ($item['title'] ?? '')),
                    'icon' => trim((string) ($item['icon'] ?? '')),
                    'description' => trim((string) ($item['description'] ?? '')),
                ])
                ->filter(fn (array $item): bool => $item['title'] !== '' || $item['description'] !== '')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeCtaBlock(array $data): array
    {
        $style = trim((string) ($data['style'] ?? 'primary'));

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'button_label' => trim((string) ($data['button_label'] ?? '')),
            'button_url' => trim((string) ($data['button_url'] ?? '')),
            'style' => in_array($style, ['primary', 'secondary', 'outline'], true) ? $style : 'primary',
            'accent' => trim((string) ($data['accent'] ?? '')),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeFaqBlock(array $data): array
    {
        $items = $this->normalizeItems($data['items'] ?? []);

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'items' => collect($items)
                ->map(fn (array $item): array => [
                    'q' => trim((string) ($item['q'] ?? '')),
                    'a' => trim((string) ($item['a'] ?? '')),
                ])
                ->filter(fn (array $item): bool => $item['q'] !== '' || $item['a'] !== '')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeHeadingBlock(array $data): array
    {
        $level = (int) ($data['level'] ?? 2);

        return [
            'level' => max(1, min(6, $level)),
            'text' => trim((string) ($data['text'] ?? $data['content'] ?? '')),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeRichTextBlock(array $data): array
    {
        return [
            'content' => $this->normalizeRichContentValue($data['content'] ?? $data['text'] ?? ''),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeImageBlock(array $data): array
    {
        $rawImage = $this->normalizeSingleFileValue($data['url'] ?? $data['image'] ?? null);

        return [
            'url' => is_string($rawImage) && $rawImage !== '' ? $this->normalizeImageUrl($rawImage) : null,
            'alt' => trim((string) ($data['alt'] ?? '')),
            'caption' => trim((string) ($data['caption'] ?? '')),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeListBlock(array $data): array
    {
        $items = collect((array) ($data['items'] ?? []))
            ->map(fn (mixed $item): string => is_string($item) ? $this->sanitizeHtml($item) : '')
            ->filter(fn (string $item): bool => trim(strip_tags($item)) !== '')
            ->values()
            ->all();

        return [
            'ordered' => (bool) ($data['ordered'] ?? false),
            'items' => $items,
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeQuoteBlock(array $data): array
    {
        return [
            'quote' => trim((string) ($data['quote'] ?? $data['text'] ?? '')),
            'cite' => trim((string) ($data['cite'] ?? '')),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeTestimonialsBlock(array $data): array
    {
        $items = $this->normalizeItems($data['items'] ?? []);

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'items' => collect($items)
                ->map(function (array $item): array {
                    $rawAvatar = $this->normalizeSingleFileValue($item['avatar'] ?? null);

                    return [
                        'name' => trim((string) ($item['name'] ?? '')),
                        'role' => trim((string) ($item['role'] ?? '')),
                        'quote' => trim((string) ($item['quote'] ?? '')),
                        'avatar' => is_string($rawAvatar) && $rawAvatar !== ''
                            ? $this->normalizeImageUrl($rawAvatar)
                            : null,
                    ];
                })
                ->filter(fn (array $item): bool => $item['name'] !== '' || $item['quote'] !== '')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeSpacerBlock(array $data): array
    {
        $size = trim((string) ($data['size'] ?? 'md'));

        return [
            'size' => in_array($size, ['sm', 'md', 'lg', 'xl'], true) ? $size : 'md',
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function normalizeCustomHtmlBlock(array $data): array
    {
        return [
            'html' => $this->sanitizeHtml((string) ($data['html'] ?? '')),
            'meta' => is_array($data['meta'] ?? null) ? $data['meta'] : [],
        ];
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    private function normalizeItems(mixed $rawItems): array
    {
        $decodedItems = $this->decodeValue($rawItems);

        if (! is_array($decodedItems)) {
            return [];
        }

        return collect($this->normalizeToList($decodedItems))
            ->map(fn (mixed $item): mixed => $this->decodeValue($item))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->all();
    }

    private function normalizeSingleFileValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                $normalized = $this->normalizeSingleFileValue($item);

                if (is_string($normalized) && $normalized !== '') {
                    return $normalized;
                }
            }

            return null;
        }

        foreach (['path', 'url', 'value'] as $key) {
            if (is_string($value[$key] ?? null) && filled($value[$key])) {
                return $value[$key];
            }
        }

        return null;
    }

    private function normalizeImageUrl(string $rawUrl): string
    {
        if (str_starts_with($rawUrl, '/media/public/')) {
            return $rawUrl;
        }

        if (
            str_starts_with($rawUrl, 'http://')
            || str_starts_with($rawUrl, 'https://')
            || str_starts_with($rawUrl, 'data:')
        ) {
            return $rawUrl;
        }

        if (
            str_starts_with($rawUrl, '/')
            && ! str_starts_with($rawUrl, '/storage/')
            && ! str_starts_with($rawUrl, '/public/')
        ) {
            return $rawUrl;
        }

        return $this->resolvePublicMediaUrl($rawUrl) ?? $rawUrl;
    }

    private function decodeValue(mixed $value, int $maxDepth = 4): mixed
    {
        $decoded = $value;
        $depth = 0;

        while (is_string($decoded) && $depth < $maxDepth) {
            $next = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $decoded = $next;
            $depth++;
        }

        return $decoded;
    }

    /**
     * @param  array<string,mixed>|array<int,mixed>  $value
     * @return array<int,mixed>
     */
    private function normalizeToList(array $value): array
    {
        if (array_is_list($value)) {
            return $value;
        }

        if (array_key_exists('type', $value) && array_key_exists('data', $value)) {
            return [$value];
        }

        return array_values($value);
    }

    private function normalizeBlockType(string $rawType): string
    {
        $normalized = Str::of($rawType)->lower()->trim()->replace('-', '_')->value();

        return match ($normalized) {
            'richtext', 'rich_editor', 'tiptap' => 'rich_text',
            'features_grid', 'feature_grid' => 'features',
            'testimonial' => 'testimonials',
            default => $normalized,
        };
    }

    private function normalizeRichContentValue(mixed $value): string
    {
        $decoded = $this->decodeValue($value);

        if (is_string($decoded)) {
            return $this->sanitizeHtml($decoded);
        }

        if (! is_array($decoded)) {
            return '';
        }

        return $this->sanitizeHtml($this->renderRichDocumentToHtml($decoded));
    }

    /**
     * @param  array<string,mixed>|array<int,mixed>  $document
     */
    private function renderRichDocumentToHtml(array $document): string
    {
        if (array_is_list($document)) {
            return $this->renderRichNodes($document);
        }

        if (isset($document['type'])) {
            return $this->renderRichNode($document);
        }

        if (is_array($document['content'] ?? null)) {
            return $this->renderRichNodes($document['content']);
        }

        return '';
    }

    /**
     * @param  array<int,mixed>  $nodes
     */
    private function renderRichNodes(array $nodes): string
    {
        return collect($nodes)
            ->map(function (mixed $node): string {
                if (! is_array($node)) {
                    return '';
                }

                return $this->renderRichNode($node);
            })
            ->implode('');
    }

    /**
     * @param  array<string,mixed>  $node
     */
    private function renderRichNode(array $node): string
    {
        $type = trim((string) ($node['type'] ?? ''));
        $children = is_array($node['content'] ?? null) ? $node['content'] : [];
        $childrenHtml = $this->renderRichNodes($children);

        if ($type === 'doc') {
            return $childrenHtml;
        }

        if ($type === 'text') {
            return $this->renderRichTextNode($node);
        }

        if ($type === 'horizontalRule') {
            return '<hr>';
        }

        if ($type === 'hardBreak') {
            return '<br>';
        }

        if ($type === 'image') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $src = $this->normalizeRichImageSource($attrs['src'] ?? null);

            if (! is_string($src) || $src === '') {
                return '';
            }

            $alt = $this->escapeHtml(trim((string) ($attrs['alt'] ?? '')));

            return '<img src="'.$this->escapeHtml($src).'" alt="'.$alt.'">';
        }

        if ($type === 'heading') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $level = max(1, min(6, (int) ($attrs['level'] ?? 2)));

            return $this->wrapTag('h'.$level, $childrenHtml);
        }

        if ($type === 'codeBlock') {
            $text = $this->escapeHtml($this->extractRichTextContent($children));

            return $this->wrapTag('pre', $this->wrapTag('code', $text));
        }

        return match ($type) {
            'paragraph' => $this->wrapTag('p', $childrenHtml),
            'bulletList' => $this->wrapTag('ul', $childrenHtml),
            'orderedList' => $this->wrapTag('ol', $childrenHtml),
            'listItem' => $this->wrapTag('li', $childrenHtml),
            'blockquote' => $this->wrapTag('blockquote', $childrenHtml),
            'table' => $this->wrapTag('table', $childrenHtml),
            'tableRow' => $this->wrapTag('tr', $childrenHtml),
            'tableCell' => $this->wrapTag('td', $childrenHtml),
            'tableHeader' => $this->wrapTag('th', $childrenHtml),
            default => $childrenHtml,
        };
    }

    /**
     * @param  array<string,mixed>  $node
     */
    private function renderRichTextNode(array $node): string
    {
        $rendered = $this->escapeHtml((string) ($node['text'] ?? ''));
        $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

        foreach ($marks as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $type = trim((string) ($mark['type'] ?? ''));
            $attrs = is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [];

            if ($type === 'bold') {
                $rendered = $this->wrapTag('strong', $rendered);

                continue;
            }

            if ($type === 'italic') {
                $rendered = $this->wrapTag('em', $rendered);

                continue;
            }

            if ($type === 'underline') {
                $rendered = $this->wrapTag('u', $rendered);

                continue;
            }

            if ($type === 'strike') {
                $rendered = $this->wrapTag('s', $rendered);

                continue;
            }

            if ($type === 'code') {
                $rendered = $this->wrapTag('code', $rendered);

                continue;
            }

            if ($type === 'link') {
                $href = $this->sanitizeLinkHref($attrs['href'] ?? null);

                if (! is_string($href) || $href === '') {
                    continue;
                }

                $target = trim((string) ($attrs['target'] ?? ''));
                $targetAttr = $target !== ''
                    ? ' target="'.$this->escapeHtml($target).'"'
                    : '';

                $rendered = '<a href="'.$this->escapeHtml($href).'"'.$targetAttr.' rel="noopener noreferrer">'.$rendered.'</a>';
            }
        }

        return $rendered;
    }

    /**
     * @param  array<int,mixed>  $nodes
     */
    private function extractRichTextContent(array $nodes): string
    {
        return collect($nodes)
            ->map(function (mixed $node): string {
                if (! is_array($node)) {
                    return '';
                }

                if (($node['type'] ?? '') === 'text') {
                    return (string) ($node['text'] ?? '');
                }

                $children = is_array($node['content'] ?? null) ? $node['content'] : [];

                return $this->extractRichTextContent($children);
            })
            ->implode('');
    }

    private function sanitizeLinkHref(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $href = trim($value);

        if ($href === '' || str_starts_with(Str::lower($href), 'javascript:')) {
            return null;
        }

        return $href;
    }

    private function normalizeRichImageSource(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $src = trim($value);

        if ($src === '' || str_starts_with(Str::lower($src), 'javascript:')) {
            return null;
        }

        if (
            str_starts_with($src, 'http://')
            || str_starts_with($src, 'https://')
            || str_starts_with($src, '/')
            || str_starts_with($src, 'data:image/')
        ) {
            return $src;
        }

        return $this->normalizeImageUrl($src);
    }

    private function resolvePublicMediaUrl(string $rawUrl): ?string
    {
        try {
            return PublicMediaUrl::resolve($rawUrl);
        } catch (\Throwable) {
            $normalizedPath = ltrim(trim($rawUrl), '/');

            if ($normalizedPath === '') {
                return null;
            }

            if (str_starts_with($normalizedPath, 'public/storage/')) {
                $normalizedPath = substr($normalizedPath, strlen('public/storage/'));
            } elseif (str_starts_with($normalizedPath, 'storage/')) {
                $normalizedPath = substr($normalizedPath, strlen('storage/'));
            } elseif (str_starts_with($normalizedPath, 'public/')) {
                $normalizedPath = substr($normalizedPath, strlen('public/'));
            }

            return $normalizedPath !== ''
                ? '/media/public/'.$normalizedPath
                : null;
        }
    }

    private function wrapTag(string $tag, string $content): string
    {
        return '<'.$tag.'>'.$content.'</'.$tag.'>';
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
