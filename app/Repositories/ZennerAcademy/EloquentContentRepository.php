<?php

namespace App\Repositories\ZennerAcademy;

use App\Models\Content;
use App\Repositories\ZennerAcademy\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentContentRepository implements ContentRepositoryInterface
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string, mixed> $filters */
    public function paginateByCategory(int $categoryId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->where('category_id', $categoryId)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): Content
    {
        return Content::query()
            ->with(['category.parent', 'creator:id,name,email'])
            ->findOrFail($id);
    }

    public function findBySlug(string $slug): Content
    {
        return Content::query()
            ->with(['category.parent', 'creator:id,name,email'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): Content
    {
        return Content::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Content $content, array $data): Content
    {
        $content->update($data);

        return $content->fresh(['category.parent', 'creator:id,name,email']);
    }

    public function delete(Content $content): bool
    {
        return (bool) $content->delete();
    }

    /** @param array<string, mixed> $filters */
    private function buildFilteredQuery(array $filters): Builder
    {
        $query = Content::query()
            ->with(['category:id,parent_id,name,slug', 'creator:id,name'])
            ->orderByDesc('created_at');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }
}
