<div class="space-y-4 p-4">

    {{-- API Check Panel --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <button
            type="button"
            wire:click="$toggle('apiPanelOpen')"
            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors"
        >
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                </svg>
                Cek Log dari API Qontak
            </span>
            <svg @class(['h-4 w-4 text-gray-400 transition-transform', 'rotate-180' => $apiPanelOpen]) xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        @if ($apiPanelOpen)
            <div class="p-4 space-y-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Qontak Broadcast UUID
                        </label>
                        <input
                            type="text"
                            wire:model="apiQueryId"
                            placeholder="Contoh: b414a7ac-313e-4b97-b3b0-683019b0f7b4"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-mono text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                    <button
                        type="button"
                        wire:click="fetchFromApi"
                        wire:loading.attr="disabled"
                        wire:target="fetchFromApi"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-60 transition-colors"
                    >
                        <span wire:loading.remove wire:target="fetchFromApi">Cek dari API</span>
                        <span wire:loading wire:target="fetchFromApi">Memuat...</span>
                    </button>
                </div>

                @if ($apiError)
                    <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 px-4 py-3 text-sm text-danger-700 dark:text-danger-300">
                        {{ $apiError }}
                    </div>
                @endif

                @if (count($apiLogs) > 0)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {{ count($apiLogs) }} entri ditemukan dari API. Data telah disinkronisasi ke tabel lokal di bawah.
                        </p>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th>
                                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor WA</th>
                                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Template</th>
                                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    @foreach ($apiLogs as $entry)
                                        @php
                                            $apiStatus = strtolower((string) ($entry['execute_status'] ?? '-'));
                                            $apiIsSuccess = in_array($apiStatus, ['done', 'sent', 'success']);
                                            $apiIsFailed  = in_array($apiStatus, ['failed', 'error']);
                                            $apiIsPending = in_array($apiStatus, ['todo', 'processing', 'pending']);
                                            $apiName = $entry['contact_extra']['full_name'] ?? $entry['name'] ?? '-';
                                            $apiPhone = $entry['channel_phone_number'] ?? '-';
                                            $apiTemplate = $entry['message_template']['name'] ?? '-';
                                            $apiCreatedAt = isset($entry['created_at'])
                                                ? \Carbon\Carbon::parse($entry['created_at'])->timezone('Asia/Jakarta')->format('d M Y H:i')
                                                : '-';
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $apiName }}</td>
                                            <td class="px-3 py-2 font-mono text-gray-500 dark:text-gray-400">{{ $apiPhone }}</td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $apiTemplate }}</td>
                                            <td class="px-3 py-2">
                                                <span @class([
                                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                                    'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' => $apiIsSuccess,
                                                    'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300'   => $apiIsFailed,
                                                    'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300' => $apiIsPending,
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'               => ! $apiIsSuccess && ! $apiIsFailed && ! $apiIsPending,
                                                ])>
                                                    {{ strtoupper($apiStatus) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $apiCreatedAt }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex items-center self-end ml-auto">
            <button
                type="button"
                wire:click="refresh"
                wire:loading.attr="disabled"
                wire:target="refresh"
                title="Refresh data"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition-colors"
            >
                <svg
                    wire:loading.class="animate-spin"
                    wire:target="refresh"
                    class="h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span wire:loading.remove wire:target="refresh">Refresh</span>
                <span wire:loading wire:target="refresh">Memuat...</span>
            </button>
        </div>
    </div>
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Broadcast</label>
            <select
                wire:model.live="filterBroadcastId"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
            >
                <option value="">Semua Broadcast</option>
                @foreach ($this->broadcasts as $id => $label)
                    <option value="{{ $id }}" @selected($filterBroadcastId === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-44">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
            <select
                wire:model.live="filterStatus"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
            >
                <option value="">Semua Status</option>
                <option value="todo">Todo</option>
                <option value="done">Done</option>
                <option value="failed">Failed</option>
                <option value="processing">Processing</option>
            </select>
        </div>

        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari</label>
            <input
                type="text"
                wire:model.live.debounce.400ms="filterSearch"
                placeholder="Nama, nomor, template..."
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
            />
        </div>
    </div>

    {{-- Table --}}
    @if ($logs->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
            Belum ada log pengiriman WhatsApp tersimpan.
            Pesan akan tercatat otomatis setelah broadcast atau test kirim dijalankan.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor WA</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Template</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pesan Dikirim</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Waktu</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach ($logs as $log)
                        @php
                            $status = strtolower((string) ($log->execute_status ?? 'todo'));
                            $isSuccess = in_array($status, ['done', 'sent', 'success']);
                            $isFailed  = in_array($status, ['failed', 'error']);
                            $isPending = in_array($status, ['todo', 'processing', 'pending']);

                            $recipientName = $log->contact_extra['full_name']
                                ?? $log->name
                                ?? '-';

                            $templateName = $log->message_template['name'] ?? '-';

                            $sentCount  = (int) ($log->message_status_count['sent'] ?? 0);
                            $readCount  = (int) ($log->message_status_count['read'] ?? 0);
                            $dlvrCount  = (int) ($log->message_status_count['delivered'] ?? 0);
                            $failCount  = (int) ($log->message_status_count['failed'] ?? 0);

                            $msgSummary = collect([
                                $sentCount  > 0 ? "Terkirim: {$sentCount}"   : null,
                                $dlvrCount  > 0 ? "Diterima: {$dlvrCount}"   : null,
                                $readCount  > 0 ? "Dibaca: {$readCount}"     : null,
                                $failCount  > 0 ? "Gagal: {$failCount}"      : null,
                            ])->filter()->implode(' · ');

                            if ($msgSummary === '' && $log->message_broadcast_error && $log->message_broadcast_error !== 'n/a') {
                                $msgSummary = $log->message_broadcast_error;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $recipientName }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">
                                {{ $log->channel_phone_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                {{ $templateName }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' => $isSuccess,
                                    'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300'   => $isFailed,
                                    'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300' => $isPending && ! $isSuccess && ! $isFailed,
                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'               => ! $isSuccess && ! $isFailed && ! $isPending,
                                ])>
                                    {{ strtoupper($status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $msgSummary ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                {{ $log->qontak_created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="showDetail({{ $log->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="showDetail({{ $log->id }})"
                                    class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 disabled:opacity-50 transition-colors"
                                >
                                    <svg wire:loading.remove wire:target="showDetail({{ $log->id }})" class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg wire:loading wire:target="showDetail({{ $log->id }})" class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 12 0v4a8 8 0 00-8 8H4z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="showDetail({{ $log->id }})">Detail</span>
                                    <span wire:loading wire:target="showDetail({{ $log->id }})">Memuat...</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Total: {{ $logs->total() }} pesan</span>
                <div>{{ $logs->links() }}</div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($selectedLog)
        <div
            x-data
            x-on:keydown.escape.window="$wire.closeDetail()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50 dark:bg-black/70"
                wire:click="closeDetail"
            ></div>

            {{-- Panel --}}
            <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-900 shadow-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Detail Log Outbound</h2>
                            @if ($selectedLog->message_broadcast_plan_id && ! $detailError)
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30 px-2 py-0.5 text-xs text-success-700 dark:text-success-300">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    Fresh dari API
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">{{ $selectedLog->qontak_id }}</p>
                        @if ($detailError)
                            <p class="text-xs text-danger-600 dark:text-danger-400 mt-1">API error: {{ $detailError }} — menampilkan data lokal.</p>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Penerima & Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Penerima</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $selectedLog->contact_extra['full_name'] ?? $selectedLog->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</p>
                            @php
                                $ds = strtolower((string) ($selectedLog->execute_status ?? '-'));
                                $dsOk  = in_array($ds, ['done', 'sent', 'success']);
                                $dsFail = in_array($ds, ['failed', 'error']);
                                $dsPend = in_array($ds, ['todo', 'processing', 'pending']);
                            @endphp
                            <span @class([
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' => $dsOk,
                                'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300'   => $dsFail,
                                'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300' => $dsPend,
                                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'               => ! $dsOk && ! $dsFail && ! $dsPend,
                            ])>{{ strtoupper($ds) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nomor WA</p>
                            <p class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $selectedLog->channel_phone_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pengirim</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $selectedLog->sender_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Channel</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $selectedLog->channel_account_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Waktu Kirim</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $selectedLog->qontak_created_at?->timezone('Asia/Jakarta')->format('d M Y H:i:s') ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Template --}}
                    @if ($selectedLog->message_template)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Template</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $selectedLog->message_template['name'] ?? '-' }}
                                <span class="ml-2 text-xs font-normal text-gray-500">({{ $selectedLog->message_template['language'] ?? '' }})</span>
                            </p>
                            @if (!empty($selectedLog->message_template['body']))
                                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap leading-relaxed">{{ $selectedLog->message_template['body'] }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Status Pesan --}}
                    @if ($selectedLog->message_status_count)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Status Pengiriman</p>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach (['sent' => 'Terkirim', 'delivered' => 'Diterima', 'read' => 'Dibaca', 'failed' => 'Gagal'] as $key => $label)
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center">
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $selectedLog->message_status_count[$key] ?? 0 }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Parameters --}}
                    @if (!empty($selectedLog->parameters))
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Parameter Terkirim</p>
                            @php $bodyParams = $selectedLog->parameters['body'] ?? []; @endphp
                            @if (is_array($bodyParams) && count($bodyParams))
                                <div class="space-y-1">
                                    @foreach ($bodyParams as $key => $val)
                                        <div class="flex gap-2 text-xs">
                                            <span class="font-mono text-gray-400 dark:text-gray-500 w-6 shrink-0">{{ $key }}</span>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-400">Tidak ada parameter body.</p>
                            @endif
                        </div>
                    @endif

                    {{-- Error dari Qontak Provider --}}
                    @if ($selectedLog->message_broadcast_error && $selectedLog->message_broadcast_error !== 'n/a')
                        @php
                            $errMsg = strtolower((string) $selectedLog->message_broadcast_error);
                            $isTemplateErr  = str_contains($errMsg, 'template') || str_contains($errMsg, 'param') || str_contains($errMsg, 'variable');
                            $isPhoneErr     = str_contains($errMsg, 'phone') || str_contains($errMsg, 'number') || str_contains($errMsg, 'recipient') || str_contains($errMsg, 'invalid');
                            $isRateErr      = str_contains($errMsg, 'rate') || str_contains($errMsg, 'limit') || str_contains($errMsg, 'quota') || str_contains($errMsg, 'too many');
                            $isChannelErr   = str_contains($errMsg, 'channel') || str_contains($errMsg, 'integration') || str_contains($errMsg, 'token') || str_contains($errMsg, 'unauthorized');
                        @endphp
                        <div class="rounded-xl border border-danger-200 dark:border-danger-800 bg-danger-50 dark:bg-danger-900/20 px-4 py-4 space-y-3">
                            {{-- Label sumber error --}}
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-danger-600 dark:text-danger-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-xs font-semibold text-danger-700 dark:text-danger-400">
                                    Error dari Provider Qontak
                                </p>
                            </div>

                            {{-- Pesan error mentah --}}
                            <p class="text-xs font-mono text-danger-600 dark:text-danger-300 bg-danger-100 dark:bg-danger-900/40 rounded-lg px-3 py-2 break-all">
                                {{ $selectedLog->message_broadcast_error }}
                            </p>

                            {{-- Panduan penanganan kontekstual --}}
                            @if ($isTemplateErr)
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 px-3 py-3 space-y-2">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kemungkinan penyebab & solusi — Template/Parameter</p>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                        <li>Jumlah variabel yang dikirim tidak sesuai dengan template di Qontak (mis. template punya 2 variabel, tapi dikirim 1 atau 3).</li>
                                        <li>Template belum disetujui oleh Meta/WhatsApp Business — cek status di <span class="font-mono font-medium">Qontak Dashboard → Settings → WhatsApp Templates</span>.</li>
                                        <li>Nama template tidak cocok atau telah diubah setelah integrasi — pastikan <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">template_id</code> pada konfigurasi masih valid.</li>
                                        <li>Isi variabel mengandung karakter khusus (URL, emoji, newline) yang tidak diizinkan oleh format template Meta.</li>
                                    </ul>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <span class="font-medium">Konfigurasi:</span> Buka Qontak → <em>WhatsApp → Templates</em> → pilih template → verifikasi jumlah dan urutan variabel body. Pastikan setiap <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">@{{1}}</code>, <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">@{{2}}</code> dikirim sesuai posisinya.
                                    </p>
                                </div>
                            @elseif ($isPhoneErr)
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 px-3 py-3 space-y-2">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kemungkinan penyebab & solusi — Nomor Penerima</p>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                        <li>Nomor tidak terdaftar di WhatsApp atau sudah tidak aktif.</li>
                                        <li>Format nomor salah — harus diawali kode negara tanpa <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">+</code> (contoh: <span class="font-mono">628123456789</span>).</li>
                                        <li>Nomor diblokir oleh WhatsApp karena menerima terlalu banyak pesan massal.</li>
                                    </ul>
                                </div>
                            @elseif ($isRateErr)
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 px-3 py-3 space-y-2">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kemungkinan penyebab & solusi — Rate Limit</p>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                        <li>Terlalu banyak pesan dikirim dalam waktu singkat — Meta membatasi throughput berdasarkan tier akun.</li>
                                        <li>Tingkatkan tier akun Anda di Meta Business Manager untuk meningkatkan batas pengiriman harian.</li>
                                        <li>Jadwalkan broadcast dalam beberapa gelombang jika daftar penerima sangat besar.</li>
                                    </ul>
                                </div>
                            @elseif ($isChannelErr)
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 px-3 py-3 space-y-2">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kemungkinan penyebab & solusi — Channel / Integrasi</p>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                        <li>Token akses Qontak kedaluwarsa atau dicabut — perbarui di <span class="font-mono font-medium">Qontak → Integrations → WhatsApp</span>.</li>
                                        <li>Channel integration ID tidak cocok — verifikasi nilai <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">QONTAK_CHANNEL_INTEGRATION_ID</code> di <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">.env</code>.</li>
                                        <li>Akun WhatsApp Business di-suspend oleh Meta — periksa status di Meta Business Manager.</li>
                                    </ul>
                                </div>
                            @else
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 px-3 py-3 space-y-2">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Langkah penanganan umum</p>
                                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                        <li>Salin pesan error di atas dan cari di <span class="font-medium">Qontak Help Center</span> atau dokumentasi WhatsApp Business API untuk kode error spesifik.</li>
                                        <li>Cek log Qontak lengkap di Dashboard → <em>Outbound → Broadcast Logs</em> untuk detail tambahan.</li>
                                        <li>Pastikan template telah aktif dan disetujui, channel integration valid, serta nomor penerima terdaftar di WhatsApp.</li>
                                        <li>Jika error persisten, hubungi support Qontak dengan menyertakan <span class="font-mono">broadcast_id</span>: <span class="font-mono text-gray-500">{{ $selectedLog->qontak_id }}</span>.</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
