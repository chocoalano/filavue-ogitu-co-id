<?php

namespace App\Services;

use App\Models\CustomerWalletTransaction;
use App\Models\WhatsAppOutboundLog;
use App\Support\QontakWhatsAppSettings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QontactService
{
    protected string $baseUrl;

    protected string $token;

    protected string $channelIntegrationId;

    protected Client $client;

    /**
     * Cache template data.
     *
     * @var array<string, array{
     *     label: string,
     *     variable_count: int,
     *     variables: list<int>,
     *     params: list<array{key: string, value: string}>
     * }>|null
     */
    private ?array $templatesCache = null;

    /**
     * Cache integrations data.
     *
     * @var list<array<string, mixed>>|null
     */
    private ?array $integrationsCache = null;

    public function __construct(?Client $client = null)
    {
        $settings = QontakWhatsAppSettings::getState();

        $this->baseUrl = rtrim(
            (string) ($settings['connection']['base_url'] ?? config('services.qontak.base_url', 'https://service-chat.qontak.com/api/open/v1')),
            '/'
        );

        $this->token = trim((string) ($settings['connection']['api_token'] ?? config('services.qontak.api_token')));
        $this->channelIntegrationId = trim((string) ($settings['connection']['channel_integration_id'] ?? config('services.qontak.channel_integration_id')));

        $this->client = $client ?: new Client([
            'base_uri' => $this->baseUrl.'/',
            'timeout' => (int) ($settings['connection']['timeout'] ?? config('services.qontak.timeout', 30)),
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendWhatsApp(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
        ?string $headerImageUrl = null,
    ): bool {
        $result = $this->sendWhatsAppWithResultFromParams(
            $toName,
            $toNumber,
            $templateId,
            $bodyParams,
            $languageCode,
            $headerImageUrl
        );

        return (bool) ($result['success'] ?? false);
    }

    /**
     * Kirim template tanpa parameter sama sekali.
     *
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppNoParams(
        string $toName,
        string $toNumber,
        string $templateId,
        string $languageCode = 'id',
        int|string|null $broadcastId = null,
    ): array {
        $toNumber = $this->normalizePhoneNumber($toNumber);

        if ($toNumber === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Nomor tujuan tidak valid.',
                'body' => null,
            ];
        }

        $payload = $this->buildBasePayload(
            toName: $toName,
            toNumber: $toNumber,
            templateId: $templateId,
            languageCode: $languageCode,
        );

        return $this->sendWhatsAppWithResult($payload, $toNumber, $templateId, $broadcastId);
    }

    /**
     * Kirim template dengan body params dan optional image header.
     *
     * @param  list<mixed>  $bodyParams
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppWithResultFromParams(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
        ?string $headerImageUrl = null,
        int|string|null $broadcastId = null,
        ?string $channelIntegrationId = null,
    ): array {
        $toNumber = $this->normalizePhoneNumber($toNumber);

        if ($toNumber === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Nomor tujuan tidak valid.',
                'body' => null,
            ];
        }

        $payload = $this->buildBasePayload(
            toName: $toName,
            toNumber: $toNumber,
            templateId: $templateId,
            languageCode: $languageCode,
            channelIntegrationId: $channelIntegrationId,
        );

        $parameters = [];

        $bodyItems = $this->buildBodyParametersFromValues($templateId, $bodyParams);
        if ($bodyItems !== []) {
            $parameters['body'] = $bodyItems;
        }

        if (filled($headerImageUrl)) {
            $headerPayload = $this->buildImageHeaderParameters((string) $headerImageUrl);

            if ($headerPayload !== []) {
                $parameters['header'] = $headerPayload;
            }
        }

        if ($parameters !== []) {
            $payload['parameters'] = $parameters;
        }

        return $this->sendWhatsAppWithResult($this->cleanArray($payload), $toNumber, $templateId, $broadcastId);
    }

    /**
     * Kirim template dengan parameter yang sudah diformat.
     *
     * @param  list<array{key?: string, value?: string, value_text?: string}>  $bodyParams
     * @param  list<array{key?: string, value?: string, value_text?: string}>  $headerParams
     * @param  list<array{key?: string, value?: string, value_text?: string}>  $buttonParams
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppWithFormattedParams(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
        array $headerParams = [],
        array $buttonParams = [],
        int|string|null $broadcastId = null,
        ?string $channelIntegrationId = null,
    ): array {
        $toNumber = $this->normalizePhoneNumber($toNumber);

        if ($toNumber === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Nomor tujuan tidak valid.',
                'body' => null,
            ];
        }

        $payload = $this->buildBasePayload(
            toName: $toName,
            toNumber: $toNumber,
            templateId: $templateId,
            languageCode: $languageCode,
            channelIntegrationId: $channelIntegrationId,
        );

        $parameters = [];

        $normalizedBodyParams = $this->normalizeFormattedParameters($bodyParams);
        if ($normalizedBodyParams !== []) {
            $parameters['body'] = $normalizedBodyParams;
        }

        $normalizedHeaderParams = $this->normalizeFormattedHeaderParameters($headerParams);
        if ($normalizedHeaderParams !== []) {
            $parameters['header'] = $normalizedHeaderParams;
        }

        $normalizedButtonParams = $this->normalizeFormattedParameters($buttonParams);
        if ($normalizedButtonParams !== []) {
            $parameters['buttons'] = $normalizedButtonParams;
        }

        if ($parameters !== []) {
            $payload['parameters'] = $parameters;
        }

        return $this->sendWhatsAppWithResult($this->cleanArray($payload), $toNumber, $templateId, $broadcastId);
    }

    /**
     * Kirim payload mentah yang sudah siap.
     *
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppRaw(array $payload, int|string|null $broadcastId = null): array
    {
        $toNumber = $this->normalizePhoneNumber((string) ($payload['to_number'] ?? ''));
        $templateId = (string) ($payload['message_template_id'] ?? '');

        if ($toNumber === '' || $templateId === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Payload wajib berisi to_number dan message_template_id.',
                'body' => null,
            ];
        }

        $payload['to_number'] = $toNumber;

        if (! isset($payload['channel_integration_id']) || blank($payload['channel_integration_id'])) {
            $payload['channel_integration_id'] = $this->channelIntegrationId;
        }

        if (! isset($payload['language']) || ! is_array($payload['language'])) {
            $payload['language'] = ['code' => 'id'];
        }

        return $this->sendWhatsAppWithResult($this->cleanArray($payload), $toNumber, $templateId, $broadcastId);
    }

    /**
     * Buat contact list Qontak secara async untuk kebutuhan bulk broadcast.
     *
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function createContactListAsync(
        string $name,
        string $filePath,
        string $sourceType = 'contacts',
    ): array {
        $name = trim($name);
        $sourceType = trim($sourceType);

        if ($name === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Nama contact list wajib diisi.',
                'body' => null,
            ];
        }

        if ($sourceType === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Source type contact list wajib diisi.',
                'body' => null,
            ];
        }

        if (! is_file($filePath) || ! is_readable($filePath)) {
            return [
                'success' => false,
                'status' => null,
                'error' => 'File contact list tidak ditemukan atau tidak dapat dibaca.',
                'body' => null,
            ];
        }

        $fileHandle = fopen($filePath, 'rb');

        if ($fileHandle === false) {
            return [
                'success' => false,
                'status' => null,
                'error' => 'File contact list tidak dapat dibuka.',
                'body' => null,
            ];
        }

        try {
            Log::info('Qontak contact list payload', [
                'name' => $name,
                'source_type' => $sourceType,
                'file_name' => basename($filePath),
            ]);

            $response = $this->createMultipartClient()->request('POST', 'contacts/contact_lists/async', [
                'multipart' => [
                    [
                        'name' => 'name',
                        'contents' => $name,
                    ],
                    [
                        'name' => 'source_type',
                        'contents' => $sourceType,
                    ],
                    [
                        'name' => 'file',
                        'contents' => $fileHandle,
                        'filename' => basename($filePath),
                        'headers' => [
                            'Content-Type' => 'text/csv',
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            Log::info('Qontak contact list created', [
                'name' => $name,
                'status' => $statusCode,
                'body' => $body,
            ]);

            return [
                'success' => true,
                'status' => $statusCode,
                'error' => null,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            $errorMessage = $this->extractQontakErrorMessage($body) ?? $e->getMessage();

            Log::warning('Qontak contact list creation failed', [
                'name' => $name,
                'status' => $statusCode,
                'error' => $errorMessage,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'status' => $statusCode,
                'error' => $errorMessage,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Qontak contact list creation error', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
                'body' => null,
            ];
        } finally {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }

    /**
     * @return array{data: array<string, mixed>|null, error: string|null, status: int|null}
     */
    public function getContactList(string $contactListId): array
    {
        $contactListId = trim($contactListId);

        if ($contactListId === '') {
            return [
                'data' => null,
                'error' => 'Contact list ID Qontak wajib diisi.',
                'status' => null,
            ];
        }

        try {
            $response = $this->client->request('GET', "contacts/contact_lists/{$contactListId}");
            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);
            $data = $body['data'] ?? null;

            return [
                'data' => is_array($data) ? $data : null,
                'error' => null,
                'status' => $statusCode,
            ];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            return [
                'data' => null,
                'error' => $this->extractQontakErrorMessage($body) ?? $e->getMessage(),
                'status' => $statusCode,
            ];
        } catch (\Throwable $e) {
            return [
                'data' => null,
                'error' => $e->getMessage(),
                'status' => null,
            ];
        }
    }

    /**
     * Tunggu sampai contact list async benar-benar selesai diproses oleh Qontak.
     *
     * @return array{success: bool, status: int|null, error: string|null, data: array<string, mixed>|null}
     */
    public function waitUntilContactListReady(
        string $contactListId,
        int $maxAttempts = 10,
        int $delayMilliseconds = 1000,
    ): array {
        $maxAttempts = max(1, $maxAttempts);
        $delayMilliseconds = max(0, $delayMilliseconds);

        $lastStatusCode = null;
        $lastData = null;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $this->getContactList($contactListId);
            $lastStatusCode = $result['status'];
            $lastData = $result['data'];
            $lastError = $result['error'];

            if ($lastError !== null) {
                return [
                    'success' => false,
                    'status' => $lastStatusCode,
                    'error' => $lastError,
                    'data' => $lastData,
                ];
            }

            $progress = trim((string) ($lastData['progress'] ?? ''));
            $contactsCount = (int) ($lastData['contacts_count_success'] ?? $lastData['contacts_count'] ?? 0);
            $finishedAt = $lastData['finished_at'] ?? null;

            if ($progress === 'success' && $contactsCount > 0) {
                return [
                    'success' => true,
                    'status' => $lastStatusCode,
                    'error' => null,
                    'data' => $lastData,
                ];
            }

            if (in_array($progress, ['failed', 'error'], true) || ($finishedAt !== null && $contactsCount === 0)) {
                return [
                    'success' => false,
                    'status' => $lastStatusCode,
                    'error' => $this->extractContactListErrorMessage($lastData),
                    'data' => $lastData,
                ];
            }

            if ($attempt < $maxAttempts && $delayMilliseconds > 0) {
                usleep($delayMilliseconds * 1000);
            }
        }

        return [
            'success' => false,
            'status' => $lastStatusCode,
            'error' => $lastError ?? 'Contact list Qontak belum siap dipakai untuk bulk broadcast.',
            'data' => $lastData,
        ];
    }

    /**
     * Kirim WhatsApp bulk melalui contact list Qontak.
     *
     * @param  list<array{key?: string, value?: string}>  $bodyParams
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppBulk(
        string $name,
        string $templateId,
        string $contactListId,
        array $bodyParams = [],
        ?string $channelIntegrationId = null,
        int|string|null $broadcastId = null,
    ): array {
        $name = trim($name);
        $templateId = trim($templateId);
        $contactListId = trim($contactListId);
        $resolvedChannelIntegrationId = trim((string) ($channelIntegrationId ?: $this->channelIntegrationId));

        if ($name === '' || $templateId === '' || $contactListId === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Payload bulk wajib berisi name, message_template_id, dan contact_list_id.',
                'body' => null,
            ];
        }

        if ($resolvedChannelIntegrationId === '') {
            return [
                'success' => false,
                'status' => null,
                'error' => 'Channel integration Qontak wajib diisi untuk bulk broadcast.',
                'body' => null,
            ];
        }

        $payload = [
            'name' => $name,
            'message_template_id' => $templateId,
            'contact_list_id' => $contactListId,
            'channel_integration_id' => $resolvedChannelIntegrationId,
            'parameters' => [
                'body' => $this->normalizeBulkParameters($bodyParams),
            ],
        ];

        $maxAttempts = max(1, (int) QontakWhatsAppSettings::get('broadcast.bulk_retry_attempts', config('services.qontak.bulk_retry_attempts', 2)));
        $bufferSeconds = max(0, (int) QontakWhatsAppSettings::get('broadcast.bulk_retry_buffer_seconds', config('services.qontak.bulk_retry_buffer_seconds', 2)));

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info('Qontak WhatsApp bulk payload', [
                    'name' => $name,
                    'template' => $templateId,
                    'contact_list_id' => $contactListId,
                    'attempt' => $attempt,
                    'payload' => $payload,
                ]);

                $response = $this->client->request('POST', 'broadcasts/whatsapp', [
                    'json' => $payload,
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode((string) $response->getBody(), true);

                Log::info('Qontak WhatsApp bulk created', [
                    'name' => $name,
                    'template' => $templateId,
                    'contact_list_id' => $contactListId,
                    'attempt' => $attempt,
                    'status' => $statusCode,
                    'body' => $body,
                ]);

                if (is_array($body) && is_array($body['data'] ?? null) && isset($body['data']['id'])) {
                    $body['data']['contact_list_id'] ??= $contactListId;
                    $body['data']['channel_integration_id'] ??= $resolvedChannelIntegrationId;
                    $body['data']['name'] ??= $name;

                    try {
                        WhatsAppOutboundLog::upsertFromQontakResponse($body['data'], $broadcastId);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to save WhatsApp bulk outbound log', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'status' => $statusCode,
                    'error' => null,
                    'body' => is_array($body) ? $body : null,
                ];
            } catch (RequestException $e) {
                $statusCode = $e->getResponse()?->getStatusCode();
                $body = null;

                if ($e->hasResponse()) {
                    $body = json_decode((string) $e->getResponse()->getBody(), true);
                }

                $errorMessage = $this->extractQontakErrorMessage($body) ?? $e->getMessage();

                Log::warning('Qontak WhatsApp bulk failed', [
                    'name' => $name,
                    'template' => $templateId,
                    'contact_list_id' => $contactListId,
                    'attempt' => $attempt,
                    'status' => $statusCode,
                    'error' => $errorMessage,
                    'body' => $body,
                ]);

                if ($statusCode === 429 && $attempt < $maxAttempts) {
                    $retryAfterSeconds = $this->extractRateLimitResetSeconds($body, $bufferSeconds);

                    Log::warning('Qontak WhatsApp bulk rate-limited, retrying after delay', [
                        'name' => $name,
                        'template' => $templateId,
                        'contact_list_id' => $contactListId,
                        'attempt' => $attempt,
                        'retry_after_seconds' => $retryAfterSeconds,
                    ]);

                    $this->pauseMilliseconds($retryAfterSeconds * 1000);

                    continue;
                }

                return [
                    'success' => false,
                    'status' => $statusCode,
                    'error' => $errorMessage,
                    'body' => is_array($body) ? $body : null,
                ];
            } catch (\Throwable $e) {
                Log::error('Qontak WhatsApp bulk error', [
                    'name' => $name,
                    'template' => $templateId,
                    'contact_list_id' => $contactListId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'status' => null,
                    'error' => $e->getMessage(),
                    'body' => null,
                ];
            }
        }

        return [
            'success' => false,
            'status' => null,
            'error' => 'Bulk broadcast Qontak gagal diproses.',
            'body' => null,
        ];
    }

    /**
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    protected function sendWhatsAppWithResult(array $payload, string $toNumber, string $templateId, int|string|null $broadcastId = null): array
    {
        try {
            Log::info('Qontak WhatsApp payload', [
                'to' => $toNumber,
                'template' => $templateId,
                'payload' => $payload,
            ]);

            $response = $this->client->request('POST', 'broadcasts/whatsapp/direct', [
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            Log::info('Qontak WhatsApp sent', [
                'to' => $toNumber,
                'template' => $templateId,
                'status' => $statusCode,
                'body' => $body,
            ]);

            if (is_array($body) && is_array($body['data'] ?? null) && isset($body['data']['id'])) {
                try {
                    WhatsAppOutboundLog::upsertFromQontakResponse($body['data'], $broadcastId);
                } catch (\Throwable $e) {
                    Log::warning('Failed to save WhatsApp outbound log', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'success' => true,
                'status' => $statusCode,
                'error' => null,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            $errorMessage = $this->extractQontakErrorMessage($body) ?? $e->getMessage();

            Log::warning('Qontak WhatsApp failed', [
                'to' => $toNumber,
                'template' => $templateId,
                'status' => $statusCode,
                'error' => $errorMessage,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'status' => $statusCode,
                'error' => $errorMessage,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Qontak WhatsApp error', [
                'to' => $toNumber,
                'template' => $templateId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
                'body' => null,
            ];
        }
    }

    protected function extractQontakErrorMessage(mixed $body): ?string
    {
        if (! is_array($body)) {
            return null;
        }

        if (isset($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }

        if (isset($body['error']) && is_string($body['error'])) {
            return $body['error'];
        }

        if (isset($body['error']) && is_array($body['error'])) {
            if (isset($body['error']['message']) && is_string($body['error']['message'])) {
                return $body['error']['message'];
            }

            if (isset($body['error']['messages']) && is_array($body['error']['messages'])) {
                $firstError = $body['error']['messages'][0] ?? null;

                if (is_string($firstError)) {
                    return $firstError;
                }
            }
        }

        if (isset($body['errors']) && is_array($body['errors'])) {
            $first = $body['errors'][0] ?? null;

            if (is_string($first)) {
                return $first;
            }

            if (is_array($first)) {
                if (isset($first['message']) && is_string($first['message'])) {
                    return $first['message'];
                }

                if (isset($first['details']) && is_string($first['details'])) {
                    return $first['details'];
                }

                if (isset($first['title'], $first['details']) && is_string($first['title']) && is_string($first['details'])) {
                    return $first['title'].': '.$first['details'];
                }
            }
        }

        return null;
    }

    public function sendWithdrawalApprovedNotification(
        CustomerWalletTransaction $transaction,
        ?string $overridePhoneNumber = null,
        ?string $overrideRecipientName = null,
        ?string $channelIntegrationId = null,
    ): bool {
        return $this->sendWithdrawalNotification(
            'withdrawal_approved',
            $transaction,
            $overridePhoneNumber,
            $overrideRecipientName,
            $channelIntegrationId,
        );
    }

    public function sendWithdrawalRejectedNotification(
        CustomerWalletTransaction $transaction,
        ?string $overridePhoneNumber = null,
        ?string $overrideRecipientName = null,
        ?string $channelIntegrationId = null,
    ): bool {
        return $this->sendWithdrawalNotification(
            'withdrawal_rejected',
            $transaction,
            $overridePhoneNumber,
            $overrideRecipientName,
            $channelIntegrationId,
        );
    }

    /**
     * @return array<string, string>
     */
    public function getWithdrawalTemplateSourceTables(): array
    {
        return [
            'customer_wallet_transactions' => 'customer_wallet_transactions — Data penarikan e-wallet',
            'customers' => 'customers — Data customer',
            'computed' => 'computed — Nilai turunan / terformat',
        ];
    }

    /**
     * @return array<string, string>|array<string, array<string, string>>
     */
    public function getWithdrawalTemplateSourceColumns(?string $table = null): array
    {
        $options = [
            'customer_wallet_transactions' => [
                'id' => 'id — ID transaksi',
                'transaction_ref' => 'transaction_ref — Referensi transaksi',
                'amount' => 'amount — Nominal mentah',
                'status' => 'status — Status transaksi',
                'payment_method' => 'payment_method — Metode pembayaran',
                'notes' => 'notes — Catatan penarikan',
                'balance_before' => 'balance_before — Saldo sebelum',
                'balance_after' => 'balance_after — Saldo sesudah',
                'created_at' => 'created_at — Waktu dibuat',
                'completed_at' => 'completed_at — Waktu selesai',
            ],
            'customers' => [
                'name' => 'name — Nama customer',
                'phone' => 'phone — Nomor WhatsApp',
                'email' => 'email — Email',
                'username' => 'username — Username',
                'ref_code' => 'ref_code — Kode referral',
                'ewallet_id' => 'ewallet_id — ID e-wallet',
                'bank_name' => 'bank_name — Nama bank',
                'bank_account' => 'bank_account — Nomor rekening',
            ],
            'computed' => [
                'customer_name' => 'customer_name — Nama customer',
                'amount_formatted' => 'amount_formatted — Nominal 10.000',
                'admin_fee_formatted' => 'admin_fee_formatted — Biaya admin 1.000',
                'net_amount_formatted' => 'net_amount_formatted — Nominal diterima 9.000',
                'nominal_text' => 'nominal_text — 10.000 - 1.000 = 9.000',
                'status_label' => 'status_label — Label status',
                'created_at_human' => 'created_at_human — Tanggal dibuat',
                'completed_at_human' => 'completed_at_human — Tanggal selesai',
            ],
        ];

        if ($table !== null) {
            return $options[$table] ?? [];
        }

        return $options;
    }

    /**
     * @param  array<mixed>  $currentMappings
     * @return list<array{key: string, value: string, source_table: string, source_column: string}>
     */
    public function syncWithdrawalTemplateMappings(
        string $notificationKey,
        string $templateId,
        array $currentMappings = [],
    ): array {
        $normalizedCurrentMappings = $this->normalizeWithdrawalTemplateMappings($currentMappings);
        $templateId = trim($templateId);

        if ($templateId === '') {
            return [];
        }

        $templateParams = $this->getWhatsAppTemplateParams($templateId);

        if ($templateParams === []) {
            return $normalizedCurrentMappings;
        }

        $existingBySignature = [];

        foreach ($normalizedCurrentMappings as $index => $mapping) {
            $existingBySignature[$mapping['key'].'|'.$mapping['value']] = $mapping;
            $existingBySignature['index:'.$index] = $mapping;
        }

        $items = [];

        foreach (array_values($templateParams) as $index => $templateParam) {
            $key = trim((string) ($templateParam['key'] ?? ($index + 1)));
            $value = trim((string) ($templateParam['value'] ?? ('param_'.($index + 1))));
            $existing = $existingBySignature[$key.'|'.$value] ?? $existingBySignature['index:'.$index] ?? null;
            $defaultSource = $this->guessWithdrawalTemplateSource($notificationKey, $key, $value, $index);

            $items[] = [
                'key' => $key,
                'value' => $value,
                'source_table' => trim((string) ($existing['source_table'] ?? $defaultSource['source_table'])),
                'source_column' => trim((string) ($existing['source_column'] ?? $defaultSource['source_column'])),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{key: string, value: string, value_text: string}>
     */
    public function buildWithdrawalNotificationBodyParams(
        string $notificationKey,
        CustomerWalletTransaction $transaction,
        ?string $overrideRecipientName = null,
    ): array {
        $templateId = $this->getWithdrawalNotificationTemplateId($notificationKey);
        $resolvedTransaction = $this->hydrateWithdrawalTransaction($transaction);
        $recipientName = trim((string) ($overrideRecipientName ?? ''));

        if ($recipientName === '') {
            $recipientName = trim((string) ($resolvedTransaction->customer?->name ?? ''));
        }

        if ($templateId === '') {
            return [];
        }

        $configuredMappings = QontakWhatsAppSettings::get(
            $this->getWithdrawalNotificationParametersPath($notificationKey),
            []
        );

        $mappings = $this->syncWithdrawalTemplateMappings(
            $notificationKey,
            $templateId,
            is_array($configuredMappings) ? $configuredMappings : [],
        );

        if ($mappings === []) {
            return $this->buildBodyParametersFromValues(
                $templateId,
                $this->buildLegacyWithdrawalBodyValues($notificationKey, $resolvedTransaction, $recipientName),
            );
        }

        $items = [];

        foreach (array_values($mappings) as $index => $mapping) {
            $items[] = [
                'key' => $mapping['key'] !== '' ? $mapping['key'] : (string) ($index + 1),
                'value' => $mapping['value'] !== '' ? $mapping['value'] : 'param_'.($index + 1),
                'value_text' => $this->resolveWithdrawalSourceValue(
                    $resolvedTransaction,
                    $mapping['source_table'],
                    $mapping['source_column'],
                    $recipientName,
                ),
            ];
        }

        return $items;
    }

    public function sendWithdrawalApproved(
        string $customerName,
        string $phoneNumber,
        string $amount,
        string $adminFee = '',
        string $netAmount = '',
    ): bool {
        if (! QontakWhatsAppSettings::notificationEnabled('withdrawal_approved')) {
            Log::info('Qontak withdrawal approved notification disabled, skipping WhatsApp notification.');

            return true;
        }

        $templateId = trim((string) QontakWhatsAppSettings::get('notifications.withdrawal_approved.template_id', config('services.qontak.wd_approved_template_id')));

        if ($templateId === '' || $this->token === '' || $this->channelIntegrationId === '') {
            Log::warning('Qontak config incomplete, skipping WhatsApp notification');

            return false;
        }

        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

        if ($normalizedPhone === '') {
            Log::warning('Qontak phone number invalid, skipping WhatsApp notification', [
                'customer' => $customerName,
                'phone' => $phoneNumber,
            ]);

            return false;
        }

        $nominalText = ($adminFee !== '' && $netAmount !== '')
            ? "{$amount} - {$adminFee} = {$netAmount}"
            : $amount;

        $headerImageUrl = trim((string) QontakWhatsAppSettings::get(
            'notifications.withdrawal_approved.header_image_url',
            config('services.qontak.wd_approved_header_image_url', '')
        ));

        return $this->sendWhatsApp(
            $customerName,
            $normalizedPhone,
            $templateId,
            [$customerName, $nominalText],
            'id',
            $headerImageUrl !== '' ? $headerImageUrl : null
        );
    }

    public function sendWithdrawalRejected(
        string $customerName,
        string $phoneNumber,
        string $amount,
    ): bool {
        if (! QontakWhatsAppSettings::notificationEnabled('withdrawal_rejected')) {
            Log::info('Qontak withdrawal rejected notification disabled, skipping WhatsApp notification.');

            return true;
        }

        $templateId = trim((string) QontakWhatsAppSettings::get('notifications.withdrawal_rejected.template_id', config('services.qontak.wd_rejected_template_id')));

        if ($templateId === '' || $this->token === '' || $this->channelIntegrationId === '') {
            Log::warning('Qontak config incomplete, skipping WhatsApp rejection notification');

            return false;
        }

        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

        if ($normalizedPhone === '') {
            Log::warning('Qontak phone number invalid, skipping WhatsApp rejection notification', [
                'customer' => $customerName,
                'phone' => $phoneNumber,
            ]);

            return false;
        }

        return $this->sendWhatsApp(
            $customerName,
            $normalizedPhone,
            $templateId,
            [$customerName, $amount],
            'id',
        );
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     variable_count: int,
     *     variables: list<int>,
     *     params: list<array{key: string, value: string}>
     * }>
     */
    private function fetchWhatsAppTemplatesData(): array
    {
        if ($this->templatesCache !== null) {
            return $this->templatesCache;
        }

        try {
            $response = $this->client->request('GET', 'templates/whatsapp');
            $items = json_decode((string) $response->getBody(), true)['data'] ?? [];
            if (! is_array($items)) {
                return $this->templatesCache = [];
            }

            $result = [];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $id = (string) ($item['id'] ?? '');
                if ($id === '') {
                    continue;
                }

                $name = (string) ($item['name'] ?? $id);
                $category = (string) ($item['category'] ?? '');
                $body = (string) ($item['body'] ?? '');

                preg_match_all('/\{\{(\d+)\}\}/', $body, $matches);
                $varNums = array_unique(array_map('intval', $matches[1] ?? []));
                sort($varNums);

                $params = [];
                if (is_array($item['params'] ?? null)) {
                    foreach ($item['params'] as $param) {
                        if (is_array($param) && isset($param['key'], $param['value'])) {
                            $params[] = [
                                'key' => (string) $param['key'],
                                'value' => (string) $param['value'],
                            ];
                        }
                    }
                }

                if ($params === [] && $varNums !== []) {
                    foreach ($varNums as $num) {
                        $params[] = [
                            'key' => (string) $num,
                            'value' => 'param_'.$num,
                        ];
                    }
                }

                $result[$id] = [
                    'label' => $category !== '' ? "{$name} ({$category})" : $name,
                    'variable_count' => count($varNums),
                    'variables' => array_values($varNums),
                    'params' => $params,
                ];
            }

            return $this->templatesCache = $result;
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch WhatsApp templates from Qontak', [
                'error' => $e->getMessage(),
            ]);

            return $this->templatesCache = [];
        }
    }

    /**
     * Ambil daftar channel integration dari Qontak API.
     *
     * @return array<string, string> [id => "name — phone_number"]
     */
    public function getWhatsAppIntegrations(): array
    {
        if ($this->integrationsCache !== null) {
            return $this->buildIntegrationOptions($this->integrationsCache);
        }

        try {
            $response = $this->client->request('GET', 'integrations');
            $items = json_decode((string) $response->getBody(), true)['data'] ?? [];
            $this->integrationsCache = is_array($items) ? $items : [];
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch WhatsApp integrations from Qontak', [
                'error' => $e->getMessage(),
            ]);
            $this->integrationsCache = [];
        }

        return $this->buildIntegrationOptions($this->integrationsCache);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, string>
     */
    private function buildIntegrationOptions(array $items): array
    {
        $options = [];

        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $name = (string) ($item['name'] ?? $item['channel_account_name'] ?? $id);
            $phone = (string) ($item['channel_phone_number'] ?? '');
            $label = $phone !== '' ? "{$name} — {$phone}" : $name;
            $options[$id] = $label;
        }

        return $options;
    }

    /**
     * Mapping nama variabel Qontak ke kolom tabel customers.
     *
     * @return array<string, array{column: string, label: string}>
     */
    public function getCustomerColumnMap(): array
    {
        return [
            'full_name' => ['column' => 'customers.name',        'label' => 'Nama customer'],
            'name' => ['column' => 'customers.name',        'label' => 'Nama customer'],
            'phone' => ['column' => 'customers.phone',       'label' => 'Nomor WA customer'],
            'phone_number' => ['column' => 'customers.phone',       'label' => 'Nomor WA customer'],
            'email' => ['column' => 'customers.email',       'label' => 'Email customer'],
            'username' => ['column' => 'customers.username',    'label' => 'Username customer'],
            'ref_code' => ['column' => 'customers.ref_code',    'label' => 'Kode referral'],
            'address' => ['column' => 'customers.address',     'label' => 'Alamat customer'],
            'alamat' => ['column' => 'customers.address',     'label' => 'Alamat customer'],
            'city' => ['column' => 'customers.city_id',     'label' => 'Kota customer'],
            'province' => ['column' => 'customers.province_id', 'label' => 'Provinsi customer'],
            'nik' => ['column' => 'customers.nik',         'label' => 'NIK customer'],
        ];
    }

    /**
     * Bangun HTML tabel pemetaan variabel template → kolom customers.
     * Dapat digunakan di form schema dengan TextEntry::html().
     *
     * @param  list<array{key: string, value: string}>  $params
     */
    public function buildParamHintHtml(array $params): string
    {
        $map = $this->getCustomerColumnMap();
        $rows = '';

        foreach ($params as $index => $param) {
            $key = htmlspecialchars((string) $param['key']);
            $varName = htmlspecialchars((string) $param['value']);
            $info = $map[$param['value']] ?? null;

            if ($index === 0) {
                $source = '<span class="font-medium text-primary-600 dark:text-primary-400">'
                    .'Field "Nama" (test) / customers.name (broadcast)</span>';
            } elseif ($info !== null) {
                $col = htmlspecialchars($info['column']);
                $lbl = htmlspecialchars($info['label']);
                $source = "<span class=\"font-medium text-success-600 dark:text-success-400\">{$col}</span>"
                    ."<span class=\"text-gray-400 dark:text-gray-500 ml-1\">— {$lbl}</span>";
            } else {
                $source = '<span class="text-warning-600 dark:text-warning-400">Manual (isi di Parameter Body)</span>';
            }

            $rows .= '<tr class="border-b border-gray-100 dark:border-gray-700 last:border-0">'
                ."<td class=\"px-2 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300\">{{{$key}}}</td>"
                ."<td class=\"px-2 py-1.5 font-mono text-xs text-indigo-600 dark:text-indigo-400\">{$varName}</td>"
                ."<td class=\"px-2 py-1.5 text-xs\">{$source}</td>"
                .'</tr>';
        }

        return '<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mt-1">'
            .'<table class="min-w-full">'
            .'<thead class="bg-gray-50 dark:bg-gray-800"><tr>'
            .'<th class="px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Key</th>'
            .'<th class="px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Variabel Qontak</th>'
            .'<th class="px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Sumber Data</th>'
            .'</tr></thead>'
            ."<tbody class=\"bg-white dark:bg-gray-900\">{$rows}</tbody>"
            .'</table></div>';
    }

    /**
     * @return array<string, string>
     */
    public function getWhatsAppTemplates(): array
    {
        $data = $this->fetchWhatsAppTemplatesData();
        $options = [];

        foreach ($data as $id => $template) {
            $varCount = $template['variable_count'];
            $options[$id] = $template['label'].' ['.$varCount.' var]';
        }

        return $options;
    }

    /**
     * @return list<int>
     */
    public function getWhatsAppTemplateVariables(string $templateId): array
    {
        return $this->fetchWhatsAppTemplatesData()[$templateId]['variables'] ?? [];
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public function getWhatsAppTemplateParams(string $templateId): array
    {
        return $this->fetchWhatsAppTemplatesData()[$templateId]['params'] ?? [];
    }

    /**
     * @param  array<mixed>  $mappings
     * @return list<array{key: string, value: string, source_table: string, source_column: string}>
     */
    private function normalizeWithdrawalTemplateMappings(array $mappings): array
    {
        $items = [];

        foreach ($mappings as $mapping) {
            if (! is_array($mapping)) {
                continue;
            }

            $key = trim((string) ($mapping['key'] ?? ''));
            $value = trim((string) ($mapping['value'] ?? ''));

            if ($key === '' && $value === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
                'source_table' => trim((string) ($mapping['source_table'] ?? '')),
                'source_column' => trim((string) ($mapping['source_column'] ?? '')),
            ];
        }

        return array_values($items);
    }

    /**
     * @return array{source_table: string, source_column: string}
     */
    private function guessWithdrawalTemplateSource(
        string $notificationKey,
        string $paramKey,
        string $paramValue,
        int $index,
    ): array {
        $identifier = Str::lower($paramKey.' '.$paramValue);

        if (Str::contains($identifier, ['name', 'nama', 'full_name', 'customer_name'])) {
            return ['source_table' => 'customers', 'source_column' => 'name'];
        }

        if (Str::contains($identifier, ['phone', 'wa', 'whatsapp', 'hp', 'telp'])) {
            return ['source_table' => 'customers', 'source_column' => 'phone'];
        }

        if (Str::contains($identifier, ['email'])) {
            return ['source_table' => 'customers', 'source_column' => 'email'];
        }

        if (Str::contains($identifier, ['username'])) {
            return ['source_table' => 'customers', 'source_column' => 'username'];
        }

        if (Str::contains($identifier, ['ref', 'reference'])) {
            return ['source_table' => 'customer_wallet_transactions', 'source_column' => 'transaction_ref'];
        }

        if (Str::contains($identifier, ['admin', 'fee'])) {
            return ['source_table' => 'computed', 'source_column' => 'admin_fee_formatted'];
        }

        if (Str::contains($identifier, ['net', 'receive', 'received', 'diterima'])) {
            return ['source_table' => 'computed', 'source_column' => 'net_amount_formatted'];
        }

        if (Str::contains($identifier, ['bank_account', 'rekening'])) {
            return ['source_table' => 'customers', 'source_column' => 'bank_account'];
        }

        if (Str::contains($identifier, ['bank_name', 'bank'])) {
            return ['source_table' => 'customers', 'source_column' => 'bank_name'];
        }

        if (Str::contains($identifier, ['notes', 'catatan', 'reason', 'alasan'])) {
            return ['source_table' => 'customer_wallet_transactions', 'source_column' => 'notes'];
        }

        if (Str::contains($identifier, ['created', 'tanggal', 'date'])) {
            return ['source_table' => 'computed', 'source_column' => 'created_at_human'];
        }

        if ($index === 0) {
            return ['source_table' => 'customers', 'source_column' => 'name'];
        }

        if (($notificationKey === 'withdrawal_approved') && ($index === 1)) {
            return ['source_table' => 'computed', 'source_column' => 'nominal_text'];
        }

        if (($notificationKey === 'withdrawal_rejected') && ($index === 1)) {
            return ['source_table' => 'computed', 'source_column' => 'amount_formatted'];
        }

        if (Str::contains($identifier, ['amount', 'nominal', 'withdrawal'])) {
            return ['source_table' => 'computed', 'source_column' => 'amount_formatted'];
        }

        return [
            'source_table' => 'computed',
            'source_column' => $notificationKey === 'withdrawal_approved'
                ? 'nominal_text'
                : 'amount_formatted',
        ];
    }

    private function sendWithdrawalNotification(
        string $notificationKey,
        CustomerWalletTransaction $transaction,
        ?string $overridePhoneNumber = null,
        ?string $overrideRecipientName = null,
        ?string $channelIntegrationId = null,
    ): bool {
        if (! QontakWhatsAppSettings::notificationEnabled($notificationKey)) {
            Log::info("Qontak {$notificationKey} notification disabled, skipping WhatsApp notification.");

            return true;
        }

        $templateId = $this->getWithdrawalNotificationTemplateId($notificationKey);
        $resolvedChannelIntegrationId = trim((string) ($channelIntegrationId ?? ''));

        if (
            $templateId === ''
            || $this->token === ''
            || ($resolvedChannelIntegrationId === '' && $this->channelIntegrationId === '')
        ) {
            Log::warning("Qontak {$notificationKey} config incomplete, skipping WhatsApp notification.");

            return false;
        }

        $resolvedTransaction = $this->hydrateWithdrawalTransaction($transaction);
        $recipientName = trim((string) ($overrideRecipientName ?? ''));
        $phoneNumber = trim((string) ($overridePhoneNumber ?? ''));

        if ($recipientName === '') {
            $recipientName = trim((string) ($resolvedTransaction->customer?->name ?? ''));
        }

        if ($phoneNumber === '') {
            $phoneNumber = trim((string) ($resolvedTransaction->customer?->phone ?? ''));
        }

        if ($recipientName === '' || $phoneNumber === '') {
            Log::warning("Qontak {$notificationKey} customer contact incomplete, skipping WhatsApp notification.", [
                'transaction_id' => $resolvedTransaction->getKey(),
                'customer_id' => $resolvedTransaction->customer_id,
            ]);

            return false;
        }

        $result = $this->sendWhatsAppWithFormattedParams(
            $recipientName,
            $phoneNumber,
            $templateId,
            $this->buildWithdrawalNotificationBodyParams($notificationKey, $resolvedTransaction, $recipientName),
            'id',
            $this->buildWithdrawalNotificationHeaderParams($notificationKey),
            [],
            null,
            $resolvedChannelIntegrationId !== '' ? $resolvedChannelIntegrationId : null,
        );

        if ((bool) ($result['success'] ?? false)) {
            return true;
        }

        Log::warning("Qontak {$notificationKey} send failed.", [
            'transaction_id' => $resolvedTransaction->getKey(),
            'template_id' => $templateId,
            'error' => $result['error'] ?? null,
            'status' => $result['status'] ?? null,
        ]);

        return false;
    }

    private function hydrateWithdrawalTransaction(CustomerWalletTransaction $transaction): CustomerWalletTransaction
    {
        if ($transaction->exists) {
            $transaction->unsetRelation('customer');
            $transaction->load('customer');
        }

        return $transaction;
    }

    private function getWithdrawalNotificationTemplateId(string $notificationKey): string
    {
        return trim((string) QontakWhatsAppSettings::get(
            $this->getWithdrawalNotificationTemplatePath($notificationKey),
            match ($notificationKey) {
                'withdrawal_approved' => config('services.qontak.wd_approved_template_id'),
                'withdrawal_rejected' => config('services.qontak.wd_rejected_template_id'),
                default => '',
            }
        ));
    }

    private function getWithdrawalNotificationTemplatePath(string $notificationKey): string
    {
        return match ($notificationKey) {
            'withdrawal_approved' => 'notifications.withdrawal_approved.template_id',
            'withdrawal_rejected' => 'notifications.withdrawal_rejected.template_id',
            default => throw new \InvalidArgumentException("Unsupported withdrawal notification [{$notificationKey}]."),
        };
    }

    private function getWithdrawalNotificationParametersPath(string $notificationKey): string
    {
        return match ($notificationKey) {
            'withdrawal_approved' => 'notifications.withdrawal_approved.parameters',
            'withdrawal_rejected' => 'notifications.withdrawal_rejected.parameters',
            default => throw new \InvalidArgumentException("Unsupported withdrawal notification [{$notificationKey}]."),
        };
    }

    /**
     * @return list<array{format: string, key: string, value_text: string}>
     */
    private function buildWithdrawalNotificationHeaderParams(string $notificationKey): array
    {
        if ($notificationKey !== 'withdrawal_approved') {
            return [];
        }

        $headerImageUrl = trim((string) QontakWhatsAppSettings::get(
            'notifications.withdrawal_approved.header_image_url',
            config('services.qontak.wd_approved_header_image_url', '')
        ));

        if ($headerImageUrl === '') {
            return [];
        }

        $items = [[
            'format' => 'IMAGE',
            'key' => 'url',
            'value_text' => $headerImageUrl,
        ]];

        $fileName = $this->extractFileNameFromUrl($headerImageUrl);

        if ($fileName !== null) {
            $items[] = [
                'format' => 'IMAGE',
                'key' => 'filename',
                'value_text' => $fileName,
            ];
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private function buildLegacyWithdrawalBodyValues(
        string $notificationKey,
        CustomerWalletTransaction $transaction,
        string $recipientName,
    ): array {
        $computedValues = $this->buildWithdrawalComputedValues($transaction, $recipientName);

        return match ($notificationKey) {
            'withdrawal_approved' => [$computedValues['customer_name'], $computedValues['nominal_text']],
            'withdrawal_rejected' => [$computedValues['customer_name'], $computedValues['amount_formatted']],
            default => [],
        };
    }

    private function resolveWithdrawalSourceValue(
        CustomerWalletTransaction $transaction,
        string $sourceTable,
        string $sourceColumn,
        string $recipientName,
    ): string {
        $sourceTable = trim($sourceTable);
        $sourceColumn = trim($sourceColumn);

        if ($sourceTable === '' || $sourceColumn === '') {
            return '';
        }

        $allowedColumns = $this->getWithdrawalTemplateSourceColumns($sourceTable);

        if (! isset($allowedColumns[$sourceColumn])) {
            return '';
        }

        return match ($sourceTable) {
            'customer_wallet_transactions' => $this->stringifyWithdrawalValue($transaction->getAttribute($sourceColumn)),
            'customers' => $this->stringifyWithdrawalValue($transaction->customer?->getAttribute($sourceColumn)),
            'computed' => $this->buildWithdrawalComputedValues($transaction, $recipientName)[$sourceColumn] ?? '',
            default => '',
        };
    }

    /**
     * @return array<string, string>
     */
    private function buildWithdrawalComputedValues(
        CustomerWalletTransaction $transaction,
        string $recipientName,
    ): array {
        $rawAmount = (float) ($transaction->amount ?? 0);
        $rawAdminFee = $this->extractWithdrawalAdminFee((string) ($transaction->notes ?? ''));
        $rawNetAmount = max(0.0, $rawAmount - $rawAdminFee);
        $formattedAmount = $this->formatWithdrawalCurrency($rawAmount);
        $formattedAdminFee = $rawAdminFee > 0 ? $this->formatWithdrawalCurrency($rawAdminFee) : '';
        $formattedNetAmount = $rawAdminFee > 0 ? $this->formatWithdrawalCurrency($rawNetAmount) : '';

        return [
            'customer_name' => $recipientName !== '' ? $recipientName : trim((string) ($transaction->customer?->name ?? '')),
            'amount_formatted' => $formattedAmount,
            'admin_fee_formatted' => $formattedAdminFee,
            'net_amount_formatted' => $formattedNetAmount,
            'nominal_text' => ($formattedAdminFee !== '' && $formattedNetAmount !== '')
                ? "{$formattedAmount} - {$formattedAdminFee} = {$formattedNetAmount}"
                : $formattedAmount,
            'status_label' => Str::title(str_replace('_', ' ', trim((string) ($transaction->status ?? '')))),
            'created_at_human' => $this->formatWithdrawalDate($transaction->created_at),
            'completed_at_human' => $this->formatWithdrawalDate($transaction->completed_at),
        ];
    }

    private function stringifyWithdrawalValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $this->formatWithdrawalDate($value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    private function formatWithdrawalCurrency(float $amount): string
    {
        return number_format((int) round($amount), 0, ',', '.');
    }

    private function formatWithdrawalDate(\DateTimeInterface|string|null $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return '';
    }

    private function extractWithdrawalAdminFee(string $notes): float
    {
        if ($notes === '' || ! preg_match('/Biaya admin:\s*Rp\s*([0-9\.\,]+)/i', $notes, $matches)) {
            return 0.0;
        }

        $raw = str_replace(['.', ','], ['', '.'], trim((string) ($matches[1] ?? '0')));

        return max(0.0, (float) $raw);
    }

    /**
     * @return array{data: list<array<string, mixed>>, error: string|null}
     */
    public function getWhatsAppBroadcastLog(string $broadcastId): array
    {
        try {
            $response = $this->client->request('GET', "broadcasts/{$broadcastId}/whatsapp/log");
            $data = json_decode((string) $response->getBody(), true)['data'] ?? [];

            return [
                'data' => is_array($data) ? $data : [],
                'error' => null,
            ];
        } catch (RequestException $e) {
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            $error = $this->extractQontakErrorMessage($body)
                ?? 'Request gagal dengan status '.($e->getResponse()?->getStatusCode() ?? 'unknown');

            return [
                'data' => [],
                'error' => $error,
            ];
        } catch (\Throwable $e) {
            return [
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function normalizePhoneNumber(string $phone): string
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if ($normalizedPhone === '') {
            return '';
        }

        if (str_starts_with($normalizedPhone, '0')) {
            $normalizedPhone = '62'.substr($normalizedPhone, 1);
        } elseif (str_starts_with($normalizedPhone, '8')) {
            $normalizedPhone = '62'.$normalizedPhone;
        }

        if (! str_starts_with($normalizedPhone, '62')) {
            $normalizedPhone = '62'.$normalizedPhone;
        }

        return strlen($normalizedPhone) >= 10 ? $normalizedPhone : '';
    }

    protected function formatPhoneNumber(string $phone): string
    {
        return $this->normalizePhoneNumber($phone);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildBasePayload(
        string $toName,
        string $toNumber,
        string $templateId,
        string $languageCode = 'id',
        ?string $channelIntegrationId = null,
    ): array {
        return [
            'to_name' => $toName,
            'to_number' => $toNumber,
            'message_template_id' => $templateId,
            'channel_integration_id' => filled($channelIntegrationId) ? $channelIntegrationId : $this->channelIntegrationId,
            'language' => [
                'code' => $languageCode,
            ],
        ];
    }

    /**
     * @param  list<mixed>  $bodyValues
     * @return list<array{key: string, value: string, value_text: string}>
     */
    protected function buildBodyParametersFromValues(string $templateId, array $bodyValues): array
    {
        $items = [];
        $templateParams = $this->getWhatsAppTemplateParams($templateId);

        foreach (array_values($bodyValues) as $index => $value) {
            $valueText = trim((string) $value);

            if ($valueText === '') {
                Log::warning('Qontak: skipping empty body param', [
                    'template' => $templateId,
                    'param_index' => $index,
                ]);

                continue;
            }

            $paramDefinition = $templateParams[$index] ?? [
                'key' => (string) ($index + 1),
                'value' => 'param_'.($index + 1),
            ];

            $items[] = [
                'key' => (string) ($paramDefinition['key'] ?? ($index + 1)),
                'value' => (string) ($paramDefinition['value'] ?? ('param_'.($index + 1))),
                'value_text' => $valueText,
            ];
        }

        return $items;
    }

    /**
     * @return array{format: string, params: list<array{key: string, value: string}>}|array{}
     */
    protected function buildImageHeaderParameters(string $imageUrl): array
    {
        $imageUrl = trim($imageUrl);

        if ($imageUrl === '') {
            return [];
        }

        $params = [[
            'key' => 'url',
            'value' => $imageUrl,
        ]];

        $fileName = $this->extractFileNameFromUrl($imageUrl);

        if ($fileName !== null) {
            $params[] = [
                'key' => 'filename',
                'value' => $fileName,
            ];
        }

        return [
            'format' => 'IMAGE',
            'params' => $params,
        ];
    }

    /**
     * @param  list<array{key?: string, value?: string, value_text?: string}>  $parameters
     * @return list<array{key: string, value: string, value_text: string}>
     */
    protected function normalizeFormattedParameters(array $parameters): array
    {
        $items = [];

        foreach (array_values($parameters) as $index => $param) {
            if (! is_array($param)) {
                continue;
            }

            $key = (string) ($param['key'] ?? ($index + 1));
            $value = (string) ($param['value'] ?? ('param_'.$key));
            $valueText = trim((string) ($param['value_text'] ?? ''));

            if ($valueText === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
                'value_text' => $valueText,
            ];
        }

        return $items;
    }

    /**
     * @param  list<array{format?: string, key?: string, value?: string, value_text?: string}>  $parameters
     * @return array{format: string, params: list<array{key: string, value: string}>}|array{}
     */
    protected function normalizeFormattedHeaderParameters(array $parameters): array
    {
        $format = 'IMAGE';
        $items = [];

        foreach (array_values($parameters) as $param) {
            if (! is_array($param)) {
                continue;
            }

            $rawFormat = trim((string) ($param['format'] ?? ''));
            if ($rawFormat !== '') {
                $format = strtoupper($rawFormat);
            }

            $key = trim((string) ($param['key'] ?? ''));
            $value = trim((string) ($param['value_text'] ?? $param['value'] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        if ($items === []) {
            return [];
        }

        return [
            'format' => $format,
            'params' => $items,
        ];
    }

    protected function extractFileNameFromUrl(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $fileName = trim(basename($path));

        return $fileName !== '' && $fileName !== '.' ? $fileName : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->cleanArray($value);
            }

            if ($value === null || $value === '' || $value === []) {
                unset($data[$key]);

                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param  list<array{key?: string, value?: string}>  $parameters
     * @return list<array{key: string, value: string}>
     */
    protected function normalizeBulkParameters(array $parameters): array
    {
        $items = [];

        foreach (array_values($parameters) as $index => $param) {
            if (! is_array($param)) {
                continue;
            }

            $key = trim((string) ($param['key'] ?? ($index + 1)));
            $value = trim((string) ($param['value'] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $items;
    }

    protected function createMultipartClient(): Client
    {
        return new Client([
            'base_uri' => $this->baseUrl.'/',
            'timeout' => (int) QontakWhatsAppSettings::get('connection.timeout', config('services.qontak.timeout', 30)),
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    protected function pauseMilliseconds(int $delayMilliseconds): void
    {
        if ($delayMilliseconds <= 0) {
            return;
        }

        usleep($delayMilliseconds * 1000);
    }

    /**
     * @param  array<string, mixed>|null  $contactListData
     */
    protected function extractContactListErrorMessage(?array $contactListData): string
    {
        if ($contactListData === null) {
            return 'Contact list Qontak gagal diproses.';
        }

        $errorMessages = $contactListData['error_messages'] ?? null;

        if (is_array($errorMessages)) {
            foreach ($errorMessages as $message) {
                if (is_string($message) && trim($message) !== '') {
                    return trim($message);
                }

                if (is_array($message)) {
                    foreach ($message as $nestedMessage) {
                        if (is_string($nestedMessage) && trim($nestedMessage) !== '') {
                            return trim($nestedMessage);
                        }
                    }
                }
            }
        }

        if (($contactListData['finished_at'] ?? null) !== null) {
            return 'Qontak selesai memproses contact list, tetapi tidak ada kontak yang berhasil diimpor.';
        }

        return 'Contact list Qontak belum siap dipakai untuk bulk broadcast.';
    }

    protected function extractRateLimitResetSeconds(?array $body, int $bufferSeconds = 0): int
    {
        $resetSeconds = null;
        $messages = $body['error']['messages'] ?? null;

        if (is_array($messages)) {
            foreach ($messages as $message) {
                if (! is_string($message)) {
                    continue;
                }

                if (preg_match('/rate_limit_reset:\s*(\d+)/', $message, $matches) === 1) {
                    $resetSeconds = (int) ($matches[1] ?? 0);

                    break;
                }
            }
        }

        return max(1, ($resetSeconds ?? 5) + max(0, $bufferSeconds));
    }
}
