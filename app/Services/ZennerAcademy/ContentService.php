<?php

namespace App\Services\ZennerAcademy;

use App\Models\Content;
use App\Repositories\ZennerAcademy\Contracts\ContentCategoryRepositoryInterface;
use App\Repositories\ZennerAcademy\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ContentService
{
    public function __construct(
        protected ContentRepositoryInterface $contentRepository,
        protected ContentCategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Daftar konten terpaginasi dengan filter opsional.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getContentList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contentRepository
            ->paginate($filters, $perPage)
            ->through(fn (Content $content): array => $this->formatContent($content));
    }

    /**
     * Daftar konten berdasarkan slug kategori.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getContentByCategory(string $categorySlug, array $filters, int $perPage = 15): array
    {
        $category = $this->categoryRepository->findBySlug($categorySlug);

        $contents = $this->contentRepository
            ->paginateByCategory($category->id, $filters, $perPage)
            ->through(fn (Content $content): array => $this->formatContent($content));

        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
            ],
            'contents' => $contents,
        ];
    }

    /** @return array<string, mixed> */
    public function getContentDetail(int $id): array
    {
        return $this->formatContent($this->contentRepository->findById($id));
    }

    /** @return array<string, mixed> */
    public function getContentDetailBySlug(string $slug): array
    {
        return $this->formatContent($this->contentRepository->findBySlug($slug));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function storeContent(array $data): array
    {
        $data['slug'] = $this->resolveSlug($data);

        $content = $this->contentRepository->store($data);

        return $this->formatContent($this->contentRepository->findById($content->id));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateContent(int $id, array $data): array
    {
        $content = $this->contentRepository->findById($id);

        if (isset($data['title']) && ! isset($data['slug'])) {
            $data['slug'] = $this->resolveSlug($data, $content);
        }

        $updated = $this->contentRepository->update($content, $data);

        return $this->formatContent($this->contentRepository->findById($updated->id));
    }

    public function deleteContent(int $id): void
    {
        $content = $this->contentRepository->findById($id);
        $this->contentRepository->delete($content);
    }

    /** @return array<string, mixed> */
    private function formatContent(Content $content): array
    {
        $data = [
            'id' => $content->id,
            'title' => $content->title,
            'slug' => $content->slug,
            'content' => $content->content,
            'file' => $content->file,
            'vlink' => $content->vlink,
            'status' => $content->status,
            'created_by' => $content->created_by,
            'created_at' => $content->created_at?->toIso8601String(),
            'updated_at' => $content->updated_at?->toIso8601String(),
        ];

        if ($content->relationLoaded('category') && $content->category) {
            $category = $content->category;
            $data['category'] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
                'parent' => $category->relationLoaded('parent') && $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
            ];
        }

        if ($content->relationLoaded('creator') && $content->creator) {
            $data['creator'] = [
                'id' => $content->creator->id,
                'name' => $content->creator->name,
            ];
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function resolveSlug(array $data, ?Content $existing = null): string
    {
        if (isset($data['slug']) && trim((string) $data['slug']) !== '') {
            return Str::slug((string) $data['slug']);
        }

        $base = Str::slug((string) ($data['title'] ?? ''));

        if ($existing !== null && $existing->slug === $base) {
            return $base;
        }

        $slug = $base;
        $counter = 1;

        while (Content::query()->where('slug', $slug)->when($existing, fn ($q) => $q->whereKeyNot($existing->id))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
