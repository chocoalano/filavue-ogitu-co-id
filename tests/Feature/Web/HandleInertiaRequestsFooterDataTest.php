<?php

use App\Models\Page;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('cache.default', 'array');
    config()->set('session.driver', 'array');
    Cache::flush();

    Schema::dropIfExists('pages');
    Schema::dropIfExists('settings');
    Schema::dropIfExists('payment_methods');
    Schema::dropIfExists('categories');

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
        $table->string('show_on')->nullable();
        $table->unsignedInteger('order')->default(0);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('settings', function (Blueprint $table): void {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->string('type')->default('text');
        $table->string('group')->default('general');
        $table->timestamps();
    });

    Schema::create('payment_methods', function (Blueprint $table): void {
        $table->id();
        $table->string('code');
        $table->string('name');
        $table->boolean('is_active')->default(false);
    });

    Schema::create('categories', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('parent_id')->nullable();
        $table->string('slug');
        $table->string('name');
        $table->text('description')->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->string('image')->nullable();
        $table->timestamps();
    });

    Route::middleware('web')
        ->get('/__test-shared-layout', fn () => Inertia::render('Test/SharedLayout'));
});

function createNavigationPage(array $attributes = []): Page
{
    return Page::query()->create(array_merge([
        'title' => 'Halaman',
        'slug' => 'halaman-'.uniqid(),
        'is_published' => true,
        'template' => 'default',
        'show_on' => 'footer_main',
        'order' => 1,
    ], $attributes));
}

it('shares published pages based on show_on placement', function (): void {
    createNavigationPage([
        'title' => 'Top Bar',
        'slug' => 'top-bar',
        'show_on' => 'header_top_bar',
        'order' => 1,
    ]);

    createNavigationPage([
        'title' => 'Header Navbar',
        'slug' => 'header-navbar',
        'show_on' => 'header_navbar',
        'order' => 2,
    ]);

    createNavigationPage([
        'title' => 'Header Bottom',
        'slug' => 'header-bottom',
        'show_on' => 'header_bottombar',
        'order' => 3,
    ]);

    createNavigationPage([
        'title' => 'Informasi',
        'slug' => 'footer-main',
        'show_on' => 'footer_main',
        'order' => 4,
    ]);

    createNavigationPage([
        'title' => 'Footer Bottom',
        'slug' => 'footer-bottom',
        'show_on' => 'bottom_main',
        'order' => 5,
    ]);

    createNavigationPage([
        'title' => 'No Placement',
        'slug' => 'no-placement',
        'show_on' => null,
        'order' => 6,
    ]);

    createNavigationPage([
        'title' => 'Draft Footer',
        'slug' => 'draft-footer',
        'show_on' => 'footer_main',
        'is_published' => false,
        'order' => 7,
    ]);

    $this->get('/__test-shared-layout')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('footer.headerTopBarPages.0.slug', 'top-bar')
            ->where('footer.headerNavbarPages.0.slug', 'header-navbar')
            ->where('footer.headerBottomBarPages.0.slug', 'header-bottom')
            ->where('footer.pages.0.slug', 'footer-main')
            ->where('footer.bottomMainPages.0.slug', 'footer-bottom')
            ->has('footer.headerTopBarPages', 1)
            ->has('footer.headerNavbarPages', 1)
            ->has('footer.headerBottomBarPages', 1)
            ->has('footer.pages', 1)
            ->has('footer.bottomMainPages', 1)
            ->etc());
});
