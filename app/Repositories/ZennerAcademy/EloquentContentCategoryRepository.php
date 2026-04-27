<?php

namespace App\Repositories\ZennerAcademy;

use App\Models\ContentCategory;
use App\Repositories\ZennerAcademy\Contracts\ContentCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentContentCategoryRepository implements ContentCategoryRepositoryInterface
{
    /** @return Collection<int, ContentCategory> */
    public function allWithHierarchy(): Collection
    {
        return ContentCategory::query()
            ->with(['children.children', 'children' => function ($query): void {
                $query->withCount('contents');
            }])
            ->withCount('contents')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, ContentCategory> */
    public function parentCategories(): Collection
    {
        return ContentCategory::query()
            ->with(['children' => function ($query): void {
                $query->withCount('contents')->orderBy('name');
            }])
            ->withCount('contents')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, ContentCategory> */
    public function childrenOf(int $parentId): Collection
    {
        return ContentCategory::query()
            ->with(['children' => function ($query): void {
                $query->withCount('contents')->orderBy('name');
            }])
            ->withCount('contents')
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ContentCategory
    {
        return ContentCategory::query()
            ->with([
                'parent',
                'children' => function ($query): void {
                    $query->withCount('contents')
                        ->with([
                            'contents' => function ($query): void {
                                $query->select('id', 'category_id', 'title', 'slug');
                            },
                        ])
                        ->orderBy('name');
                },
                'contents' => function ($query): void {
                    $query->select('id', 'category_id', 'title', 'slug');
                },
            ])
            ->withCount('contents')
            ->findOrFail($id);
    }

    public function findBySlug(string $slug): ContentCategory
    {
        return ContentCategory::query()
            ->with([
                'parent',
                'children' => function ($query): void {
                    $query->withCount('contents')->orderBy('name');
                },
                'contents' => function ($query): void {
                    $query->select('id', 'category_id', 'title', 'slug');
                },
            ])
            ->withCount('contents')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): ContentCategory
    {
        return ContentCategory::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ContentCategory $category, array $data): ContentCategory
    {
        $category->update($data);

        return $category->fresh(['parent', 'children']);
    }

    public function delete(ContentCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
