<?php

use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerProfileService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('registers customer auth api routes', function (): void {
    expect(route('api.auth.login', [], false))->toBe('/api/auth/login')
        ->and(route('api.auth.logout', [], false))->toBe('/api/auth/logout')
        ->and(route('api.auth.me', [], false))->toBe('/api/auth/me')
        ->and(route('api.auth.me_form', [], false))->toBe('/api/auth/me-form')
        ->and(route('api.auth.me.update', [], false))->toBe('/api/auth/me');
});

it('resolves referral code from username on register-meta endpoint', function (): void {
    prepareRegisterMetaDatabase();

    DB::table('customers')->insert([
        'username' => 'mitra.api',
        'ref_code' => '20260311123000-0001',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson(route('api.auth.register-meta', ['username' => 'mitra.api']))
        ->assertOk()
        ->assertJsonPath('data.referralCode', '20260311123000-0001')
        ->assertJsonPath('data.referralUsername', 'mitra.api');
});

it('clears referral code session on register-meta when username is unknown', function (): void {
    prepareRegisterMetaDatabase();

    $this->withSession([
        'referral_code' => '20260311123000-0009',
        'referral_username' => 'mitra.session',
    ])->getJson(route('api.auth.register-meta', ['username' => 'username.tidak.ada']))
        ->assertOk()
        ->assertJsonPath('data.referralCode', null)
        ->assertJsonPath('data.referralUsername', null);
});

it('returns access token payload when customer api login succeeds', function (): void {
    $customer = makeCustomerForApi(501);

    $this->mock(CustomerAuthService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('attemptApiLogin')
            ->once()
            ->with('member501', 'secret123', 'android-app')
            ->andReturn([
                'customer' => $customer,
                'access_token' => '1|test-token',
                'token_type' => 'Bearer',
            ]);
    });

    $this->postJson(route('api.auth.login'), [
        'username' => 'member501',
        'password' => 'secret123',
        'device_name' => 'android-app',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Login API berhasil.')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.access_token', '1|test-token')
        ->assertJsonPath('data.customer.id', 501)
        ->assertJsonPath('data.customer.username', 'member501');
});

it('returns error response when customer api credentials are invalid', function (): void {
    $this->mock(CustomerAuthService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('attemptApiLogin')
            ->once()
            ->with('member501', 'wrong-password', null)
            ->andReturnNull();
    });

    $this->postJson(route('api.auth.login'), [
        'username' => 'member501',
        'password' => 'wrong-password',
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Username atau kata sandi salah.')
        ->assertJsonPath('errors.username.0', 'Username atau kata sandi salah.');
});

it('rejects api login for customer with non-member status', function (): void {
    $customer = makeCustomerForApi(509);
    $customer->setAttribute('status', 2);

    $this->mock(CustomerAuthService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('attemptApiLogin')
            ->once()
            ->with('member509', 'secret123', null)
            ->andReturn([
                'customer' => $customer,
                'access_token' => '1|test-token',
                'token_type' => 'Bearer',
            ]);
    });

    $this->postJson(route('api.auth.login'), [
        'username' => 'member509',
        'password' => 'secret123',
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Username atau kata sandi salah.')
        ->assertJsonPath('errors.username.0', 'Username atau kata sandi salah.');
});

it('validates required payload for customer api login', function (): void {
    $this->postJson(route('api.auth.login'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'password']);
});

it('returns authenticated customer profile from me endpoint', function (): void {
    $customer = makeCustomerForApi(601);

    $this->mock(CustomerProfileService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getApiProfile')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer): bool {
                return (int) $authenticatedCustomer->id === 601;
            })
            ->andReturn([
                'id' => 601,
                'name' => 'Customer 601',
                'username' => 'member601',
                'email' => 'member601@example.test',
                'phone' => '08123456789',
                'status' => 3,
                'member_package' => 'Gold',
                'referral_code' => 'REF601',
                'account_compleated' => true,
                'summary' => [
                    'total_bonus' => 0,
                    'network_count' => 0,
                    'sponsor_count' => 0,
                ],
                'orders' => [
                    'total' => 0,
                    'processing' => 0,
                    'completed' => 0,
                ],
                'mitra' => [
                    'prospek' => 0,
                    'aktif' => 0,
                    'pasif' => 0,
                ],
                'network_binary' => [
                    'bonus' => 0,
                    'sponsor' => 0,
                    'matching' => 0,
                    'pairing' => 0,
                    'cashback' => 0,
                    'rewards' => 0,
                    'retail' => 0,
                    'lifetime_cash' => 0,
                ],
                'promo' => [
                    'active_count' => 0,
                ],
                'wallet' => [
                    'balance' => 0,
                    'reward_points' => 0,
                    'active' => true,
                ],
            ]);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.auth.me'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Profile loaded')
        ->assertJsonPath('data.id', 601)
        ->assertJsonPath('data.username', 'member601')
        ->assertJsonPath('data.email', 'member601@example.test')
        ->assertJsonPath('data.member_package', 'Gold')
        ->assertJsonPath('data.referral_code', 'REF601')
        ->assertJsonPath('data.account_compleated', true)
        ->assertJsonPath('data.summary.total_bonus', 0)
        ->assertJsonPath('data.orders.processing', 0)
        ->assertJsonPath('data.wallet.active', true);
});

it('returns authenticated customer profile form metadata from me form endpoint', function (): void {
    prepareAuthProfileUpdateDatabase();

    $customer = Customer::query()->create([
        'username' => 'member.form',
        'name' => 'Customer Form',
        'nik' => '3276010101010001',
        'gender' => 'L',
        'email' => 'member.form@example.test',
        'phone' => '081234567891',
        'password' => bcrypt('secret123'),
        'bank_name' => 'BCA',
        'bank_account' => '1234567890',
        'status' => 3,
    ]);

    DB::table('customer_npwp')->insert([
        'member_id' => $customer->id,
        'nama' => 'Customer Form NPWP',
        'npwp' => '12.345.678.9-012.000',
        'jk' => 1,
        'npwp_date' => '2024-01-31',
        'alamat' => 'Jl. Merdeka No. 1',
        'menikah' => 'Y',
        'anak' => '2',
        'kerja' => 'Y',
        'office' => 'PT Contoh',
    ]);

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.auth.me_form'))
        ->assertOk()
        ->assertJsonPath('message', 'Form profil akun berhasil diambil.')
        ->assertJsonPath('data.form.username', 'member.form')
        ->assertJsonPath('data.form.bank_account', '1234567890')
        ->assertJsonPath('data.form.npwp_number', '12.345.678.9-012.000')
        ->assertJsonPath('data.validation.required_fields.0', 'username')
        ->assertJsonPath('data.validation.rules.username.5', 'unique')
        ->assertJsonPath('data.validation.rules.phone.5', 'custom:phone_max_7_accounts')
        ->assertJsonFragment([
            'username.required' => 'Username wajib diisi.',
        ]);
});

it('requires sanctum authentication for auth me form endpoint', function (): void {
    $this->getJson(route('api.auth.me_form'))
        ->assertUnauthorized();
});

it('updates authenticated customer profile from auth me update endpoint', function (): void {
    prepareAuthProfileUpdateDatabase();

    $customer = makeCustomerForApi(901);
    Sanctum::actingAs($customer, ['customer:api']);

    $this->mock(DashboardService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('updateAccountProfile')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer, array $payload) use ($customer): bool {
                return (int) $authenticatedCustomer->id === (int) $customer->id
                    && ($payload['username'] ?? null) === 'member.901'
                    && ($payload['name'] ?? null) === 'Customer 901 Update'
                    && ($payload['nik'] ?? null) === '3276010101010001'
                    && ($payload['gender'] ?? null) === 'L'
                    && ($payload['email'] ?? null) === 'member901.update@example.test'
                    && ($payload['phone'] ?? null) === '081234567891'
                    && ($payload['bank_name'] ?? null) === 'BCA'
                    && ($payload['bank_account'] ?? null) === '1234567890';
            });
    });

    $this->putJson(route('api.auth.me.update'), [
        'username' => 'Member.901',
        'name' => 'Customer 901 Update',
        'nik' => '3276010101010001',
        'gender' => 'male',
        'email' => 'Member901.Update@Example.Test',
        'phone' => '0812 3456 7891',
        'bank_name' => ' BCA ',
        'bank_account' => '123-456-7890',
        'npwp_nama' => null,
        'npwp_number' => null,
        'npwp_jk' => null,
        'npwp_date' => null,
        'npwp_alamat' => null,
        'npwp_menikah' => null,
        'npwp_anak' => null,
        'npwp_kerja' => null,
        'npwp_office' => null,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Profil akun berhasil diperbarui.');
});

it('requires sanctum authentication for auth me update endpoint', function (): void {
    $this->putJson(route('api.auth.me.update'), [])
        ->assertUnauthorized();
});

it('validates payload for auth me update endpoint', function (): void {
    prepareAuthProfileUpdateDatabase();

    Sanctum::actingAs(makeCustomerForApi(902), ['customer:api']);

    $this->putJson(route('api.auth.me.update'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'username',
            'name',
            'nik',
            'gender',
            'email',
            'phone',
            'bank_name',
            'bank_account',
        ]);
});

it('revokes customer api current token on logout', function (): void {
    $customer = makeCustomerForApi(701);

    Sanctum::actingAs($customer, ['customer:api']);

    $this->mock(CustomerAuthService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('logoutApi')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer): bool {
                return (int) $authenticatedCustomer->id === 701;
            });
    });

    $this->postJson(route('api.auth.logout'))
        ->assertOk()
        ->assertJsonPath('message', 'Logout API berhasil.');
});

function makeCustomerForApi(int $id): Customer
{
    $customer = new Customer;
    $customer->forceFill([
        'name' => 'Customer '.$id,
        'username' => 'member'.$id,
        'email' => 'member'.$id.'@example.test',
        'phone' => '08123456789',
        'password' => bcrypt('secret123'),
        'status' => 3,
    ]);
    $customer->setAttribute('id', $id);
    $customer->exists = true;

    return $customer;
}

function prepareAuthProfileUpdateDatabase(): void
{
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');
    Schema::dropIfExists('customer_npwp');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('nik')->nullable();
        $table->string('gender')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('password')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
        $table->integer('status')->default(3);
        $table->timestamps();
    });

    Schema::create('customer_npwp', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id');
        $table->string('nama')->nullable();
        $table->string('npwp')->nullable();
        $table->integer('jk')->nullable();
        $table->date('npwp_date')->nullable();
        $table->string('alamat')->nullable();
        $table->string('menikah')->nullable();
        $table->string('anak')->nullable();
        $table->string('kerja')->nullable();
        $table->string('office')->nullable();
    });
}

function prepareRegisterMetaDatabase(): void
{
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
}
