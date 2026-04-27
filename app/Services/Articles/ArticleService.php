<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Repositories\Articles\Contracts\ArticleRepositoryInterface;
use App\Support\Articles\ArticleContentParser;
use App\Support\Media\PublicMediaUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected ArticleContentParser $contentParser,
    ) {}

    /**
     * @param  array{search:string|null,tag:string|null,sort:'newest'|'oldest'|'az'|'za',page:int}  $filters
     * @return array<string, mixed>
     */
    public function getIndexPageData(array $filters): array
    {
        $normalizedFilters = [
            'search' => $filters['search'] ?? null,
            'tag' => $filters['tag'] ?? null,
            'sort' => $filters['sort'] ?? 'newest',
            'page' => max(1, (int) ($filters['page'] ?? 1)),
        ];

        $articles = $this->articleRepository
            ->paginatePublished($normalizedFilters, 9)
            ->through(fn (Article $article): array => $this->formatArticleCard($article));

        $availableTags = $this->articleRepository->getAvailableTags();
        $stats = [
            'total_articles' => $this->articleRepository->countPublished(),
            'result_count' => $articles->total(),
            'current_page_count' => count($articles->items()),
            'tag_count' => $availableTags->count(),
        ];

        $hasQueryFilters = $this->hasIndexQueryFilters($normalizedFilters);

        $canonicalUrl = route('articles.index');
        $seoTitle = $this->buildIndexTitle($normalizedFilters, $articles->total());
        $seoDescription = $this->buildIndexDescription($normalizedFilters, $articles->total());

        return [
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => $canonicalUrl,
                'robots' => $hasQueryFilters ? 'noindex,follow' : 'index,follow',
                'image' => $this->findFirstCardImage($articles),
                'structured_data' => $this->buildIndexStructuredData($articles, $seoTitle, $seoDescription),
            ],
            'articles' => $articles,
            'filters' => [
                'search' => $normalizedFilters['search'],
                'tag' => $normalizedFilters['tag'],
                'sort' => $normalizedFilters['sort'],
                'page' => $normalizedFilters['page'],
            ],
            'availableTags' => $availableTags->values()->all(),
            'stats' => $stats,
        ];
    }

    /** @return array<string, mixed> */
    public function getShowPageData(string $slug): array
    {
        $article = $this->articleRepository->findPublishedBySlug($slug);
        $formattedArticle = $this->formatArticleDetail($article);

        $relatedArticles = $this->articleRepository
            ->getRelatedByTags($article, $formattedArticle['tags'], 3)
            ->map(fn (Article $relatedArticle): array => $this->formatArticleCard($relatedArticle))
            ->values()
            ->all();

        $seoTitle = (string) ($formattedArticle['seo_title'] ?: $formattedArticle['title']);
        $seoDescription = (string) ($formattedArticle['seo_description'] ?: $formattedArticle['excerpt']);

        return [
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => route('articles.show', ['slug' => $formattedArticle['slug']]),
                'robots' => 'index,follow',
                'image' => $formattedArticle['banner_image'] ?: $formattedArticle['cover_image'],
                'structured_data' => $this->buildShowStructuredData($formattedArticle, $seoTitle, $seoDescription),
            ],
            'article' => $formattedArticle,
            'relatedArticles' => $relatedArticles,
        ];
    }

    /** @return array<string, mixed> */
    private function formatArticleCard(Article $article): array
    {
        $blocks = $this->contentParser->normalizeBlocks($this->resolveArticleContentPayload($article));
        $plainText = $this->contentParser->extractPlainTextFromBlocks($blocks);
        $tags = $this->resolveArticleTags($article);
        $coverImage = $this->contentParser->extractFirstImageFromBlocks($blocks);
        $bannerImage = $this->resolveBannerImage($article->image_banner);

        $excerpt = trim((string) ($article->seo_description ?? ''));
        if ($excerpt === '') {
            $excerpt = Str::limit($plainText, 170);
        }

        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'seo_title' => $article->seo_title,
            'seo_description' => $article->seo_description,
            'excerpt' => $excerpt,
            'banner_image' => $bannerImage,
            'cover_image' => $coverImage,
            'published_at' => $article->published_at?->toIso8601String(),
            'published_label' => $article->published_at?->translatedFormat('d M Y'),
            'read_time_minutes' => $this->contentParser->estimateReadTimeMinutes($plainText),
            'tags' => $tags,
            'url' => route('articles.show', ['slug' => $article->slug]),
        ];
    }

    /** @return array<string, mixed> */
    private function formatArticleDetail(Article $article): array
    {
        $blocks = $this->contentParser->normalizeBlocks($this->resolveArticleContentPayload($article));
        $plainText = $this->contentParser->extractPlainTextFromBlocks($blocks);
        $coverImage = $this->contentParser->extractFirstImageFromBlocks($blocks);
        $bannerImage = $this->resolveBannerImage($article->image_banner);
        $tags = $this->resolveArticleTags($article);

        $excerpt = trim((string) ($article->seo_description ?? ''));
        if ($excerpt === '') {
            $excerpt = Str::limit($plainText, 220);
        }

        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'seo_title' => $article->seo_title,
            'seo_description' => $article->seo_description,
            'excerpt' => $excerpt,
            'banner_image' => $bannerImage,
            'cover_image' => $coverImage,
            'tags' => $tags,
            'blocks' => $blocks,
            'read_time_minutes' => $this->contentParser->estimateReadTimeMinutes($plainText),
            'published_at' => $article->published_at?->toIso8601String(),
            'published_label' => $article->published_at?->translatedFormat('d M Y'),
            'updated_at' => $article->updated_at?->toIso8601String(),
            'updated_label' => $article->updated_at?->translatedFormat('d M Y H:i'),
            'url' => route('articles.show', ['slug' => $article->slug]),
        ];
    }

    private function resolveArticleContentPayload(Article $article): mixed
    {
        if ($article->relationLoaded('content') && $article->content) {
            return $article->content->content;
        }

        $content = $article->relationLoaded('contents')
            ? $article->contents->first()
            : $article->contents()->first();

        return $content?->content;
    }

    /** @return array<int, string> */
    private function resolveArticleTags(Article $article): array
    {
        if ($article->relationLoaded('content') && $article->content) {
            return $this->contentParser->normalizeTags($article->content->tags);
        }

        $content = $article->relationLoaded('contents')
            ? $article->contents->first()
            : $article->contents()->first();

        return $this->contentParser->normalizeTags($content?->tags);
    }

    /** @param array{search:string|null,tag:string|null,sort:'newest'|'oldest'|'az'|'za',page:int} $filters */
    private function hasIndexQueryFilters(array $filters): bool
    {
        return ($filters['search'] ?? null) !== null
            || ($filters['tag'] ?? null) !== null
            || (($filters['sort'] ?? 'newest') !== 'newest')
            || (($filters['page'] ?? 1) > 1);
    }

    /** @param array{search:string|null,tag:string|null,sort:'newest'|'oldest'|'az'|'za',page:int} $filters */
    private function buildIndexTitle(array $filters, int $total): string
    {
        if (($filters['search'] ?? null) !== null) {
            return sprintf('Hasil Pencarian Artikel "%s" (%d)', $filters['search'], $total);
        }

        if (($filters['tag'] ?? null) !== null) {
            return sprintf('Artikel dengan Tag "%s" (%d)', $filters['tag'], $total);
        }

        return 'Artikel & Insight Terbaru';
    }

    /** @param array{search:string|null,tag:string|null,sort:'newest'|'oldest'|'az'|'za',page:int} $filters */
    private function buildIndexDescription(array $filters, int $total): string
    {
        if (($filters['search'] ?? null) !== null) {
            return sprintf('Menampilkan %d artikel untuk pencarian "%s".', $total, $filters['search']);
        }

        if (($filters['tag'] ?? null) !== null) {
            return sprintf('Menampilkan %d artikel dengan tag "%s".', $total, $filters['tag']);
        }

        return 'Temukan artikel terbaru, insight bisnis, dan edukasi produk pilihan dari tim kami.';
    }

    private function findFirstCardImage(LengthAwarePaginator $articles): ?string
    {
        foreach ($articles->items() as $article) {
            if (! is_array($article)) {
                continue;
            }

            $image = $article['banner_image'] ?? $article['cover_image'] ?? null;

            if (is_string($image) && $image !== '') {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildIndexStructuredData(
        LengthAwarePaginator $articles,
        string $seoTitle,
        string $seoDescription,
    ): array {
        $itemListElement = collect($articles->items())
            ->values()
            ->map(function (mixed $article, int $index) use ($articles): array {
                if (! is_array($article)) {
                    return [];
                }

                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1 + (($articles->currentPage() - 1) * $articles->perPage()),
                    'url' => $article['url'] ?? null,
                    'name' => $article['title'] ?? null,
                ];
            })
            ->filter(fn (array $item): bool => ! empty($item['url']) && ! empty($item['name']))
            ->values()
            ->all();

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $seoTitle,
                'description' => $seoDescription,
                'url' => request()->fullUrl(),
                'inLanguage' => 'id-ID',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => config('app.name'),
                    'url' => url('/'),
                ],
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'numberOfItems' => count($itemListElement),
                    'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
                    'itemListElement' => $itemListElement,
                ],
            ],
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
                        'name' => 'Artikel',
                        'item' => route('articles.index'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $article
     * @return array<int, array<string, mixed>>
     */
    private function buildShowStructuredData(array $article, string $seoTitle, string $seoDescription): array
    {
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $seoTitle,
            'description' => $seoDescription,
            'datePublished' => $article['published_at'] ?? null,
            'dateModified' => $article['updated_at'] ?? null,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $article['url'] ?? request()->fullUrl(),
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
            'inLanguage' => 'id-ID',
        ];

        $primaryImage = $article['banner_image'] ?? $article['cover_image'] ?? null;

        if (is_string($primaryImage) && $primaryImage !== '') {
            $articleSchema['image'] = [$primaryImage];
        }

        return [
            $articleSchema,
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
                        'name' => 'Artikel',
                        'item' => route('articles.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $article['title'] ?? $seoTitle,
                        'item' => $article['url'] ?? request()->fullUrl(),
                    ],
                ],
            ],
        ];
    }

    private function resolveBannerImage(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        try {
            return PublicMediaUrl::resolve($path);
        } catch (\Throwable) {
            return $path;
        }
    }
}
