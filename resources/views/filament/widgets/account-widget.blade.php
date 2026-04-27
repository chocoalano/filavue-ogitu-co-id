@php
    $user = filament()->auth()->user();
    $userName = filament()->getUserName($user);
    $initials = collect(explode(' ', $userName))
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');

    $roleMeta = [
        'admin'   => ['label' => 'Administrator', 'icon' => 'heroicon-s-shield-check'],
        'manager' => ['label' => 'Manager', 'icon' => 'heroicon-s-briefcase'],
        'staff'   => ['label' => 'Staff', 'icon' => 'heroicon-s-user'],
    ];

    $role = strtolower($user->role ?? '');
    $badge = $roleMeta[$role] ?? ['label' => ucfirst($role ?: 'User'), 'icon' => 'heroicon-s-user-circle'];

    $memberSince = $user->created_at?->translatedFormat('d M Y') ?? 'N/A';
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700/60 dark:bg-zinc-900">

        {{-- Header — sama persis dengan app-info-widget --}}
        <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-900 text-sm font-bold text-zinc-100 shadow-sm dark:bg-zinc-100 dark:text-zinc-900">
                {{ $initials }}
                <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></span>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $userName }}
                </h3>
                <p class="truncate text-[11px] text-zinc-400 dark:text-zinc-500">
                    {{ $user->email }}
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 ring-1 ring-zinc-200/80 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700/60">
                @svg($badge['icon'], 'h-3 w-3 text-zinc-400 dark:text-zinc-500')
                {{ $badge['label'] }}
            </span>
        </div>

        {{-- Informasi Akun --}}
        <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">Informasi Akun</p>

            <div class="space-y-0.5">
                @foreach ([
                    ['label' => 'User ID', 'value' => "#{$user->id}", 'icon' => 'heroicon-s-identification'],
                    ['label' => 'Email', 'value' => $user->email, 'icon' => 'heroicon-s-envelope'],
                    ['label' => 'Bergabung', 'value' => $memberSince, 'icon' => 'heroicon-s-calendar-days'],
                ] as $row)
                    <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            @svg($row['icon'], 'h-3 w-3 text-zinc-400 dark:text-zinc-500 shrink-0')
                            {{ $row['label'] }}
                        </div>
                        <span class="max-w-36 truncate text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $row['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sesi Aktif — flex-1 agar mengisi sisa tinggi --}}
        <div class="flex-1 border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-600">Sesi Aktif</p>

            <div class="space-y-0.5">
                <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-700/60">
                            <x-heroicon-o-clock class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        Waktu Server
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                        {{ now()->format('d M Y, H:i') }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-700/60">
                            <x-heroicon-o-computer-desktop class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        Panel
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Control Panel</span>
                </div>

                <div class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-emerald-50 dark:bg-emerald-500/10">
                            <x-heroicon-s-signal class="h-3.5 w-3.5 text-emerald-500" />
                        </div>
                        Status
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Online
                    </span>
                </div>
            </div>
        </div>

        {{-- Logout action --}}
        <div class="px-5 py-3.5">
            <form action="{{ filament()->getLogoutUrl() }}" method="post">
                @csrf
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-600 transition-all hover:border-zinc-300 hover:bg-white hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
                >
                    <x-heroicon-o-arrow-left-end-on-rectangle class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" />
                    Keluar dari Sesi
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="border-t border-zinc-100 px-5 py-2.5 dark:border-zinc-800">
            <p class="text-[11px] text-zinc-400 dark:text-zinc-600">
                {{ config('app.name') }} &middot; Control Panel
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
