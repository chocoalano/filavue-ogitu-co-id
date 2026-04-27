<?php

use App\Models\Article;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Schema::dropIfExists('article_contents');
    Schema::dropIfExists('articles');

    Schema::create('articles', function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        $table->string('image_banner')->nullable();
        $table->boolean('is_published')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('article_contents', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
        $table->longText('content');
        $table->json('tags')->nullable();
        $table->timestamps();
        $table->unique('article_id');
    });

    $this->withoutMiddleware();
});

function createArticleRecord(array $attributes = [], array $tags = []): Article
{
    $article = Article::query()->create(array_merge([
        'title' => 'Artikel Test',
        'slug' => 'artikel-test-'.uniqid(),
        'seo_title' => 'SEO Artikel Test',
        'seo_description' => 'Deskripsi artikel test.',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ], $attributes));

    $article->contents()->create([
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    'text' => '<p>Konten artikel testing.</p>',
                ],
            ],
            [
                'type' => 'image',
                'content' => [
                    'url' => '/storage/articles/example.jpg',
                    'alt' => 'Contoh gambar',
                ],
            ],
        ],
        'tags' => $tags,
    ]);

    return $article;
}

it('shows only published articles with published date in the past', function (): void {
    $publishedArticle = createArticleRecord([
        'title' => 'Artikel Published',
        'slug' => 'artikel-published',
        'image_banner' => 'articles/banners/artikel-published.webp',
        'published_at' => Carbon::parse('2026-01-10 10:00:00'),
    ]);

    createArticleRecord([
        'title' => 'Artikel Draft',
        'slug' => 'artikel-draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    createArticleRecord([
        'title' => 'Artikel Masa Depan',
        'slug' => 'artikel-masa-depan',
        'published_at' => now()->addDays(2),
    ]);

    $deleted = createArticleRecord([
        'title' => 'Artikel Deleted',
        'slug' => 'artikel-deleted',
    ]);
    $deleted->delete();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->has('articles.data', 1)
            ->where('articles.data.0.slug', $publishedArticle->slug)
            ->where('articles.data.0.banner_image', '/media/public/articles/banners/artikel-published.webp')
            ->where('seo.image', '/media/public/articles/banners/artikel-published.webp')
            ->where('filters.sort', 'newest')
            ->where('filters.page', 1)
            ->etc());
});

it('filters article list by search keyword', function (): void {
    createArticleRecord([
        'title' => 'Strategi Omzet Harian',
        'slug' => 'strategi-omzet-harian',
    ]);

    createArticleRecord([
        'title' => 'Pola Rekrutmen Jaringan',
        'slug' => 'pola-rekrutmen-jaringan',
    ]);

    $this->get('/articles?search=omzet')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->has('articles.data', 1)
            ->where('articles.data.0.slug', 'strategi-omzet-harian')
            ->where('filters.search', 'omzet')
            ->etc());
});

it('filters article list by tag', function (): void {
    createArticleRecord([
        'title' => 'Artikel Tag SEO',
        'slug' => 'artikel-tag-seo',
    ], ['seo', 'copywriting']);

    createArticleRecord([
        'title' => 'Artikel Tag Closing',
        'slug' => 'artikel-tag-closing',
    ], ['closing']);

    $this->get('/articles?tag=seo')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->has('articles.data', 1)
            ->where('articles.data.0.slug', 'artikel-tag-seo')
            ->where('filters.tag', 'seo')
            ->etc());
});

it('sorts article list with supported sort options', function (): void {
    createArticleRecord([
        'title' => 'Zulu Notes',
        'slug' => 'zulu-notes',
        'published_at' => Carbon::parse('2026-01-12 10:00:00'),
    ]);

    createArticleRecord([
        'title' => 'Alpha Notes',
        'slug' => 'alpha-notes',
        'published_at' => Carbon::parse('2026-01-10 10:00:00'),
    ]);

    $this->get('/articles?sort=oldest')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->where('articles.data.0.slug', 'alpha-notes')
            ->where('filters.sort', 'oldest')
            ->etc());

    $this->get('/articles?sort=az')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->where('articles.data.0.slug', 'alpha-notes')
            ->where('filters.sort', 'az')
            ->etc());
});

it('keeps pagination metadata and page query', function (): void {
    for ($i = 1; $i <= 11; $i++) {
        createArticleRecord([
            'title' => 'Artikel ke-'.$i,
            'slug' => 'artikel-ke-'.$i,
            'published_at' => now()->subMinutes($i),
        ]);
    }

    $this->get('/articles?page=2')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Index')
            ->where('articles.current_page', 2)
            ->where('filters.page', 2)
            ->where('articles.last_page', 2)
            ->etc());
});

it('shows article detail when slug is valid and published', function (): void {
    $article = createArticleRecord([
        'title' => 'Detail Artikel',
        'slug' => 'detail-artikel',
        'image_banner' => 'articles/banners/detail-artikel.webp',
    ], ['seo']);

    $this->get('/articles/'.$article->slug)
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article/Show')
            ->where('article.slug', 'detail-artikel')
            ->where('article.banner_image', '/media/public/articles/banners/detail-artikel.webp')
            ->where('seo.image', '/media/public/articles/banners/detail-artikel.webp')
            ->has('article.blocks')
            ->etc());
});

it('returns not found when article slug does not exist', function (): void {
    $this->get('/articles/slug-yang-tidak-ada')->assertNotFound();
});

it('returns not found when article exists but is not published', function (): void {
    $draftArticle = createArticleRecord([
        'title' => 'Artikel Belum Publish',
        'slug' => 'artikel-belum-publish',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get('/articles/'.$draftArticle->slug)->assertNotFound();
});
