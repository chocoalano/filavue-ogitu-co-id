<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Home\HomeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;

beforeEach(function (): void {
    $this->withoutMiddleware(HandleInertiaRequests::class);

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');
    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('username')->nullable();
        $table->string('ref_code')->nullable();
        $table->timestamps();
    });

    $this->mock(HomeService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getIndexPageData')
            ->once()
            ->andReturn([]);
    });
});

it('stores referral code from home query into session', function (): void {
    DB::table('customers')->insert([
        'username' => 'mitra.kode',
        'ref_code' => 'REF-MLM-001',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get('/?referral_code=REF-MLM-001')
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-MLM-001')
        ->assertSessionHas('referral_username', 'mitra.kode');
});

it('stores referral code from username query into session', function (): void {
    DB::table('customers')->insert([
        'username' => 'mitra.test',
        'ref_code' => '20260311123000-0001',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get('/?username=mitra.test')
        ->assertSuccessful()
        ->assertSessionHas('referral_code', '20260311123000-0001')
        ->assertSessionHas('referral_username', 'mitra.test');
});

it('clears existing referral code session when username query is not found', function (): void {
    $this->withSession([
        'referral_code' => 'REF-SESSION-EXISTING',
        'referral_username' => 'mitra.session',
    ])->get('/?username=username.tidak.ada')
        ->assertSuccessful()
        ->assertSessionMissing('referral_code')
        ->assertSessionMissing('referral_username');
});

it('keeps existing referral code session when query is missing', function (): void {
    $this->withSession([
        'referral_code' => 'REF-SESSION-EXISTING',
        'referral_username' => 'mitra.session',
    ])->get('/')
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-SESSION-EXISTING')
        ->assertSessionHas('referral_username', 'mitra.session');
});

it('clears existing referral session when referral code query is not found', function (): void {
    $this->withSession([
        'referral_code' => 'REF-SESSION-EXISTING',
        'referral_username' => 'mitra.session',
    ])->get('/?referral_code=REF-TIDAK-ADA')
        ->assertSuccessful()
        ->assertSessionMissing('referral_code')
        ->assertSessionMissing('referral_username');
});
