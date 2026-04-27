<?php

namespace App\Services\Pages;

use App\Models\Page;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use App\Support\Pages\PageBlockParser;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(
        protected PageRepositoryInterface $pageRepository,
        protected PageBlockParser $pageBlockParser,
    ) {}

    /**
     * @return array{
     *   seo:array<string,mixed>,
     *   page:array<string,mixed>
     * }
     */
    public function getShowPageData(string $slug): array
    {
        $page = $this->pageRepository->findPublishedBySlug($slug);
        $formattedPage = $this->formatPage($page);

        $seoTitle = $formattedPage['seo_title'] !== ''
            ? $formattedPage['seo_title']
            : $formattedPage['title'];

        $seoDescription = $formattedPage['seo_description'] !== ''
            ? $formattedPage['seo_description']
            : $formattedPage['excerpt'];

        return [
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => route('pages.show', ['slug' => $formattedPage['slug']]),
                'robots' => 'index,follow',
                'image' => $formattedPage['cover_image'],
                'structured_data' => $this->buildStructuredData($formattedPage, $seoTitle, $seoDescription),
            ],
            'page' => $formattedPage,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function formatPage(Page $page): array
    {
        $blocks = $this->pageBlockParser->normalizeBlocks($page->blocks);
        $contentHtml = is_string($page->content) ? $this->pageBlockParser->sanitizeHtml($page->content) : '';
        $excerpt = $this->pageBlockParser->extractSummary($blocks, $contentHtml);
        $coverImage = $this->pageBlockParser->extractFirstImage($blocks);

        return [
            'id' => $page->id,
            'title' => (string) $page->title,
            'slug' => (string) $page->slug,
            'template' => is_string($page->template) ? $page->template : 'default',
            'seo_title' => trim((string) ($page->seo_title ?? '')),
            'seo_description' => trim((string) ($page->seo_description ?? '')),
            'excerpt' => $excerpt,
            'cover_image' => $coverImage,
            'content_html' => $contentHtml,
            'blocks' => $blocks,
            'published_label' => $page->updated_at?->translatedFormat('d M Y H:i'),
            'updated_at' => $page->updated_at?->toIso8601String(),
            'url' => route('pages.show', ['slug' => $page->slug]),
        ];
    }

    /**
     * @param array<string,mixed> $page
     * @return array<int,array<string,mixed>>
     */
    private function buildStructuredData(array $page, string $seoTitle, string $seoDescription): array
    {
        $webPageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $seoTitle,
            'description' => $seoDescription,
            'url' => $page['url'] ?? request()->fullUrl(),
            'inLanguage' => 'id-ID',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
            'dateModified' => $page['updated_at'] ?? now()->toIso8601String(),
        ];

        if (is_string($page['cover_image'] ?? null) && $page['cover_image'] !== '') {
            $webPageSchema['image'] = [$page['cover_image']];
        }

        return [
            $webPageSchema,
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Halaman',
                        'item' => route('pages.show', ['slug' => $page['slug'] ?? Str::slug($seoTitle)]),
                    ],
                ],
            ],
        ];
    }
}
