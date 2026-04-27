<?php

use App\Models\CustomerWalletTransaction;
use App\Models\Setting;
use App\Services\QontactService;
use App\Support\QontakWhatsAppSettings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery as M;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('cache.default', 'array');
    Cache::flush();

    Schema::dropIfExists('settings');
    Schema::dropIfExists('customer_wallet_transactions');
    Schema::dropIfExists('customers');

    Schema::create('settings', function (Blueprint $table): void {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->string('type')->default('text');
        $table->string('group')->default('general');
        $table->timestamps();
    });

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->decimal('balance_before', 16, 2)->default(0);
        $table->decimal('balance_after', 16, 2)->default(0);
        $table->string('status')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('transaction_ref')->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
});

it('builds qontak image header payload with format and params for direct whatsapp send', function (): void {
    $client = M::mock(Client::class);
    $client->shouldReceive('request')
        ->once()
        ->withArgs(function (string $method, string $uri, array $options): bool {
            expect($method)->toBe('POST')
                ->and($uri)->toBe('broadcasts/whatsapp/direct');

            $payload = $options['json'] ?? [];
            $header = $payload['parameters']['header'] ?? null;

            expect($header)->toBeArray()
                ->and($header['format'] ?? null)->toBe('IMAGE')
                ->and($header['params'][0]['key'] ?? null)->toBe('url')
                ->and($header['params'][0]['value'] ?? null)->toBe('https://cdn.example.com/banner/logo.png')
                ->and($header['params'][1]['key'] ?? null)->toBe('filename')
                ->and($header['params'][1]['value'] ?? null)->toBe('logo.png');

            return true;
        })
        ->andReturn(new Response(201, [], json_encode([
            'data' => [
                'id' => 'direct-message-uuid',
            ],
        ])));

    $service = new QontactService($client);

    $result = $service->sendWhatsAppWithResultFromParams(
        'Tester',
        '081234567890',
        'template-uuid',
        ['Tester', 'Pesan test'],
        'id',
        'https://cdn.example.com/banner/logo.png',
    );

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe(201);
});

it('does not fail when multipart client closes the uploaded file handle', function (): void {
    $temporaryFile = tempnam(sys_get_temp_dir(), 'qontact_contact_list_');

    if (! is_string($temporaryFile) || $temporaryFile === '') {
        throw new RuntimeException('Unable to create temporary contact list file.');
    }

    file_put_contents($temporaryFile, "full_name,phone_number\nTester,6281234567890\n");

    $client = M::mock(Client::class);
    $client->shouldReceive('request')
        ->once()
        ->withArgs(function (string $method, string $uri, array $options): bool {
            expect($method)->toBe('POST')
                ->and($uri)->toBe('contacts/contact_lists/async');

            $multipart = $options['multipart'] ?? [];
            $filePart = collect($multipart)->firstWhere('name', 'file');
            $fileHandle = $filePart['contents'] ?? null;

            expect(is_resource($fileHandle))->toBeTrue();

            fclose($fileHandle);

            return true;
        })
        ->andReturn(new Response(201, [], json_encode([
            'data' => [
                'id' => 'contact-list-uuid',
            ],
        ])));

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('createMultipartClient')
        ->once()
        ->andReturn($client);

    $result = $service->createContactListAsync('Bulk Contacts', $temporaryFile);

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe(201)
        ->and($result['body']['data']['id'] ?? null)->toBe('contact-list-uuid');

    @unlink($temporaryFile);
});

it('waits until contact list processing is finished before returning ready', function (): void {
    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('getContactList')
        ->twice()
        ->with('contact-list-uuid')
        ->andReturn(
            [
                'data' => [
                    'id' => 'contact-list-uuid',
                    'progress' => 'processing',
                    'contacts_count_success' => 0,
                    'finished_at' => null,
                ],
                'error' => null,
                'status' => 200,
            ],
            [
                'data' => [
                    'id' => 'contact-list-uuid',
                    'progress' => 'success',
                    'contacts_count_success' => 195,
                    'finished_at' => now()->toIso8601String(),
                ],
                'error' => null,
                'status' => 200,
            ],
        );

    $result = $service->waitUntilContactListReady('contact-list-uuid', 2, 0);

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe(200)
        ->and($result['data']['contacts_count_success'] ?? null)->toBe(195);
});

it('fails when qontak finishes processing a contact list without imported contacts', function (): void {
    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('getContactList')
        ->once()
        ->with('contact-list-empty')
        ->andReturn([
            'data' => [
                'id' => 'contact-list-empty',
                'progress' => 'success',
                'contacts_count_success' => 0,
                'finished_at' => now()->toIso8601String(),
                'error_messages' => [],
            ],
            'error' => null,
            'status' => 200,
        ]);

    $result = $service->waitUntilContactListReady('contact-list-empty', 1, 0);

    expect($result['success'])->toBeFalse()
        ->and($result['status'])->toBe(200)
        ->and($result['error'])->toBe('Qontak selesai memproses contact list, tetapi tidak ada kontak yang berhasil diimpor.');
});

it('retries whatsapp bulk when qontak returns rate limit response', function (): void {
    $client = M::mock(Client::class);
    $attempt = 0;

    $client->shouldReceive('request')
        ->twice()
        ->with('POST', 'broadcasts/whatsapp', M::type('array'))
        ->andReturnUsing(function () use (&$attempt): Response {
            $attempt++;

            if ($attempt === 1) {
                throw new RequestException(
                    'Too many requests',
                    new Request('POST', 'broadcasts/whatsapp'),
                    new Response(429, [], json_encode([
                        'status' => 'error',
                        'error' => [
                            'code' => 429,
                            'messages' => [
                                'Too many requests',
                                'rate_limit_reset: 66',
                            ],
                        ],
                    ]))
                );
            }

            return new Response(201, [], json_encode([
                'data' => [
                    'id' => 'broadcast-uuid',
                ],
            ]));
        });

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class, [$client]);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('pauseMilliseconds')
        ->once()
        ->with(68000);

    config()->set('services.qontak.bulk_retry_attempts', 2);
    config()->set('services.qontak.bulk_retry_buffer_seconds', 2);

    $result = $service->sendWhatsAppBulk(
        'Testing6',
        'template-uuid',
        'contact-list-uuid',
        [
            ['key' => '1', 'value' => 'full_name'],
        ],
        'channel-uuid',
        55,
    );

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe(201)
        ->and($result['body']['data']['id'] ?? null)->toBe('broadcast-uuid');
});

it('uses stored qontak retry settings instead of config defaults', function (): void {
    QontakWhatsAppSettings::writeState([
        'broadcast' => [
            'bulk_retry_attempts' => 3,
            'bulk_retry_buffer_seconds' => 7,
        ],
    ]);

    $client = M::mock(Client::class);

    $client->shouldReceive('request')
        ->times(3)
        ->with('POST', 'broadcasts/whatsapp', M::type('array'))
        ->andThrow(new RequestException(
            'Too many requests',
            new Request('POST', 'broadcasts/whatsapp'),
            new Response(429, [], json_encode([
                'status' => 'error',
                'error' => [
                    'code' => 429,
                    'messages' => [
                        'Too many requests',
                        'rate_limit_reset: 10',
                    ],
                ],
            ]))
        ));

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class, [$client]);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('pauseMilliseconds')
        ->twice()
        ->with(17000);

    config()->set('services.qontak.bulk_retry_attempts', 1);
    config()->set('services.qontak.bulk_retry_buffer_seconds', 1);

    $result = $service->sendWhatsAppBulk(
        'Stored Retry Settings',
        'template-uuid',
        'contact-list-uuid',
        [
            ['key' => '1', 'value' => 'full_name'],
        ],
        'channel-uuid',
        77,
    );

    expect($result['success'])->toBeFalse()
        ->and($result['status'])->toBe(429)
        ->and($result['error'])->toBe('Too many requests');
});

it('builds withdrawal approval params from stored mapping and transaction data', function (): void {
    seedWithdrawalTransactionForQontact(9001, 8001, 125000, [
        'notes' => 'Biaya admin: Rp 5.000',
    ]);

    QontakWhatsAppSettings::writeState([
        'notifications' => [
            'withdrawal_approved' => [
                'template_id' => 'tmpl-approved',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'full_name',
                        'source_table' => 'customers',
                        'source_column' => 'name',
                    ],
                    [
                        'key' => '2',
                        'value' => 'nominal',
                        'source_table' => 'computed',
                        'source_column' => 'nominal_text',
                    ],
                ],
            ],
        ],
    ]);

    $transaction = CustomerWalletTransaction::query()
        ->with('customer')
        ->findOrFail(9001);

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('getWhatsAppTemplateParams')
        ->once()
        ->with('tmpl-approved')
        ->andReturn([
            ['key' => '1', 'value' => 'full_name'],
            ['key' => '2', 'value' => 'nominal'],
        ]);

    $params = $service->buildWithdrawalNotificationBodyParams('withdrawal_approved', $transaction);

    expect($params)->toBe([
        [
            'key' => '1',
            'value' => 'full_name',
            'value_text' => 'Customer 8001',
        ],
        [
            'key' => '2',
            'value' => 'nominal',
            'value_text' => '125.000 - 5.000 = 120.000',
        ],
    ]);
});

it('sends withdrawal approved notification using mapped params and configured header image', function (): void {
    seedWithdrawalTransactionForQontact(9002, 8002, 99000, [
        'notes' => 'Biaya admin: Rp 4.000',
    ]);

    QontakWhatsAppSettings::writeState([
        'connection' => [
            'api_token' => 'secret-token',
            'channel_integration_id' => 'channel-uuid',
        ],
        'notifications' => [
            'withdrawal_approved' => [
                'enabled' => true,
                'template_id' => 'tmpl-approved',
                'header_image_url' => 'https://cdn.example.com/banner.png',
                'parameters' => [
                    [
                        'key' => '1',
                        'value' => 'full_name',
                        'source_table' => 'customers',
                        'source_column' => 'name',
                    ],
                    [
                        'key' => '2',
                        'value' => 'nominal',
                        'source_table' => 'computed',
                        'source_column' => 'nominal_text',
                    ],
                ],
            ],
        ],
    ]);

    $client = M::mock(Client::class);
    $client->shouldReceive('request')
        ->once()
        ->withArgs(function (string $method, string $uri, array $options): bool {
            expect($method)->toBe('POST')
                ->and($uri)->toBe('broadcasts/whatsapp/direct');

            $payload = $options['json'] ?? [];
            $body = $payload['parameters']['body'] ?? [];
            $header = $payload['parameters']['header'] ?? [];

            expect($payload['to_name'] ?? null)->toBe('Customer 8002')
                ->and($payload['to_number'] ?? null)->toBe('6281234567890')
                ->and($payload['message_template_id'] ?? null)->toBe('tmpl-approved')
                ->and($body[0]['value_text'] ?? null)->toBe('Customer 8002')
                ->and($body[1]['value_text'] ?? null)->toBe('99.000 - 4.000 = 95.000')
                ->and($header['format'] ?? null)->toBe('IMAGE')
                ->and($header['params'][0]['value'] ?? null)->toBe('https://cdn.example.com/banner.png');

            return true;
        })
        ->andReturn(new Response(201, [], json_encode([
            'data' => [
                'id' => 'direct-withdrawal-approved',
            ],
        ])));

    $transaction = CustomerWalletTransaction::query()
        ->with('customer')
        ->findOrFail(9002);

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class, [$client]);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('getWhatsAppTemplateParams')
        ->once()
        ->with('tmpl-approved')
        ->andReturn([
            ['key' => '1', 'value' => 'full_name'],
            ['key' => '2', 'value' => 'nominal'],
        ]);

    expect($service->sendWithdrawalApprovedNotification($transaction))->toBeTrue();
});

it('skips withdrawal approved send when notification is disabled in qontak settings', function (): void {
    Setting::query()->create([
        'key' => 'qontak.notifications.withdrawal_approved.enabled',
        'value' => '0',
        'type' => 'text',
        'group' => 'qontak',
    ]);

    QontakWhatsAppSettings::forgetCache();

    /** @var QontactService&MockInterface $service */
    $service = M::mock(QontactService::class);
    $service->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    $service->shouldNotReceive('sendWhatsApp');

    expect($service->sendWithdrawalApproved(
        'Tester',
        '081234567890',
        '100.000',
        '10.000',
        '90.000',
    ))->toBeTrue();
});

function seedWithdrawalTransactionForQontact(int $transactionId, int $customerId, float $amount, array $overrides = []): void
{
    DB::table('customers')->insert([
        'id' => $customerId,
        'name' => 'Customer '.$customerId,
        'phone' => '081234567890',
        'email' => 'customer'.$customerId.'@example.test',
        'bank_name' => 'BCA',
        'bank_account' => '1234567890',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_wallet_transactions')->insert(array_merge([
        'id' => $transactionId,
        'customer_id' => $customerId,
        'type' => 'withdrawal',
        'amount' => $amount,
        'balance_before' => 500000,
        'balance_after' => 375000,
        'status' => 'completed',
        'payment_method' => 'bank_transfer',
        'transaction_ref' => 'WD-'.$transactionId,
        'notes' => null,
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}
