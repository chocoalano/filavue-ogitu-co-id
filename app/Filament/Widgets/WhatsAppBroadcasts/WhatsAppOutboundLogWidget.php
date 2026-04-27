<?php

namespace App\Filament\Widgets\WhatsAppBroadcasts;

use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppOutboundLog;
use App\Services\QontactService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class WhatsAppOutboundLogWidget extends Component
{
    use WithPagination;

    // --- Local DB filters ---
    public ?string $filterBroadcastId = null;

    public string $filterStatus = '';

    public string $filterSearch = '';

    public int $perPage = 25;

    // --- API check ---
    public string $apiQueryId = '';

    /** @var list<array<string, mixed>> */
    public array $apiLogs = [];

    public ?string $apiError = null;

    public bool $apiLoading = false;

    public bool $apiPanelOpen = false;

    // --- Row detail ---
    public ?int $selectedLogId = null;

    public bool $detailLoading = false;

    public ?string $detailError = null;

    public function showDetail(int $id): void
    {
        $this->detailError = null;
        $this->detailLoading = true;
        $this->selectedLogId = $id;

        $log = WhatsAppOutboundLog::find($id);

        if ($log?->qontak_id) {
            $result = app(QontactService::class)->getWhatsAppBroadcastLog((string) $log->qontak_id);

            if ($result['error'] !== null) {
                $this->detailError = $result['error'];
            } elseif ($result['data'] !== []) {
                $entry = null;

                // Cocokkan berdasarkan nomor telepon penerima
                if ($log->channel_phone_number) {
                    foreach ($result['data'] as $item) {
                        if (isset($item['contact_phone_number']) && $item['contact_phone_number'] === $log->channel_phone_number) {
                            $entry = $item;
                            break;
                        }
                    }
                }

                // Fallback: ambil entri pertama (broadcast langsung biasanya hanya satu)
                if ($entry === null) {
                    $entry = $result['data'][0];
                }

                try {
                    $log->updateFromBroadcastLogEntry($entry);
                } catch (\Throwable) {
                    // silent
                }
            }
        }

        $this->detailLoading = false;
    }

    public function closeDetail(): void
    {
        $this->selectedLogId = null;
        $this->detailError = null;
    }

    public function refresh(): void
    {
        // Re-render saja; getLogs() selalu query fresh dari DB
    }

    public function updatingFilterBroadcastId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSearch(): void
    {
        $this->resetPage();
    }

    public function fetchFromApi(): void
    {
        $broadcastId = trim($this->apiQueryId);

        if ($broadcastId === '') {
            $this->apiError = 'Masukkan Qontak Broadcast UUID terlebih dahulu.';
            $this->apiLogs = [];

            return;
        }

        $this->apiLoading = true;
        $this->apiError = null;
        $this->apiLogs = [];
        $this->apiPanelOpen = true;

        $result = app(QontactService::class)->getWhatsAppBroadcastLog($broadcastId);

        $this->apiLogs = $result['data'];
        $this->apiError = $result['error'];
        $this->apiLoading = false;

        // Upsert setiap entri ke tabel lokal
        if ($this->apiLogs !== []) {
            foreach ($this->apiLogs as $entry) {
                if (isset($entry['id'])) {
                    try {
                        $localBroadcastId = ($this->filterBroadcastId !== null && $this->filterBroadcastId !== '')
                            ? (int) $this->filterBroadcastId
                            : null;
                        WhatsAppOutboundLog::upsertFromQontakResponse($entry, $localBroadcastId);
                    } catch (\Throwable) {
                        // silent — log utama tetap ditampilkan
                    }
                }
            }
        }
    }

    public function getLogs(): LengthAwarePaginator
    {
        $query = WhatsAppOutboundLog::query()
            ->latest('qontak_created_at');

        if ($this->filterBroadcastId !== null && $this->filterBroadcastId !== '') {
            $query->where('broadcast_id', $this->filterBroadcastId);
        }

        if ($this->filterStatus !== '') {
            $query->where('execute_status', $this->filterStatus);
        }

        if ($this->filterSearch !== '') {
            $search = $this->filterSearch;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('channel_phone_number', 'like', "%{$search}%")
                    ->orWhereJsonContains('contact_extra->full_name', $search)
                    ->orWhere('sender_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($this->perPage);
    }

    /** @return array<string, string> */
    public function getBroadcastsProperty(): array
    {
        return WhatsAppBroadcast::query()
            ->latest()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (WhatsAppBroadcast $b): array => [
                (string) $b->id => "[#{$b->id}] {$b->title} — {$b->status}",
            ])
            ->all();
    }

    public function render(): View
    {
        return view('filament.widgets.whats-app-outbound-log-widget', [
            'logs' => $this->getLogs(),
            'selectedLog' => $this->selectedLogId
                ? WhatsAppOutboundLog::find($this->selectedLogId)
                : null,
        ]);
    }
}
