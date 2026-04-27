<?php

namespace App\Services\ZennerAcademy;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Repositories\ZennerAcademy\Contracts\ContentCategoryRepositoryInterface;
use Illuminate\Support\Str;

class ContentCategoryService
{
    public function __construct(
        protected ContentCategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Pohon kategori lengkap dari root hingga sub-kategori.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCategoryTree(): array
    {
        return $this->categoryRepository
            ->allWithHierarchy()
            ->map(fn (ContentCategory $category): array => $this->formatCategoryWithChildren($category))
            ->values()
            ->all();
    }

    /**
     * Daftar kategori root (parent_id null) beserta sub-kategori langsung.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getParentCategories(): array
    {
        return $this->categoryRepository
            ->parentCategories()
            ->map(fn (ContentCategory $category): array => $this->formatCategoryWithChildren($category))
            ->values()
            ->all();
    }

    /**
     * Sub-kategori dari parent tertentu beserta info parent-nya.
     *
     * @return array{parent: array<string, mixed>, children: array<int, array<string, mixed>>}
     */
    public function getSubCategoriesByParent(int $parentId): array
    {
        $parent = $this->categoryRepository->findById($parentId);

        $children = $this->categoryRepository
            ->childrenOf($parentId)
            ->map(fn (ContentCategory $category): array => $this->formatCategoryWithChildren($category))
            ->values()
            ->all();

        return [
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
                'parent_id' => $parent->parent_id,
                'contents_count' => $parent->contents_count ?? 0,
            ],
            'children' => $children,
        ];
    }

    /** @return array<string, mixed> */
    public function getCategoryDetail(int $id): array
    {
        $category = $this->categoryRepository->findById($id);

        return $this->formatCategoryWithChildren($category);
    }

    /** @return array<string, mixed> */
    public function getCategoryDetailBySlug(string $slug): array
    {
        $category = $this->categoryRepository->findBySlug($slug);

        return $this->formatCategoryWithChildren($category);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function storeCategory(array $data): array
    {
        $data['slug'] = $this->resolveSlug($data);

        $category = $this->categoryRepository->store($data);

        return $this->formatCategoryWithChildren(
            $this->categoryRepository->findById($category->id)
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateCategory(int $id, array $data): array
    {
        $category = $this->categoryRepository->findById($id);

        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = $this->resolveSlug($data, $category);
        }

        $updated = $this->categoryRepository->update($category, $data);

        return $this->formatCategoryWithChildren(
            $this->categoryRepository->findById($updated->id)
        );
    }

    public function deleteCategory(int $id): void
    {
        $category = $this->categoryRepository->findById($id);
        $this->categoryRepository->delete($category);
    }

    /** @return array<string, mixed> */
    private function formatCategoryWithChildren(ContentCategory $category): array
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->parent_id,
            'contents_count' => $category->contents_count ?? 0,
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];

        if ($category->relationLoaded('contents')) {
            $data['contents'] = $category->contents
                ->map(fn (Content $content): array => [
                    'id' => $content->id,
                    'title' => $content->title,
                    'slug' => $content->slug,
                ])
                ->values()
                ->all();
        }

        if ($category->relationLoaded('parent') && $category->parent) {
            $data['parent'] = [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ];
        }

        if ($category->relationLoaded('children')) {
            $data['children'] = $category->children
                ->map(fn (ContentCategory $child): array => $this->formatCategoryWithChildren($child))
                ->values()
                ->all();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function resolveSlug(array $data, ?ContentCategory $existing = null): string
    {
        if (isset($data['slug']) && trim((string) $data['slug']) !== '') {
            return Str::slug((string) $data['slug']);
        }

        $base = Str::slug((string) ($data['name'] ?? ''));

        if ($existing !== null && $existing->slug === $base) {
            return $base;
        }

        $slug = $base;
        $counter = 1;

        while (ContentCategory::query()->where('slug', $slug)->when($existing, fn ($q) => $q->whereKeyNot($existing->id))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
