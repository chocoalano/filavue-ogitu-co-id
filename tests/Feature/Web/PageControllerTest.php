<?php

use App\Models\Page;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Schema::dropIfExists('pages');

    Schema::create('pages', function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->longText('content')->nullable();
        $table->json('blocks')->nullable();
        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        $table->boolean('is_published')->default(false);
        $table->string('template')->default('default');
        $table->unsignedInteger('order')->default(0);
        $table->timestamps();
        $table->softDeletes();
    });

    $this->withoutMiddleware();
});

function createPageRecord(array $attributes = []): Page
{
    return Page::query()->create(array_merge([
        'title' => 'Tentang Kami',
        'slug' => 'tentang-kami',
        'content' => '<p>Ini konten halaman.</p>',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'headline' => 'Selamat Datang',
                    'subheadline' => 'Kenali lebih jauh brand kami.',
                    'primary_cta_label' => 'Hubungi Kami',
                    'primary_cta_url' => '/contact',
                ],
            ],
        ],
        'seo_title' => 'Tentang Kami',
        'seo_description' => 'Profil perusahaan kami.',
        'is_published' => true,
        'template' => 'about',
        'order' => 1,
    ], $attributes));
}

it('shows published page by slug', function (): void {
    $page = createPageRecord();

    $this->get('/page/' . $page->slug)
        ->assertSuccessful()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Page/Show')
            ->where('page.slug', $page->slug)
            ->where('page.title', $page->title)
            ->has('page.blocks', 1)
            ->where('page.blocks.0.type', 'hero')
            ->etc());
});

it('returns not found for unpublished page', function (): void {
    $page = createPageRecord([
        'slug' => 'kebijakan-privasi',
        'is_published' => false,
    ]);

    $this->get('/page/' . $page->slug)->assertNotFound();
});

