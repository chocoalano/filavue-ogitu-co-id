@php
    $record = $this->getRecord();
    $messages = $this->getChatComments();
    $pinnedMessages = $messages->where('is_pinned', true);
@endphp

<x-filament-panels::page class="bug-report-chat-page" :full-height="true">
    @once
        <style>
            html,
            body {
                height: 100%;
            }

            body.bug-report-chat-body {
                height: 100vh !important;
                min-height: 100vh !important;
                overflow: hidden !important;
            }

            body.bug-report-chat-body .fi-body,
            body.bug-report-chat-body .fi-layout,
            body.bug-report-chat-body .fi-main-ctn,
            body.bug-report-chat-body .fi-main,
            .bug-report-chat-page,
            .bug-report-chat-page > .fi-page-header-main-ctn,
            .bug-report-chat-page .fi-page-main,
            .bug-report-chat-page .fi-page-content {
                height: 100% !important;
                min-height: 0 !important;
            }

            body.bug-report-chat-body .fi-main {
                padding: 0 !important;
                overflow: hidden !important;
            }

            .bug-report-chat-page > .fi-page-header-main-ctn,
            .bug-report-chat-page .fi-page-main,
            .bug-report-chat-page .fi-page-content {
                display: flex;
                flex-direction: column;
                padding: 0 !important;
                gap: 0 !important;
                overflow: hidden !important;
            }

            .bug-report-chat-page {
                display: flex;
                flex-direction: column;
                overflow: hidden;
                isolation: isolate;
            }

            .bug-report-chat-shell {
                display: flex;
                flex-direction: column;
                height: 100%;
                min-height: 100%;
                overflow: hidden;
                border-top: 1px solid rgb(228 228 231);
                border-bottom: 1px solid rgb(228 228 231);
                background:
                    radial-gradient(circle at top left, rgba(24, 24, 27, 0.07), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(24, 24, 27, 0.04), transparent 24%),
                    linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            }

            .dark .bug-report-chat-shell {
                border-top-color: rgb(39 39 42);
                border-bottom-color: rgb(39 39 42);
                background:
                    radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.05), transparent 24%),
                    linear-gradient(180deg, #18181b 0%, #09090b 100%);
                box-shadow: 0 24px 72px rgba(0, 0, 0, 0.32);
            }

            .bug-report-chat-glass {
                background-color: rgb(255 255 255 / 0.96);
            }

            .dark .bug-report-chat-glass {
                background-color: rgb(9 9 11 / 0.96);
            }

            @@supports ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
                .bug-report-chat-glass {
                    background-color: rgb(255 255 255 / 0.84);
                    -webkit-backdrop-filter: blur(14px);
                    backdrop-filter: blur(14px);
                }

                .dark .bug-report-chat-glass {
                    background-color: rgb(9 9 11 / 0.84);
                }
            }

            .bug-report-chat-fixed-top {
                position: relative;
                z-index: 30;
                flex-shrink: 0;
            }

            .bug-report-chat-fixed-bottom {
                position: relative;
                z-index: 30;
                flex-shrink: 0;
                padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));
            }

            .bug-report-chat-content {
                flex: 1 1 auto;
                min-height: 0;
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                overflow: hidden;
            }

            @@media (min-width: 1280px) {
                .bug-report-chat-content {
                    grid-template-columns: 17rem minmax(0, 1fr);
                }
            }

            .bug-report-chat-sidebar {
                min-height: 0;
                overflow: hidden;
            }

            .bug-report-chat-sidebar-scroll {
                height: 100%;
                min-height: 0;
                overflow-y: auto;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
            }

            .bug-report-chat-main {
                min-height: 0;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .bug-report-chat-viewport {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
                background:
                    radial-gradient(circle at top, rgba(24, 24, 27, 0.05), transparent 26%),
                    linear-gradient(180deg, #f4f4f5, #ffffff);
            }

            .dark .bug-report-chat-viewport {
                background:
                    radial-gradient(circle at top, rgba(255, 255, 255, 0.07), transparent 26%),
                    linear-gradient(180deg, rgba(24, 24, 27, 0.94), rgba(9, 9, 11, 0.98));
            }

            .bug-report-chat-header-grid {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                align-items: center;
                gap: 0.75rem;
                min-width: 0;
            }

            .bug-report-chat-header-title {
                min-width: 0;
            }

            .bug-report-chat-header-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.5rem;
                min-width: 0;
            }

            .bug-report-chat-empty {
                min-height: 12rem;
            }

            @@media (min-width: 640px) {
                .bug-report-chat-empty {
                    min-height: 18rem;
                }
            }

            @@media (max-width: 639px) {
                .bug-report-chat-header-actions .bug-report-chat-desktop-action {
                    display: none !important;
                }
            }
        </style>
    @endonce

    <section
        x-data="{
            init() {
                document.body.classList.add('bug-report-chat-body')
                this.$nextTick(() => this.scrollToBottom())
            },
            destroy() {
                document.body.classList.remove('bug-report-chat-body')
            },
            scrollToBottom() {
                if (! this.$refs.viewport) return
                this.$refs.viewport.scrollTop = this.$refs.viewport.scrollHeight
            },
        }"
        x-on:bug-chat-scroll.window="$nextTick(() => scrollToBottom())"
        x-on:livewire:navigating.window="document.body.classList.remove('bug-report-chat-body')"
        class="bug-report-chat-shell"
    >
        <header
            role="banner"
            class="bug-report-chat-fixed-top bug-report-chat-glass border-b border-zinc-200/80 px-3 py-2.5 shadow-[0_1px_0_rgba(255,255,255,0.7)] sm:px-4 sm:py-3 lg:px-5 dark:border-zinc-800/80 dark:shadow-[0_1px_0_rgba(255,255,255,0.03)]"
        >
            <div class="bug-report-chat-header-grid">
                <a
                    href="{{ $this->getResourceUrl('view') }}"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-900 sm:h-9 sm:w-9 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                >
                    <x-heroicon-m-arrow-left class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                </a>

                <div class="bug-report-chat-header-title">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center rounded-full border border-zinc-300 bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-700 sm:px-2.5 sm:text-[11px] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            Bug #{{ $record->id }}
                        </span>

                        <span class="inline-flex items-center rounded-full border border-zinc-300 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-700 sm:px-2.5 sm:text-[11px] dark:border-zinc-700 dark:bg-zinc-900/80 dark:text-zinc-300">
                            {{ $record->status?->getLabel() ?? '-' }}
                        </span>

                        <span class="hidden sm:inline-flex items-center rounded-full border border-zinc-300 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900/80 dark:text-zinc-300">
                            {{ $messages->count() }} pesan
                        </span>
                    </div>

                    <p class="mt-0.5 truncate text-xs font-semibold tracking-tight text-zinc-950 sm:mt-1 sm:text-sm dark:text-zinc-50">
                        {{ $record->title }}
                    </p>
                </div>

                <div class="bug-report-chat-header-actions">
                    <a
                        href="{{ $this->getResourceUrl('view') }}"
                        class="bug-report-chat-desktop-action inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    >
                        <x-heroicon-o-eye class="h-3.5 w-3.5" />
                        Detail
                    </a>

                    <a
                        href="{{ $this->getResourceUrl('edit') }}"
                        class="bug-report-chat-desktop-action inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    >
                        <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                        Ubah
                    </a>

                    <div class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        <span class="hidden sm:inline">Live</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="bug-report-chat-content">
            <aside class="bug-report-chat-sidebar bug-report-chat-glass hidden border-r border-zinc-200/80 xl:block dark:border-zinc-800/80">
                <div class="bug-report-chat-sidebar-scroll px-4 py-4">
                    <div class="space-y-3">
                        <div class="rounded-[1.5rem] border border-zinc-200 bg-white/85 px-4 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/80 dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">Pelapor</p>
                            <p class="mt-1.5 text-sm font-medium leading-5 text-zinc-900 dark:text-zinc-100">
                                {{ $this->reporterDisplayName() }}
                            </p>
                        </div>

                        <div class="rounded-[1.5rem] border border-zinc-200 bg-white/85 px-4 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/80 dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">Penanggung Jawab</p>
                            <p class="mt-1.5 text-sm font-medium leading-5 text-zinc-900 dark:text-zinc-100">
                                {{ $this->assigneeDisplayName() }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-[1.5rem] border border-zinc-200 bg-white/80 px-4 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-none">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">Status</p>
                                <p class="mt-1.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $record->status?->getLabel() ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-[1.5rem] border border-zinc-200 bg-white/80 px-4 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-none">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">Pesan</p>
                                <p class="mt-1.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $messages->count() }}
                                </p>
                            </div>
                        </div>

                        @if (filled($record->page_url))
                            <div class="rounded-[1.5rem] border border-zinc-200 bg-white/80 px-4 py-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-none">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">Halaman Terkait</p>
                                <a
                                    href="{{ $record->page_url }}"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="mt-2 inline-flex items-start gap-2 text-sm leading-5 text-zinc-700 transition hover:text-zinc-950 dark:text-zinc-200 dark:hover:text-white"
                                >
                                    <x-heroicon-o-link class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
                                    <span class="break-all">{{ $record->page_url }}</span>
                                </a>
                            </div>
                        @endif

                        @if ($pinnedMessages->isNotEmpty())
                            <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50/80 px-4 py-3 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/30">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-s-bookmark class="h-3.5 w-3.5 text-amber-500 dark:text-amber-400" />
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-400">
                                        Pesan Dipin
                                    </p>
                                </div>

                                <ul class="mt-2 space-y-2">
                                    @foreach ($pinnedMessages as $pinned)
                                        <li>
                                            <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                                                {{ $this->senderName($pinned) }}
                                            </p>
                                            <p class="mt-0.5 line-clamp-2 text-xs leading-5 text-amber-900/80 dark:text-amber-200/80">
                                                {{ $pinned->body }}
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! $this->supportsTwoWayChat())
                            <div class="rounded-[1.5rem] border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-5 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100">
                                <p class="font-semibold">Chat dua arah belum tersedia.</p>
                                <p class="mt-1.5 text-amber-700 dark:text-amber-200/90">
                                    Fitur percakapan penuh baru aktif jika pelapor berasal dari user internal
                                    yang dapat login ke panel.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

            <div class="bug-report-chat-main">
                <div
                    x-ref="viewport"
                    wire:poll.10s="pollMessages"
                    class="bug-report-chat-viewport px-3 py-3 sm:px-4 sm:py-4 lg:px-5"
                >
                    <div wire:key="bug-chat-thread-{{ $messages->count() }}-{{ $messages->last()?->id ?? 0 }}" class="mx-auto flex w-full max-w-5xl flex-col gap-3 sm:gap-4">
                        @forelse ($messages as $comment)
                            @php
                                $isOwnMessage = $this->isOwnMessage($comment);
                                $role = $this->senderRole($comment);
                            @endphp

                            <div
                                wire:key="bug-chat-message-{{ $comment->id }}"
                                @class([
                                    'group flex w-full',
                                    'justify-end' => $isOwnMessage,
                                    'justify-start' => ! $isOwnMessage,
                                ])
                            >
                                <div
                                    @class([
                                        'flex w-full max-w-[94%] items-end gap-2 sm:max-w-[88%] sm:gap-3 lg:max-w-[78%]',
                                        'ml-auto flex-row-reverse' => $isOwnMessage,
                                        'mr-auto flex-row' => ! $isOwnMessage,
                                    ])
                                >
                                    <div
                                        @class([
                                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-[10px] font-semibold shadow-sm sm:h-9 sm:w-9 sm:text-[11px]',
                                            'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-200 dark:bg-zinc-100 dark:text-zinc-900' => $isOwnMessage,
                                            'border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100' => ! $isOwnMessage,
                                        ])
                                    >
                                        {{ $this->senderInitials($comment) }}
                                    </div>

                                    <div
                                        @class([
                                            'min-w-0 flex flex-1 flex-col',
                                            'items-end' => $isOwnMessage,
                                            'items-start text-left' => ! $isOwnMessage,
                                        ])
                                    >
                                        <div
                                            @class([
                                                'w-fit max-w-full overflow-hidden rounded-[1.25rem] px-3 py-2.5 shadow-sm ring-1 sm:rounded-[1.5rem] sm:px-4 sm:py-3',
                                                'text-right bg-zinc-900 text-white ring-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:ring-zinc-200' => $isOwnMessage,
                                                'text-left bg-white text-zinc-900 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-100 dark:ring-zinc-800' => ! $isOwnMessage,
                                                'ring-amber-300 dark:ring-amber-700/60' => $comment->is_pinned,
                                            ])
                                        >
                                            <div
                                                @class([
                                                    'mb-1.5 flex w-full flex-wrap items-center gap-x-2 gap-y-1 text-[10px] sm:text-[11px]',
                                                    'justify-end text-right' => $isOwnMessage,
                                                    'justify-start text-left' => ! $isOwnMessage,
                                                ])
                                            >
                                                <span
                                                    @class([
                                                        'inline-flex items-center rounded-full px-2 py-0.5 font-semibold tracking-wide',
                                                        'bg-white/15 text-white dark:bg-zinc-900/10 dark:text-zinc-900' => $isOwnMessage,
                                                        'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' => ! $isOwnMessage,
                                                    ])
                                                >
                                                    {{ $role }}
                                                </span>

                                                <span
                                                    @class([
                                                        'truncate font-semibold',
                                                        'text-zinc-100 dark:text-zinc-900' => $isOwnMessage,
                                                        'text-zinc-900 dark:text-zinc-100' => ! $isOwnMessage,
                                                    ])
                                                >
                                                    {{ $this->senderName($comment) }}
                                                </span>

                                                <span
                                                    @class([
                                                        'shrink-0',
                                                        'text-zinc-300 dark:text-zinc-600' => $isOwnMessage,
                                                        'text-zinc-500 dark:text-zinc-400' => ! $isOwnMessage,
                                                    ])
                                                >
                                                    {{ $comment->created_at?->translatedFormat('d M Y, H:i') }}
                                                </span>
                                            </div>

                                            <div
                                                @class([
                                                    'w-full text-sm leading-6',
                                                    'text-right' => $isOwnMessage,
                                                    'text-left' => ! $isOwnMessage,
                                                    'text-zinc-100 dark:text-zinc-900' => $isOwnMessage,
                                                    'text-zinc-700 dark:text-zinc-100' => ! $isOwnMessage,
                                                ])
                                            >
                                                {{ $comment->body }}
                                            </div>
                                        </div>

                                        @if ($this->canPinMessage())
                                            <button
                                                type="button"
                                                wire:click="togglePinComment({{ $comment->id }})"
                                                title="{{ $comment->is_pinned ? 'Unpin pesan ini' : 'Pin pesan ini' }}"
                                                @class([
                                                    'mt-1.5 inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1',
                                                    'opacity-0 group-hover:opacity-100',
                                                    'self-end' => $isOwnMessage,
                                                    'self-start' => ! $isOwnMessage,
                                                    'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-700/50 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-950/60' => $comment->is_pinned,
                                                    'border-zinc-200 bg-white/80 text-zinc-500 hover:border-zinc-300 hover:bg-zinc-100 hover:text-zinc-700 dark:border-zinc-700/80 dark:bg-zinc-900/70 dark:text-zinc-400 dark:hover:bg-zinc-800' => ! $comment->is_pinned,
                                                ])
                                            >
                                                @if ($comment->is_pinned)
                                                    <x-heroicon-s-bookmark class="h-3 w-3 text-amber-500 dark:text-amber-400" />
                                                    <span>Unpin</span>
                                                @else
                                                    <x-heroicon-o-bookmark class="h-3 w-3" />
                                                    <span>Pin</span>
                                                @endif
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bug-report-chat-empty flex items-center justify-center">
                                <div class="max-w-sm rounded-[1.5rem] border border-dashed border-zinc-300 bg-white/90 px-5 py-7 text-center shadow-sm sm:max-w-md sm:rounded-[1.8rem] sm:px-6 sm:py-9 dark:border-zinc-700 dark:bg-zinc-900/70 dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-300 bg-zinc-100 text-zinc-700 sm:h-14 sm:w-14 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 sm:h-7 sm:w-7" />
                                    </div>

                                    <p class="mt-3 text-sm font-semibold text-zinc-900 sm:mt-4 dark:text-zinc-100">
                                        Belum ada percakapan.
                                    </p>

                                    <p class="mt-1.5 text-xs leading-5 text-zinc-600 sm:mt-2 sm:text-sm sm:leading-6 dark:text-zinc-400">
                                        Mulai diskusi untuk memperjelas konteks bug, progres penanganan, atau
                                        konfirmasi hasil perbaikan.
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <footer class="bug-report-chat-fixed-bottom bug-report-chat-glass border-t border-zinc-200/80 px-3 pt-2.5 sm:px-4 sm:pt-3 lg:px-5 dark:border-zinc-800/80 mb-15">
                    <div class="mx-auto w-full max-w-5xl mb-2">
                        @if ($this->canSendMessage())
                            <form wire:submit="sendMessage" class="space-y-1.5 sm:space-y-2">
                                <label for="bug-chat-message" class="sr-only">Pesan Chat</label>

                                <div class="relative rounded-full border border-zinc-200 bg-white shadow-sm transition focus-within:border-zinc-400 focus-within:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:focus-within:border-zinc-600">
                                    <input
                                        id="bug-chat-message"
                                        type="text"
                                        wire:model.defer="messageBody"
                                        wire:loading.attr="disabled"
                                        wire:target="sendMessage"
                                        inputmode="text"
                                        enterkeyhint="send"
                                        autocomplete="off"
                                        autocapitalize="sentences"
                                        spellcheck="true"
                                        placeholder="Tulis pesan..."
                                        class="block h-11 w-full rounded-full border-0 bg-transparent pl-4 pr-14 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-0 sm:h-12 sm:pl-5 sm:pr-16 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                    />

                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="sendMessage"
                                        class="absolute right-1.5 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-zinc-900 text-white transition hover:scale-[1.03] hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60 sm:right-2 sm:h-9 sm:w-9 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                                    >
                                        <x-heroicon-m-paper-airplane class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    </button>
                                </div>

                                <div class="flex items-start justify-between gap-3 px-1">
                                    <p class="text-[11px] leading-5 text-zinc-500 sm:text-xs dark:text-zinc-400">
                                        {{ $this->composerHelperText() }}
                                    </p>

                                    <p class="hidden shrink-0 text-[11px] text-zinc-400 sm:block dark:text-zinc-500">
                                        Enter untuk kirim
                                    </p>
                                </div>

                                @error('messageBody')
                                    <p class="px-1 text-sm font-medium text-danger-600 dark:text-danger-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </form>
                        @else
                            <div class="rounded-[1.5rem] border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm leading-5 text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/70 dark:text-zinc-300">
                                {{ $this->composerHelperText() }}
                            </div>
                        @endif
                    </div>
                </footer>
            </div>
        </div>
    </section>
</x-filament-panels::page>
