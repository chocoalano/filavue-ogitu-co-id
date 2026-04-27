<?php

namespace App\Repositories\Pages;

use App\Models\Page;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EloquentPageRepository implements PageRepositoryInterface
{
    public function findPublishedBySlug(string $slug): Page
    {
        return $this->basePublishedQuery()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>
     */
    public function getPublishedNavigationPages(): Collection
    {
        $hasShowOnColumn = Schema::hasColumn('pages', 'show_on');
        $columns = $hasShowOnColumn
            ? ['title', 'slug', 'template', 'show_on']
            : ['title', 'slug', 'template'];

        return $this->basePublishedQuery()
            ->orderBy('order')
            ->orderBy('title')
            ->get($columns)
            ->map(fn (Page $page): array => [
                'title' => (string) $page->title,
                'slug' => (string) $page->slug,
                'template' => is_string($page->template) ? $page->template : null,
                'show_on' => $hasShowOnColumn && is_string($page->show_on) ? $page->show_on : null,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>
     */
    public function getPublishedFooterPages(): Collection
    {
        return $this->getPublishedNavigationPages()
            ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'footer_main')
            ->values();
    }

    private function basePublishedQuery(): Builder
    {
        return Page::query()
            ->where('is_published', true);
    }
}
