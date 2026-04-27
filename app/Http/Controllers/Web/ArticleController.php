<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\ArticleIndexRequest;
use App\Services\Articles\ArticleService;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request, ArticleService $articleService): Response
    {
        return Inertia::render('Article/Index', $articleService->getIndexPageData($request->payload()));
    }

    public function show(string $slug, ArticleService $articleService): Response
    {
        return Inertia::render('Article/Show', $articleService->getShowPageData($slug));
    }
}
