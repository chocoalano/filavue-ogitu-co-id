<?php

namespace App\Repositories\ZennerAcademy\Contracts;

use App\Models\Content;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContentRepositoryInterface
{
    /**
     * Daftar konten dengan filter: search, status, category_id.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Daftar konten berdasarkan kategori dengan filter opsional.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginateByCategory(int $categoryId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): Content;

    public function findBySlug(string $slug): Content;

    /** @param array<string, mixed> $data */
    public function store(array $data): Content;

    /** @param array<string, mixed> $data */
    public function update(Content $content, array $data): Content;

    public function delete(Content $content): bool;
}
