<?php

namespace App\Services\WhatsApp;

use App\Models\Customer;
use App\Models\WhatsAppBroadcastRecipient;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Services\QontactService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppBroadcastService
{
    public function __construct(
        protected WhatsAppBroadcastRepositoryInterface $broadcastRepository,
        protected QontactService $qontactService,
    ) {}

    public function process(int $broadcastId): void
    {
        $broadcast = $this->broadcastRepository->findById($broadcastId);

        if (! $broadcast) {
            return;
        }

        if (trim((string) $broadcast->template_id) === '') {
            $this->broadcastRepository->markFailed($broadcast, 'Template ID Qontak wajib diisi.');

            return;
        }

        $this->broadcastRepository->markProcessing($broadcast);

        if ($this->broadcastRepository->countRecipients($broadcastId) === 0) {
            $recipientPayloads = $this->buildUniqueRecipientPayloads();

            if ($recipientPayloads === []) {
                $this->broadcastRepository->markFailed(
                    $broadcast,
                    'Tidak ada nomor customer valid yang dapat diproses.'
                );

                return;
            }

            $this->broadcastRepository->replaceRecipients($broadcastId, $recipientPayloads);
        }

        $lastError = null;
        $pendingRecipients = $this->broadcastRepository->getPendingRecipients($broadcastId);

        if ($pendingRecipients->isEmpty()) {
            $stats = $this->broadcastRepository->summarizeRecipientStats($broadcastId);

            $this->broadcastRepository->markCompleted(
                $broadcast,
                $stats['total'],
                $stats['success'],
                $stats['failed'],
                null
            );

            return;
        }

        try {
            $contactListRows = $this->buildContactListRows(
                $pendingRecipients,
                (array) ($broadcast->body_params ?? [])
            );

            if ($contactListRows === []) {
                $lastError = 'Tidak ada data recipient valid untuk dibuatkan contact list bulk.';
                $this->markRecipientsFailed($pendingRecipients, $lastError);
            } else {
                $contactListFile = $this->createContactListCsv($broadcastId, $contactListRows);

                try {
                    $contactListResult = $this->qontactService->createContactListAsync(
                        $this->buildContactListName($broadcastId, (string) $broadcast->title),
                        $contactListFile
                    );
                } finally {
                    @unlink($contactListFile);
                }

                if (! (bool) ($contactListResult['success'] ?? false)) {
                    $lastError = $this->extractErrorMessage($contactListResult);
                    $this->markRecipientsFailed($pendingRecipients, $lastError);
                } else {
                    $contactListId = trim((string) ($contactListResult['body']['data']['id'] ?? ''));

                    if ($contactListId === '') {
                        $lastError = 'Qontak tidak mengembalikan contact_list_id untuk broadcast bulk.';
                        $this->markRecipientsFailed($pendingRecipients, $lastError);
                    } else {
                        $contactListReadyResult = $this->qontactService->waitUntilContactListReady($contactListId);

                        if (! (bool) ($contactListReadyResult['success'] ?? false)) {
                            $lastError = $this->extractErrorMessage($contactListReadyResult);
                            $this->markRecipientsFailed($pendingRecipients, $lastError);
                        } else {
                            $result = $this->qontactService->sendWhatsAppBulk(
                                $this->buildBroadcastName($broadcastId, (string) $broadcast->title),
                                (string) $broadcast->template_id,
                                $contactListId,
                                $this->buildBulkBodyParams((array) ($broadcast->body_params ?? [])),
                                filled($broadcast->channel_integration_id) ? (string) $broadcast->channel_integration_id : null,
                                $broadcastId,
                            );

                            if ((bool) ($result['success'] ?? false)) {
                                $this->markRecipientsSent(
                                    $pendingRecipients,
                                    $this->extractSuccessMessage($result)
                                );
                            } else {
                                $lastError = $this->extractErrorMessage($result);
                                $this->markRecipientsFailed($pendingRecipients, $lastError);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $exception) {
            $lastError = $exception->getMessage();
            $this->markRecipientsFailed($pendingRecipients, $lastError);

            Log::error('Failed to process WhatsApp bulk broadcast.', [
                'broadcast_id' => $broadcastId,
                'error' => $exception->getMessage(),
            ]);
        }

        $stats = $this->broadcastRepository->summarizeRecipientStats($broadcastId);

        $this->broadcastRepository->markCompleted(
            $broadcast,
            $stats['total'],
            $stats['success'],
            $stats['failed'],
            $lastError
        );
    }

    /**
     * @return list<array{
     *   customer_id:int,
     *   customer_name:string,
     *   phone:string,
     *   normalized_phone:string
     * }>
     */
    private function buildUniqueRecipientPayloads(): array
    {
        $customers = $this->broadcastRepository->getCustomersWithPhone();
        $uniquePhones = [];
        $recipients = [];

        foreach ($customers as $customer) {
            $rawPhone = trim((string) ($customer->phone ?? ''));
            if ($rawPhone === '') {
                continue;
            }

            $normalizedPhone = $this->qontactService->normalizePhoneNumber($rawPhone);
            if ($normalizedPhone === '' || isset($uniquePhones[$normalizedPhone])) {
                continue;
            }

            $uniquePhones[$normalizedPhone] = true;
            $customerName = trim((string) ($customer->name ?? ''));

            $recipients[] = [
                'customer_id' => (int) ($customer->id ?? 0),
                'customer_name' => $customerName !== '' ? $customerName : 'Pelanggan',
                'phone' => $rawPhone,
                'normalized_phone' => $normalizedPhone,
            ];
        }

        return $recipients;
    }

    /**
     * @param  Collection<int, WhatsAppBroadcastRecipient>  $pendingRecipients
     * @param  list<array{value: string, value_text: string}>  $bodyParamsConfig
     * @return list<array<string, string>>
     */
    private function buildContactListRows(Collection $pendingRecipients, array $bodyParamsConfig): array
    {
        $customers = $this->loadCustomersForRecipients($pendingRecipients, $bodyParamsConfig);
        $rows = [];

        /** @var WhatsAppBroadcastRecipient $recipient */
        foreach ($pendingRecipients as $recipient) {
            $customer = $customers->get((int) $recipient->customer_id);
            $fullName = trim((string) $recipient->customer_name);

            if ($fullName === '') {
                $fullName = trim((string) ($customer?->name ?? ''));
            }

            $row = [
                'full_name' => $fullName !== '' ? $fullName : 'Pelanggan',
                'phone_number' => (string) $recipient->normalized_phone,
            ];

            foreach (array_values($bodyParamsConfig) as $item) {
                $varName = trim((string) ($item['value'] ?? ''));
                $columnRef = trim((string) ($item['value_text'] ?? ''));

                if ($varName === '') {
                    continue;
                }

                $row[$varName] = $this->resolveBulkValue($customer, $columnRef, $row);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  Collection<int, WhatsAppBroadcastRecipient>  $pendingRecipients
     * @param  list<array{value: string, value_text: string}>  $bodyParamsConfig
     * @return Collection<int, Customer>
     */
    private function loadCustomersForRecipients(Collection $pendingRecipients, array $bodyParamsConfig): Collection
    {
        $customerIds = $pendingRecipients
            ->pluck('customer_id')
            ->filter(fn (mixed $customerId): bool => (int) $customerId > 0)
            ->map(fn (mixed $customerId): int => (int) $customerId)
            ->unique()
            ->values();

        if ($customerIds->isEmpty()) {
            return collect();
        }

        $columns = ['id', 'name', 'phone'];

        foreach (array_values($bodyParamsConfig) as $item) {
            $columnRef = trim((string) ($item['value_text'] ?? ''));

            if (! str_starts_with($columnRef, 'customers.')) {
                continue;
            }

            $columns[] = substr($columnRef, \strlen('customers.'));
        }

        $columns = array_values(array_unique($columns));

        return Customer::query()
            ->select($columns)
            ->whereIn('id', $customerIds->all())
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<array{value: string, value_text: string}>  $bodyParamsConfig
     * @return list<array{key: string, value: string}>
     */
    private function buildBulkBodyParams(array $bodyParamsConfig): array
    {
        $result = [];

        foreach (array_values($bodyParamsConfig) as $index => $item) {
            $varName = trim((string) ($item['value'] ?? ''));

            if ($varName === '') {
                continue;
            }

            $result[] = [
                'key' => (string) ($index + 1),
                'value' => $varName,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $baseRow
     */
    private function resolveBulkValue(?Customer $customer, string $columnRef, array $baseRow): string
    {
        if ($columnRef === '') {
            return '';
        }

        if (! str_starts_with($columnRef, 'customers.')) {
            return $columnRef;
        }

        $field = substr($columnRef, \strlen('customers.'));

        if ($field === 'name') {
            return $baseRow['full_name'] ?? '';
        }

        if ($field === 'phone') {
            return $baseRow['phone_number'] ?? '';
        }

        if ($customer === null) {
            return '';
        }

        $value = $customer->{$field} ?? null;

        return $value !== null ? trim((string) $value) : '';
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function createContactListCsv(int $broadcastId, array $rows): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), "wa_broadcast_{$broadcastId}_");

        if (! is_string($temporaryFile) || $temporaryFile === '') {
            throw new \RuntimeException('Tidak dapat membuat file sementara untuk contact list bulk.');
        }

        $csvPath = $temporaryFile.'.csv';

        if (! @rename($temporaryFile, $csvPath)) {
            @unlink($temporaryFile);

            throw new \RuntimeException('Tidak dapat menyiapkan file CSV contact list bulk.');
        }

        $handle = fopen($csvPath, 'wb');

        if ($handle === false) {
            @unlink($csvPath);

            throw new \RuntimeException('File CSV contact list bulk tidak dapat ditulis.');
        }

        $headers = array_keys($rows[0] ?? []);

        try {
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $values = array_map(
                    fn (string $header): string => (string) ($row[$header] ?? ''),
                    $headers
                );

                fputcsv($handle, $values);
            }
        } finally {
            fclose($handle);
        }

        return $csvPath;
    }

    /**
     * @param  Collection<int, WhatsAppBroadcastRecipient>  $recipients
     */
    private function markRecipientsSent(Collection $recipients, ?string $responseMessage): void
    {
        /** @var WhatsAppBroadcastRecipient $recipient */
        foreach ($recipients as $recipient) {
            $this->broadcastRepository->markRecipientSent((int) $recipient->id, $responseMessage);
        }
    }

    /**
     * @param  Collection<int, WhatsAppBroadcastRecipient>  $recipients
     */
    private function markRecipientsFailed(Collection $recipients, string $errorMessage): void
    {
        /** @var WhatsAppBroadcastRecipient $recipient */
        foreach ($recipients as $recipient) {
            $this->broadcastRepository->markRecipientFailed((int) $recipient->id, $errorMessage);
        }
    }

    private function buildBroadcastName(int $broadcastId, string $title): string
    {
        $title = trim($title);

        return $title !== '' ? $title : "WhatsApp Broadcast #{$broadcastId}";
    }

    private function buildContactListName(int $broadcastId, string $title): string
    {
        return Str::limit(
            $this->buildBroadcastName($broadcastId, $title).' Contacts '.now()->format('YmdHis'),
            120,
            ''
        );
    }

    /**
     * @param  array{error:?string}  $result
     */
    private function extractErrorMessage(array $result): string
    {
        $error = trim((string) ($result['error'] ?? ''));

        return $error !== '' ? $error : 'Gagal mengirim pesan ke nomor tujuan.';
    }

    /**
     * @param  array{status:int|null}  $result
     */
    private function extractSuccessMessage(array $result): ?string
    {
        $statusCode = $result['status'] ?? null;

        if (! \is_int($statusCode)) {
            return null;
        }

        $qontakId = trim((string) ($result['body']['data']['id'] ?? ''));

        if ($qontakId !== '') {
            return "Queued via Qontak bulk broadcast {$qontakId} (HTTP {$statusCode})";
        }

        return "Queued via Qontak bulk broadcast (HTTP {$statusCode})";
    }
}
