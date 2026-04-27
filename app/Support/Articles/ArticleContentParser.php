<?php

namespace App\Support\Articles;

use App\Support\Media\PublicMediaUrl;

class ArticleContentParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizeBlocks(mixed $rawContent): array
    {
        $decoded = $this->decodeContent($rawContent);

        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['blocks']) && is_array($decoded['blocks'])) {
            $decoded = $decoded['blocks'];
        }

        if (! array_is_list($decoded)) {
            if (array_key_exists('type', $decoded)) {
                $decoded = [$decoded];
            } else {
                $decoded = array_values($decoded);
            }
        }

        return collect($decoded)
            ->map(fn (mixed $block): ?array => $this->normalizeBlock($block))
            ->filter(fn (?array $block): bool => $block !== null)
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function normalizeTags(mixed $rawTags): array
    {
        if (is_string($rawTags)) {
            $decoded = json_decode($rawTags, true);
        } elseif (is_array($rawTags)) {
            $decoded = $rawTags;
        } else {
            $decoded = [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => trim($tag))
            ->unique()
            ->values()
            ->all();
    }

    public function extractPlainTextFromBlocks(array $blocks): string
    {
        $text = collect($blocks)
            ->map(function (array $block): string {
                $type = (string) ($block['type'] ?? '');

                return match ($type) {
                    'heading' => (string) ($block['text'] ?? ''),
                    'rich_text' => strip_tags((string) ($block['html'] ?? '')),
                    'list' => collect((array) ($block['items'] ?? []))
                        ->map(fn (mixed $item): string => is_string($item) ? strip_tags($item) : '')
                        ->implode(' '),
                    'quote' => (string) ($block['quote'] ?? ''),
                    default => '',
                };
            })
            ->implode(' ');

        return trim((string) preg_replace('/\s+/', ' ', $text));
    }

    public function extractFirstImageFromBlocks(array $blocks): ?string
    {
        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? null) !== 'image') {
                continue;
            }

            $url = $block['url'] ?? null;
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return null;
    }

    public function estimateReadTimeMinutes(string $plainText): int
    {
        $words = str_word_count($plainText);

        return max(1, (int) ceil($words / 200));
    }

    /** @return array<string, mixed>|array<int, mixed>|null */
    private function decodeContent(mixed $rawContent): ?array
    {
        if (is_string($rawContent)) {
            $decoded = json_decode($rawContent, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return [
                [
                    'type' => 'rich_text',
                    'content' => ['text' => $rawContent],
                ],
            ];
        }

        if (is_array($rawContent)) {
            return $rawContent;
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    private function normalizeBlock(mixed $rawBlock): ?array
    {
        if (! is_array($rawBlock)) {
            return null;
        }

        $blockType = (string) ($rawBlock['type'] ?? 'unknown');
        $data = $this->extractBlockData($rawBlock);

        return match ($blockType) {
            'heading' => $this->normalizeHeadingBlock($data),
            'rich_text', 'paragraph' => $this->normalizeRichTextBlock($data),
            'image' => $this->normalizeImageBlock($data),
            'list' => $this->normalizeListBlock($data),
            'quote' => $this->normalizeQuoteBlock($data),
            'divider' => ['type' => 'divider'],
            default => [
                'type' => 'unknown',
                'block_type' => $blockType,
                'data' => $data,
            ],
        };
    }

    /** @param array<string, mixed> $rawBlock
     * @return array<string, mixed>
     */
    private function extractBlockData(array $rawBlock): array
    {
        if (isset($rawBlock['data']) && is_array($rawBlock['data'])) {
            return $rawBlock['data'];
        }

        if (isset($rawBlock['content'])) {
            if (is_array($rawBlock['content'])) {
                return $rawBlock['content'];
            }

            if (is_string($rawBlock['content'])) {
                return ['text' => $rawBlock['content']];
            }
        }

        return [];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeHeadingBlock(array $data): array
    {
        $rawLevel = (int) ($data['level'] ?? 2);
        $level = max(1, min(6, $rawLevel));

        $text = trim(strip_tags((string) ($data['text'] ?? $data['content'] ?? '')));

        return [
            'type' => 'heading',
            'level' => $level,
            'text' => $text,
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeRichTextBlock(array $data): array
    {
        $html = $this->normalizeRichTextValue($data['text'] ?? $data['content'] ?? '');

        return [
            'type' => 'rich_text',
            'html' => $this->sanitizeHtml($html),
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeImageBlock(array $data): array
    {
        $rawUrl = (string) ($data['url'] ?? '');

        return [
            'type' => 'image',
            'url' => $this->normalizeImageUrl($rawUrl),
            'alt' => trim((string) ($data['alt'] ?? '')),
            'caption' => trim(strip_tags((string) ($data['caption'] ?? ''))),
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeListBlock(array $data): array
    {
        $items = collect((array) ($data['items'] ?? []))
            ->map(fn (mixed $item): string => is_string($item) ? $this->sanitizeHtml($item) : '')
            ->filter(fn (string $item): bool => trim(strip_tags($item)) !== '')
            ->values()
            ->all();

        return [
            'type' => 'list',
            'ordered' => (bool) ($data['ordered'] ?? false),
            'items' => $items,
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeQuoteBlock(array $data): array
    {
        return [
            'type' => 'quote',
            'quote' => trim(strip_tags((string) ($data['quote'] ?? ''))),
            'cite' => trim(strip_tags((string) ($data['cite'] ?? ''))),
        ];
    }

    private function normalizeImageUrl(string $rawUrl): ?string
    {
        if ($rawUrl === '') {
            return null;
        }

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

        return $this->resolvePublicMediaUrl($rawUrl);
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

    private function sanitizeHtml(string $html): string
    {
        $cleaned = preg_replace('/<script\\b[^>]*>(.*?)<\\/script>/is', '', $html);

        return is_string($cleaned) ? trim($cleaned) : trim($html);
    }

    private function normalizeRichTextValue(mixed $value): string
    {
        if (is_array($value)) {
            return $this->renderTipTapDocumentToHtml($value);
        }

        if (! is_string($value)) {
            return '';
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $value;
        }

        return $this->renderTipTapDocumentToHtml($decoded);
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $document
     */
    private function renderTipTapDocumentToHtml(array $document): string
    {
        $normalizedDocument = $this->normalizeTipTapDocument($document);

        return $this->renderRichDocumentToHtml($normalizedDocument);
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $document
     * @return array<string, mixed>
     */
    private function normalizeTipTapDocument(array $document): array
    {
        if (array_is_list($document)) {
            return [
                'type' => 'doc',
                'content' => $this->normalizeTipTapNodeList($document),
            ];
        }

        if (($document['type'] ?? null) === 'doc') {
            return [
                'type' => 'doc',
                'content' => is_array($document['content'] ?? null)
                    ? $this->normalizeTipTapNodeList($document['content'])
                    : [],
            ];
        }

        if (is_string($document['type'] ?? null)) {
            $normalizedNode = $this->normalizeTipTapNode($document);

            return [
                'type' => 'doc',
                'content' => $normalizedNode ? [$normalizedNode] : [],
            ];
        }

        if (is_array($document['content'] ?? null)) {
            return [
                'type' => 'doc',
                'content' => $this->normalizeTipTapNodeList($document['content']),
            ];
        }

        return [
            'type' => 'doc',
            'content' => [],
        ];
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTipTapNodeList(array $nodes): array
    {
        $normalizedNodes = [];

        foreach ($nodes as $node) {
            $normalizedNode = $this->normalizeTipTapNode($node);

            if ($normalizedNode !== null) {
                $normalizedNodes[] = $normalizedNode;
            }
        }

        return $normalizedNodes;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeTipTapNode(mixed $node): ?array
    {
        if (! is_array($node)) {
            return null;
        }

        $type = trim((string) ($node['type'] ?? ''));

        if ($type === '') {
            return null;
        }

        if (isset($node['content'])) {
            if (is_array($node['content'])) {
                $node['content'] = $this->normalizeTipTapNodeList($node['content']);
            } else {
                unset($node['content']);
            }
        }

        if ($type === 'heading') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $level = (int) ($attrs['level'] ?? 2);
            $attrs['level'] = max(1, min(6, $level));
            $node['attrs'] = $attrs;
        }

        $node['type'] = $type;

        return $node;
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $document
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
     * @param  array<int, mixed>  $nodes
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
     * @param  array<string, mixed>  $node
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
     * @param  array<string, mixed>  $node
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
     * @param  array<int, mixed>  $nodes
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

        if ($href === '' || str_starts_with(strtolower($href), 'javascript:')) {
            return null;
        }

        return $href;
    }

    private function normalizeRichImageSource(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $source = trim($value);

        if ($source === '' || str_starts_with(strtolower($source), 'javascript:')) {
            return null;
        }

        if (
            str_starts_with($source, 'http://')
            || str_starts_with($source, 'https://')
            || str_starts_with($source, '/')
            || str_starts_with($source, 'data:image/')
        ) {
            return $source;
        }

        return $this->normalizeImageUrl($source);
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
