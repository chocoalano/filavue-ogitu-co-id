<?php

namespace App\Repositories\ZennerAcademy\Contracts;

use App\Models\ContentCategory;
use Illuminate\Support\Collection;

interface ContentCategoryRepositoryInterface
{
    /**
     * Semua kategori dengan relasi children dan jumlah konten.
     *
     * @return Collection<int, ContentCategory>
     */
    public function allWithHierarchy(): Collection;

    /**
     * Hanya kategori root (parent_id null) dengan relasi children.
     *
     * @return Collection<int, ContentCategory>
     */
    public function parentCategories(): Collection;

    /**
     * Sub-kategori langsung dari parent tertentu.
     *
     * @return Collection<int, ContentCategory>
     */
    public function childrenOf(int $parentId): Collection;

    public function findById(int $id): ContentCategory;

    public function findBySlug(string $slug): ContentCategory;

    /** @param array<string, mixed> $data */
    public function store(array $data): ContentCategory;

    /** @param array<string, mixed> $data */
    public function update(ContentCategory $category, array $data): ContentCategory;

    public function delete(ContentCategory $category): bool;
}
