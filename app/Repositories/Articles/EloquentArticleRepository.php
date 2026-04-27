<?php

namespace App\Repositories\Articles;

use App\Models\Article;
use App\Repositories\Articles\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentArticleRepository implements ArticleRepositoryInterface
{
    public function paginatePublished(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = $this->basePublishedQuery();

        $search = trim((string) ($filters['search'] ?? ''));
        $tag = trim((string) ($filters['tag'] ?? ''));
        $sort = (string) ($filters['sort'] ?? 'newest');

        if ($search !== '') {
            $driver = DB::connection()->getDriverName();
            $shouldUseFullText = in_array($driver, ['mysql', 'mariadb'], true) && $this->hasArticlesFullTextIndex();

            $query->where(function (Builder $searchQuery) use ($search, $shouldUseFullText): void {
                if ($shouldUseFullText) {
                    $searchQuery
                        ->whereFullText(['title', 'seo_title'], $search)
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('seo_title', 'like', "%{$search}%")
                        ->orWhere('seo_description', 'like', "%{$search}%");

                    return;
                }

                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('seo_title', 'like', "%{$search}%")
                    ->orWhere('seo_description', 'like', "%{$search}%");
            });
        }

        if ($tag !== '') {
            $query->whereHas('contents', function (Builder $contentQuery) use ($tag): void {
                $contentQuery->whereJsonContains('tags', $tag);
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('published_at'),
            'az' => $query->orderBy('title'),
            'za' => $query->orderByDesc('title'),
            default => $query->orderByDesc('published_at'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function findPublishedBySlug(string $slug): Article
    {
        return $this->basePublishedQuery()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /** @return Collection<int, string> */
    public function getAvailableTags(): Collection
    {
        return $this->basePublishedQuery()
            ->get()
            ->flatMap(function (Article $article): Collection {
                $tags = $article->content?->tags;

                return collect(is_array($tags) ? $tags : []);
            })
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => trim($tag))
            ->unique()
            ->sort()
            ->values();
    }

    /** @param array<int, string> $tags */
    public function getRelatedByTags(Article $article, array $tags, int $limit = 3): Collection
    {
        $query = $this->basePublishedQuery()
            ->whereKeyNot($article->id);

        $normalizedTags = collect($tags)
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => trim($tag))
            ->values();

        if ($normalizedTags->isNotEmpty()) {
            $query->whereHas('contents', function (Builder $contentQuery) use ($normalizedTags): void {
                $contentQuery->where(function (Builder $tagsQuery) use ($normalizedTags): void {
                    $normalizedTags->each(function (string $tag) use ($tagsQuery): void {
                        $tagsQuery->orWhereJsonContains('tags', $tag);
                    });
                });
            });
        }

        return $query
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function countPublished(): int
    {
        return $this->basePublishedQuery()->count();
    }

    private function basePublishedQuery(): Builder
    {
        return Article::query()
            ->with([
                'content:id,article_id,content,tags,created_at,updated_at',
                'contents:id,article_id,content,tags,created_at,updated_at',
            ])
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    private function hasArticlesFullTextIndex(): bool
    {
        static $hasFullTextIndex = null;

        if ($hasFullTextIndex !== null) {
            return $hasFullTextIndex;
        }

        $databaseName = DB::connection()->getDatabaseName();

        if (! is_string($databaseName) || $databaseName === '') {
            $hasFullTextIndex = false;

            return $hasFullTextIndex;
        }

        $indexes = DB::table('information_schema.statistics')
            ->select('index_name', 'column_name')
            ->where('table_schema', $databaseName)
            ->where('table_name', 'articles')
            ->where('index_type', 'FULLTEXT')
            ->get()
            ->groupBy('index_name');

        $hasFullTextIndex = $indexes->contains(function (Collection $columns): bool {
            $columnNames = $columns
                ->pluck('column_name')
                ->filter(fn (mixed $column): bool => is_string($column))
                ->map(fn (string $column): string => strtolower($column))
                ->values()
                ->all();

            return in_array('title', $columnNames, true) && in_array('seo_title', $columnNames, true);
        });

        return $hasFullTextIndex;
    }
}
