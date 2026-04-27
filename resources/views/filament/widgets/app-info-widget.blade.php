@php
    $appData = $this->getAppData();
    $stats   = $this->getSystemStats();

    $envBadge = [
        'production' => ['bg' => 'bg-emerald-500/10 dark:bg-emerald-500/15', 'text' => 'text-emerald-600 dark:text-emerald-400', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-500/20'],
        'local'      => ['bg' => 'bg-amber-500/10 dark:bg-amber-500/15', 'text' => 'text-amber-600 dark:text-amber-400', 'dot' => 'bg-amber-500', 'ring' => 'ring-amber-500/20'],
        'staging'    => ['bg' => 'bg-sky-500/10 dark:bg-sky-500/15', 'text' => 'text-sky-600 dark:text-sky-400', 'dot' => 'bg-sky-500', 'ring' => 'ring-sky-500/20'],
        'testing'    => ['bg' => 'bg-violet-500/10 dark:bg-violet-500/15', 'text' => 'text-violet-600 dark:text-violet-400', 'dot' => 'bg-violet-500', 'ring' => 'ring-violet-500/20'],
    ];

    $env   = $appData['app_env'];
    $badge = $envBadge[$env] ?? $envBadge['local'];

    $formatBytes = function (int $bytes): string {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1024, 0) . ' KB';
    };

    $memPct   = $stats['memory_percent'];
    $memColor = match (true) {
        $memPct >= 80 => ['bar' => 'bg-red-500', 'text' => 'text-red-600 dark:text-red-400'],
        $memPct >= 60 => ['bar' => 'bg-amber-500', 'text' => 'text-amber-600 dark:text-amber-400'],
        default       => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400'],
    };

    $driverIcons = [
        'redis'    => ['icon' => 'heroicon-s-bolt', 'color' => 'text-red-400'],
        'database' => ['icon' => 'heroicon-s-circle-stack', 'color' => 'text-blue-400'],
        'file'     => ['icon' => 'heroicon-s-folder', 'color' => 'text-zinc-400'],
        'array'    => ['icon' => 'heroicon-s-squares-2x2', 'color' => 'text-zinc-400'],
        'sync'     => ['icon' => 'heroicon-s-arrows-right-left', 'color' => 'text-zinc-400'],
    ];
@endphp

<x-filament-widgets::widget class="fi-app-info-widget">
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700/60 dark:bg-zinc-900">

        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-900 shadow-sm dark:bg-zinc-100">
                <x-heroicon-s-square-3-stack-3d class="h-4 w-4 text-zinc-100 dark:text-zinc-900" />
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $appData['app_name'] }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500">
                    {{ $appData['hostname'] }} &middot; {{ $appData['os'] }}
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['ring'] }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                {{ ucfirst($env) }}
            </span>
        </div>

        {{-- System status --}}
        <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">Status Sistem</p>

            <div class="grid grid-cols-2 gap-2">
                {{-- Database --}}
                <div class="flex items-center gap-2.5 rounded-lg border px-3 py-2.5 {{ $stats['db_status'] ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/40 dark:bg-emerald-900/10' : 'border-red-200 bg-red-50 dark:border-red-800/40 dark:bg-red-900/10' }}">
                    <span class="relative flex h-2 w-2 shrink-0">
                        @if ($stats['db_status'])
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </span>
                    <div>
                        <p class="text-[10px] font-medium {{ $stats['db_status'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                            Database
                        </p>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                            {{ $stats['db_driver'] }} &middot; {{ $stats['db_status'] ? 'Terhubung' : 'Gagal' }}
                        </p>
                    </div>
                </div>

                {{-- Redis --}}
                <div class="flex items-center gap-2.5 rounded-lg border px-3 py-2.5 {{ $stats['redis_status'] ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/40 dark:bg-emerald-900/10' : 'border-red-200 bg-red-50 dark:border-red-800/40 dark:bg-red-900/10' }}">
                    <span class="relative flex h-2 w-2 shrink-0">
                        @if ($stats['redis_status'])
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </span>
                    <div>
                        <p class="text-[10px] font-medium {{ $stats['redis_status'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                            Redis
                        </p>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                            {{ $stats['redis_status'] ? 'Terhubung' : 'Gagal' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Memory bar --}}
            <div class="mt-3">
                <div class="mb-1.5 flex items-center justify-between">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                        Memory &mdash; {{ $formatBytes($stats['memory_used']) }} / {{ $formatBytes($stats['memory_limit']) }}
                    </span>
                    <span class="text-[11px] font-semibold {{ $memColor['text'] }}">{{ $memPct }}%</span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full transition-all duration-500 {{ $memColor['bar'] }}" style="width: {{ $memPct }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-zinc-400 dark:text-zinc-600">
                    Peak: {{ $formatBytes($stats['memory_peak']) }}
                </p>
            </div>
        </div>

        {{-- App info --}}
        <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">Info Aplikasi</p>

            <div class="space-y-0.5">
                @foreach ([
                    // ['label' => 'Laravel', 'value' => 'v' . app()->version()],
                    // ['label' => 'PHP', 'value' => 'v' . PHP_VERSION],
                    // ['label' => 'Octane', 'value' => ucfirst($appData['octane_server'] ?? 'N/A')],
                    ['label' => 'Server Time', 'value' => now()->format('d M Y, H:i')],
                ] as $item)
                    <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</span>
                        <span class="font-mono text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Drivers --}}
        <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">Driver</p>

            <div class="space-y-0.5">
                @foreach ([
                    ['label' => 'Cache', 'value' => $appData['cache_driver']],
                    ['label' => 'Queue', 'value' => $appData['queue_driver']],
                    ['label' => 'Session', 'value' => $appData['session_driver']],
                ] as $driver)
                    @php $dIcon = $driverIcons[$driver['value']] ?? ['icon' => 'heroicon-s-circle', 'color' => 'text-zinc-400']; @endphp
                    <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $driver['label'] }}</span>
                        <span class="inline-flex items-center gap-1.5">
                            @svg($dIcon['icon'], 'h-3 w-3 ' . $dIcon['color'])
                            <span class="font-mono text-xs font-medium capitalize text-zinc-700 dark:text-zinc-300">{{ $driver['value'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 px-5 py-3.5">
            <button
                wire:click="clearCache"
                wire:loading.attr="disabled"
                wire:target="clearCache"
                class="group flex flex-1 items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-600 transition-all hover:border-zinc-300 hover:bg-white hover:text-zinc-900 disabled:cursor-wait disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
            >
                <span wire:loading.remove wire:target="clearCache" class="flex items-center gap-1.5">
                    <x-heroicon-o-trash class="h-3.5 w-3.5 text-zinc-400 group-hover:text-red-400" />
                    Bersihkan Cache
                </span>
                <span wire:loading wire:target="clearCache" class="flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5 animate-spin" />
                    Membersihkan...
                </span>
            </button>

            <button
                wire:click="recache"
                wire:loading.attr="disabled"
                wire:target="recache"
                class="group flex flex-1 items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-600 transition-all hover:border-zinc-300 hover:bg-white hover:text-zinc-900 disabled:cursor-wait disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
            >
                <span wire:loading.remove wire:target="recache" class="flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5 text-zinc-400 group-hover:text-emerald-400" />
                    Recache
                </span>
                <span wire:loading wire:target="recache" class="flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5 animate-spin" />
                    Memproses...
                </span>
            </button>
        </div>

        {{-- Footer --}}
        <div class="border-t border-zinc-100 px-5 py-2.5 dark:border-zinc-800">
            <p class="text-[11px] text-zinc-400 dark:text-zinc-600">
                <span class="font-medium text-zinc-500 dark:text-zinc-500">PT. Your Vibe Indonesia</span>
                &copy; {{ date('Y') }} All rights reserved.
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
