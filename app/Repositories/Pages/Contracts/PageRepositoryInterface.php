<?php

namespace App\Repositories\Pages\Contracts;

use App\Models\Page;
use Illuminate\Support\Collection;

interface PageRepositoryInterface
{
    public function findPublishedBySlug(string $slug): Page;

    /**
     * @return Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>
     */
    public function getPublishedNavigationPages(): Collection;

    /**
     * @return Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>
     */
    public function getPublishedFooterPages(): Collection;
}
