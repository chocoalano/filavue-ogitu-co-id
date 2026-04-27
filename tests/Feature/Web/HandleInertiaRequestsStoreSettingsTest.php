<?php

use App\Models\Setting;
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
        ->get('/__test-store-settings', fn () => Inertia::render('Test/SharedLayout'));
});

it('shares affiliate cta settings to inertia storefront props', function (): void {
    $settings = [
        'affiliate_cta.badge_label' => 'Program Mitra',
        'affiliate_cta.heading_main' => 'Bangun Jaringan',
        'affiliate_cta.heading_sub' => 'Dari Rumah',
        'affiliate_cta.description' => 'Kelola bisnis kemitraan dengan sistem komisi yang fleksibel.',
        'affiliate_cta.stat1_title' => 'Bonus Harian',
        'affiliate_cta.stat1_description' => 'Bonus langsung masuk ke wallet setelah transaksi tervalidasi.',
        'affiliate_cta.stat2_value' => '85%',
        'affiliate_cta.stat2_label' => 'Margin Komisi',
        'affiliate_cta.stat3_value' => '250+',
        'affiliate_cta.stat3_label' => 'Kota Aktif',
        'affiliate_cta.floating_label' => 'Reward Tahunan',
        'affiliate_cta.floating_value' => 'Trip Internasional',
        'affiliate_cta.primary_cta_label' => 'Daftar Mitra',
        'affiliate_cta.primary_cta_url' => '/mitra/daftar',
        'affiliate_cta.secondary_cta_label' => 'Lihat Plan',
        'affiliate_cta.secondary_cta_url' => '/mitra/plan',
        'affiliate_cta.footer_note' => 'Bonus dihitung otomatis setiap closing transaksi.',
        'affiliate_cta.benefits' => json_encode([
            ['icon' => 'i-lucide-wallet', 'label' => 'Komisi Retail', 'value' => '15%', 'description' => 'Komisi langsung masuk setelah order lunas.'],
            ['icon' => 'i-lucide-network', 'label' => 'Bonus Jaringan', 'value' => 'Tanpa Batas', 'description' => 'Jaringan aktif menghasilkan bonus berulang.'],
        ], JSON_THROW_ON_ERROR),
    ];

    foreach ($settings as $key => $value) {
        Setting::query()->create([
            'key' => $key,
            'value' => $value,
            'type' => 'text',
            'group' => 'general',
        ]);
    }

    $this->get('/__test-store-settings')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('storeSettings.affiliate_cta.badge_label', 'Program Mitra')
            ->where('storeSettings.affiliate_cta.heading_main', 'Bangun Jaringan')
            ->where('storeSettings.affiliate_cta.heading_sub', 'Dari Rumah')
            ->where('storeSettings.affiliate_cta.primary_cta_url', '/mitra/daftar')
            ->where('storeSettings.affiliate_cta.secondary_cta_url', '/mitra/plan')
            ->where('storeSettings.affiliate_cta.benefits.0.label', 'Komisi Retail')
            ->where('storeSettings.affiliate_cta.benefits.0.value', '15%')
            ->where('storeSettings.affiliate_cta.benefits.0.description', 'Komisi langsung masuk setelah order lunas.')
            ->where('storeSettings.affiliate_cta.benefits.1.label', 'Bonus Jaringan')
            ->etc());
});
