<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Auth\CustomerRegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sponsor_id')->nullable();
        $table->string('ref_code')->nullable();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('nik')->nullable();
        $table->string('gender')->nullable();
        $table->text('alamat')->nullable();
        $table->string('password')->nullable();
        $table->integer('status')->default(1);
        $table->timestamps();
    });

    $this->withoutMiddleware(HandleInertiaRequests::class);
});

it('shows register page with username referral resolved from referral code query', function (): void {
    config()->set('app.debug', true);

    DB::table('customers')->insert([
        'username' => 'mitra.register',
        'ref_code' => 'REF-REGISTER-001',
        'name' => 'Mitra Register',
        'email' => 'mitra.register@example.test',
        'phone' => '081234567800',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get(route('register', ['referral_code' => 'REF-REGISTER-001']))
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-REGISTER-001')
        ->assertSessionHas('referral_username', 'mitra.register')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->where('debugMode', true)
            ->where('referralCode', 'REF-REGISTER-001')
            ->where('referralUsername', 'mitra.register')
            ->etc());
});

it('hydrates referral username on register page from existing session referral code', function (): void {
    DB::table('customers')->insert([
        'username' => 'mitra.session',
        'ref_code' => 'REF-SESSION-001',
        'name' => 'Mitra Session',
        'email' => 'mitra.session@example.test',
        'phone' => '081234567804',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->withSession([
        'referral_code' => 'REF-SESSION-001',
    ])->get(route('register'))
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-SESSION-001')
        ->assertSessionHas('referral_username', 'mitra.session')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->where('referralCode', 'REF-SESSION-001')
            ->where('referralUsername', 'mitra.session')
            ->etc());
});

it('registers customer with sponsor resolved from referral username', function (): void {
    $sponsorId = DB::table('customers')->insertGetId([
        'username' => 'mitra.sponsor',
        'ref_code' => 'REF-SPONSOR-001',
        'name' => 'Mitra Sponsor',
        'email' => 'mitra.sponsor@example.test',
        'phone' => '081234567801',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post(route('register.store'), [
        'name' => 'Member Baru',
        'username' => 'Member.Baru',
        'email' => 'Member.Baru@example.test',
        'telp' => '081234567899',
        'nik' => '3276010101010001',
        'gender' => 'L',
        'alamat' => 'Jl. Mawar No. 10',
        'referral_username' => '@Mitra.Sponsor',
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
        'terms' => true,
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Akun berhasil dibuat! Silakan masuk.');

    $registeredCustomer = DB::table('customers')
        ->where('email', 'member.baru@example.test')
        ->first();

    expect($registeredCustomer)->not->toBeNull()
        ->and((int) $registeredCustomer->sponsor_id)->toBe($sponsorId)
        ->and($registeredCustomer->username)->toBe('member.baru');
});

it('logs unexpected register errors and redirects back with generic error', function (): void {
    Log::spy();

    DB::table('customers')->insert([
        'username' => 'mitra.debug',
        'ref_code' => 'REF-DEBUG-001',
        'name' => 'Mitra Debug',
        'email' => 'mitra.debug@example.test',
        'phone' => '081234567805',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->mock(CustomerRegistrationService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('register')
            ->once()
            ->andThrow(new RuntimeException('DB timeout while registering customer'));
    });

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Member Gagal',
            'username' => 'member.gagal',
            'email' => 'member.gagal@example.test',
            'telp' => '081234567897',
            'nik' => '3276010101010002',
            'gender' => 'P',
            'alamat' => 'Jl. Melati No. 7',
            'referral_username' => 'mitra.debug',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'terms' => true,
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'error' => 'Pendaftaran gagal. Silakan coba lagi.',
        ]);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(static function (string $message, array $context): bool {
            return $message === 'Customer registration failed.'
                && ($context['username'] ?? null) === 'member.gagal'
                && ($context['email'] ?? null) === 'member.gagal@example.test'
                && ($context['telp'] ?? null) === '081234567897'
                && ($context['referral_username'] ?? null) === 'mitra.debug'
                && ($context['referral_code'] ?? null) === ''
                && ($context['exception'] ?? null) === RuntimeException::class
                && ($context['message'] ?? null) === 'DB timeout while registering customer';
        });
});

it('rejects mismatched referral username and code values during register', function (): void {
    DB::table('customers')->insert([
        [
            'username' => 'mitra.satu',
            'ref_code' => 'REF-SPONSOR-ONE',
            'name' => 'Mitra Satu',
            'email' => 'mitra.satu@example.test',
            'phone' => '081234567802',
            'password' => bcrypt('secret123'),
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'username' => 'mitra.dua',
            'ref_code' => 'REF-SPONSOR-TWO',
            'name' => 'Mitra Dua',
            'email' => 'mitra.dua@example.test',
            'phone' => '081234567803',
            'password' => bcrypt('secret123'),
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Member Konflik',
            'username' => 'member.konflik',
            'email' => 'member.konflik@example.test',
            'telp' => '081234567898',
            'gender' => 'P',
            'referral_username' => 'mitra.satu',
            'referral_code' => 'REF-SPONSOR-TWO',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'terms' => true,
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'referral_username' => 'Username referral tidak sesuai dengan kode referral.',
        ]);
});
