<?php

namespace App\Repositories\Articles\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ArticleRepositoryInterface
{
    public function paginatePublished(array $filters = [], int $perPage = 9): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): Article;

    /** @return Collection<int, string> */
    public function getAvailableTags(): Collection;

    /** @param array<int, string> $tags */
    public function getRelatedByTags(Article $article, array $tags, int $limit = 3): Collection;

    public function countPublished(): int;
}
