<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Customer;
use App\Services\Dashboard\DashboardService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->integer('status')->default(3);
        $table->timestamps();
    });

    $this->withoutMiddleware(HandleInertiaRequests::class);
});

it('uses the authenticated customer for generation network payload even during impersonation session', function (): void {
    DB::table('customers')->insert([
        'id' => 21,
        'username' => 'member-impersonated',
        'name' => 'Member Impersonated',
        'email' => 'member-impersonated@example.test',
        'password' => bcrypt('secret123'),
        'status' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $customer = Customer::query()->findOrFail(21);

    $this->mock(DashboardService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getPageData')
            ->once()
            ->withArgs(static function (
                Customer $authenticatedCustomer,
                int $ordersPage,
                int $walletPage,
                array $walletFilters,
                array $orderFilters,
                ?int $networkRootId,
                int $generationPage,
                array $generationFilters,
            ): bool {
                return $authenticatedCustomer->id === 21
                    && $ordersPage === 1
                    && $walletPage === 1
                    && $walletFilters === [
                        'search' => null,
                        'type' => null,
                        'status' => null,
                    ]
                    && $orderFilters === [
                        'q' => null,
                        'status' => null,
                        'sort' => null,
                        'date_from' => null,
                        'date_to' => null,
                    ]
                    && $networkRootId === null
                    && $generationPage === 2
                    && $generationFilters === [
                        'level' => '3',
                    ];
            })
            ->andReturn([
                'customer' => [
                    'id' => 21,
                    'name' => 'Member Impersonated',
                ],
                'generationNetwork' => [
                    'data' => [
                        [
                            'id' => 501,
                            'member_id' => 501,
                            'generation' => 3,
                            'username' => 'gen-3-member',
                            'name' => 'Generasi Tiga',
                            'email' => 'gen-3@example.test',
                            'phone' => '081234567890',
                            'package_name' => 'Gold',
                            'member_level' => 'Executive',
                            'omzet_group' => 1500000,
                            'status' => 3,
                            'status_label' => 'Aktif',
                            'joined_at' => now()->toIso8601String(),
                        ],
                    ],
                    'current_page' => 2,
                    'per_page' => 15,
                    'total' => 16,
                    'last_page' => 2,
                    'from' => 16,
                    'to' => 16,
                    'filters' => [
                        'level' => 3,
                    ],
                    'available_generations' => [
                        ['value' => 1, 'label' => 'Generasi 1'],
                        ['value' => 2, 'label' => 'Generasi 2'],
                        ['value' => 3, 'label' => 'Generasi 3'],
                    ],
                ],
            ]);
    });

    $this->actingAs($customer, 'customer')
        ->withSession([
            'impersonation' => [
                'is_active' => true,
                'admin_id' => 1,
                'admin_name' => 'Admin QA',
            ],
        ])
        ->get(route('dashboard', [
            'section' => 'generation_network',
            'generation_page' => 2,
            'generation_level' => 3,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Dashboard/Index')
            ->where('customer.id', 21)
            ->where('generationNetwork.current_page', 2)
            ->where('generationNetwork.filters.level', 3)
            ->where('generationNetwork.data.0.username', 'gen-3-member')
            ->etc());
});
