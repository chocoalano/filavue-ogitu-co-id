<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Pages\PageService;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug, PageService $pageService): Response
    {
        return Inertia::render('Page/Show', $pageService->getShowPageData($slug));
    }
}

