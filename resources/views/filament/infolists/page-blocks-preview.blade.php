@php
    use App\Filament\Resources\Pages\Schemas\PageInfolist;
    use Illuminate\Support\Str;

    /**
     * In Filament schema View components, the Eloquent record is injected
     * directly as the $record variable — not via $getRecord().
     *
     * @var \App\Models\Page $record
     */
    $pageRecord = $record;

    $decodeJsonValue = function (mixed $value, int $maxDepth = 3): mixed {
        $decodedValue = $value;
        $depth = 0;

        while (is_string($decodedValue) && $depth < $maxDepth) {
            $nextDecodedValue = json_decode($decodedValue, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $decodedValue = $nextDecodedValue;
            $depth++;
        }

        return $decodedValue;
    };

    /**
     * Normalise a block's sub-items array (features, faq, testimonials).
     * Filament Repeater also stores items as UUID-keyed objects.
     */
    $normalizeItems = function (mixed $raw) use ($decodeJsonValue): array {
        $normalizedRaw = $decodeJsonValue($raw);

        if (! is_array($normalizedRaw)) {
            return [];
        }

        $items = array_is_list($normalizedRaw) ? $normalizedRaw : array_values($normalizedRaw);

        return collect($items)
            ->map(fn (mixed $item) => $decodeJsonValue($item))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->all();
    };

    $normalizeSingleFile = function (mixed $value) use (&$normalizeSingleFile): ?string {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                $normalizedItem = $normalizeSingleFile($item);

                if (filled($normalizedItem)) {
                    return $normalizedItem;
                }
            }

            return null;
        }

        foreach (['path', 'url', 'value'] as $key) {
            if (is_string($value[$key] ?? null) && filled($value[$key])) {
                return $value[$key];
            }
        }

        return null;
    };

    $rawBlocks = $decodeJsonValue($pageRecord?->blocks);

    // Filament Builder stores blocks as UUID-keyed objects, not lists.
    // Normalise to a plain list so @foreach works with numeric indices.
    if (is_array($rawBlocks) && ! array_is_list($rawBlocks)) {
        if (array_key_exists('type', $rawBlocks) && array_key_exists('data', $rawBlocks)) {
            $rawBlocks = [$rawBlocks];
        } else {
            $rawBlocks = array_values($rawBlocks);
        }
    }

    $blocks = collect(is_array($rawBlocks) ? $rawBlocks : [])
        ->map(function (mixed $block) use ($decodeJsonValue, $normalizeItems, $normalizeSingleFile): ?array {
            $normalizedBlock = $decodeJsonValue($block);

            if (! is_array($normalizedBlock) || ! isset($normalizedBlock['type'])) {
                return null;
            }

            $blockType = (string) $normalizedBlock['type'];
            $blockData = $decodeJsonValue($normalizedBlock['data'] ?? []);

            if (! is_array($blockData)) {
                $blockData = [];
            }

            if ($blockType === 'hero') {
                $blockData['image'] = $normalizeSingleFile($blockData['image'] ?? null);
            }

            if ($blockType === 'features' || $blockType === 'faq') {
                $blockData['items'] = $normalizeItems($blockData['items'] ?? []);
            }

            if ($blockType === 'testimonials') {
                $blockData['items'] = collect($normalizeItems($blockData['items'] ?? []))
                    ->map(function (array $item) use ($normalizeSingleFile): array {
                        $item['avatar'] = $normalizeSingleFile($item['avatar'] ?? null);

                        return $item;
                    })
                    ->values()
                    ->all();
            }

            return [
                'type' => $blockType,
                'data' => $blockData,
            ];
        })
        ->filter(fn (mixed $block): bool => is_array($block) && isset($block['type'], $block['data']))
        ->values();
@endphp

<div class="space-y-3 py-1">
    @forelse ($blocks as $index => $block)
        @php
            $type = (string) ($block['type'] ?? '');
            $data = (array) ($block['data'] ?? []);

            $blockMeta = match ($type) {
                'hero'         => ['label' => 'Hero',         'icon' => '✨', 'headerBg' => 'bg-purple-50 dark:bg-purple-950/30',  'headerBorder' => 'border-purple-200 dark:border-purple-800/60',  'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',  'outerBorder' => 'border-purple-200 dark:border-purple-800/60'],
                'section_rich' => ['label' => 'Rich Text',    'icon' => '📝', 'headerBg' => 'bg-blue-50 dark:bg-blue-950/30',    'headerBorder' => 'border-blue-200 dark:border-blue-800/60',    'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',    'outerBorder' => 'border-blue-200 dark:border-blue-800/60'],
                'features'     => ['label' => 'Features',     'icon' => '⚡', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-950/30','headerBorder' => 'border-emerald-200 dark:border-emerald-800/60','badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300','outerBorder' => 'border-emerald-200 dark:border-emerald-800/60'],
                'cta'          => ['label' => 'CTA',          'icon' => '📢', 'headerBg' => 'bg-orange-50 dark:bg-orange-950/30',  'headerBorder' => 'border-orange-200 dark:border-orange-800/60',  'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300',  'outerBorder' => 'border-orange-200 dark:border-orange-800/60'],
                'faq'          => ['label' => 'FAQ',          'icon' => '❓', 'headerBg' => 'bg-amber-50 dark:bg-amber-950/30',   'headerBorder' => 'border-amber-200 dark:border-amber-800/60',   'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',   'outerBorder' => 'border-amber-200 dark:border-amber-800/60'],
                'testimonials' => ['label' => 'Testimonials', 'icon' => '💬', 'headerBg' => 'bg-pink-50 dark:bg-pink-950/30',     'headerBorder' => 'border-pink-200 dark:border-pink-800/60',     'badge' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300',     'outerBorder' => 'border-pink-200 dark:border-pink-800/60'],
                'divider'      => ['label' => 'Divider',      'icon' => '—',  'headerBg' => 'bg-gray-50 dark:bg-gray-800/40',    'headerBorder' => 'border-gray-200 dark:border-gray-700',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',        'outerBorder' => 'border-gray-200 dark:border-gray-700'],
                'spacer'       => ['label' => 'Spacer',       'icon' => '↕',  'headerBg' => 'bg-gray-50 dark:bg-gray-800/40',    'headerBorder' => 'border-gray-200 dark:border-gray-700',        'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',        'outerBorder' => 'border-gray-200 dark:border-gray-700'],
                'custom_html'  => ['label' => 'Custom HTML',  'icon' => '🔧', 'headerBg' => 'bg-red-50 dark:bg-red-950/30',      'headerBorder' => 'border-red-200 dark:border-red-800/60',      'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',      'outerBorder' => 'border-red-200 dark:border-red-800/60'],
                default        => ['label' => Str::headline($type), 'icon' => '📄', 'headerBg' => 'bg-gray-50 dark:bg-gray-800/40', 'headerBorder' => 'border-gray-200 dark:border-gray-700', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400', 'outerBorder' => 'border-gray-200 dark:border-gray-700'],
            };
        @endphp

        <div class="rounded-xl border overflow-hidden shadow-xs {{ $blockMeta['outerBorder'] }}">

            {{-- Block header --}}
            <div class="flex items-center gap-2.5 px-4 py-2.5 {{ $blockMeta['headerBg'] }} border-b {{ $blockMeta['headerBorder'] }}">
                <span class="text-sm leading-none select-none">{{ $blockMeta['icon'] }}</span>
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $blockMeta['badge'] }}">
                    {{ $blockMeta['label'] }}
                </span>
                <span class="ml-auto text-xs font-mono text-gray-400 dark:text-gray-600">
                    #{{ $index + 1 }}
                </span>
            </div>

            {{-- Block content --}}
            <div class="p-5 bg-white dark:bg-gray-900">

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- HERO --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'hero')
                    @php
                        $headline    = trim((string) ($data['headline']            ?? ''));
                        $subheadline = trim((string) ($data['subheadline']         ?? ''));
                        $priLabel    = trim((string) ($data['primary_cta_label']   ?? ''));
                        $priUrl      = trim((string) ($data['primary_cta_url']     ?? ''));
                        $secLabel    = trim((string) ($data['secondary_cta_label'] ?? ''));
                        $secUrl      = trim((string) ($data['secondary_cta_url']   ?? ''));
                        $align       = trim((string) ($data['align']               ?? 'left'));
                        $variant     = trim((string) ($data['variant']             ?? 'image-right'));
                        $imagePath   = $data['image'] ?? null;

                        if (is_array($imagePath)) {
                            $imagePath = collect($imagePath)->first(fn ($i) => is_string($i) && filled($i));
                        }

                        $imageUrl  = (is_string($imagePath) && filled($imagePath)) ? PageInfolist::resolveImageUrl($imagePath) : null;
                        $alignClass = $align === 'center' ? 'text-center items-center' : 'text-left items-start';
                    @endphp

                    <div class="flex flex-col {{ $alignClass }} gap-4">
                        @if (filled($headline))
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $headline }}
                            </h2>
                        @endif

                        @if (filled($subheadline))
                            <p class="text-base text-gray-500 dark:text-gray-400 max-w-2xl leading-relaxed">
                                {{ $subheadline }}
                            </p>
                        @endif

                        @if (filled($priLabel) || filled($secLabel))
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if (filled($priLabel))
                                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium">
                                        {{ $priLabel }}
                                        @if (filled($priUrl))
                                            <a href="{{ $priUrl }}" target="_blank" class="opacity-70 hover:opacity-100 text-xs">↗</a>
                                        @endif
                                    </span>
                                @endif

                                @if (filled($secLabel))
                                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium">
                                        {{ $secLabel }}
                                        @if (filled($secUrl))
                                            <a href="{{ $secUrl }}" target="_blank" class="opacity-70 hover:opacity-100 text-xs">↗</a>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if ($imageUrl)
                            <div class="mt-1 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 max-w-sm">
                                <img src="{{ $imageUrl }}" alt="{{ $headline ?: 'Hero Image' }}"
                                     class="w-full h-auto object-cover max-h-40">
                            </div>
                        @endif

                        {{-- Settings badges --}}
                        <div class="flex flex-wrap gap-2 pt-1 border-t border-gray-100 dark:border-gray-800 mt-1">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <span class="opacity-60">Align:</span>
                                <span class="font-medium">{{ $align }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <span class="opacity-60">Variant:</span>
                                <span class="font-medium">{{ $variant }}</span>
                            </span>
                            @if (filled($priUrl))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-mono truncate max-w-48" title="{{ $priUrl }}">
                                    Primary → {{ $priUrl }}
                                </span>
                            @endif
                            @if (filled($secUrl))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-mono truncate max-w-48" title="{{ $secUrl }}">
                                    Secondary → {{ $secUrl }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- SECTION RICH TEXT --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'section_rich')
                    @php
                        $title       = trim((string) ($data['title']        ?? ''));
                        $content     = $data['content']      ?? null;
                        $container   = trim((string) ($data['container']    ?? 'lg'));
                        $withDivider = (bool) ($data['with_divider'] ?? false);
                    @endphp

                    <div class="space-y-3">
                        @if (filled($title))
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $title }}
                            </h3>
                        @endif

                        @if (is_string($content) && filled($content))
                            <div class="prose prose-sm dark:prose-invert max-w-none
                                        prose-headings:text-gray-900 dark:prose-headings:text-white
                                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                                        prose-a:text-blue-600 dark:prose-a:text-blue-400
                                        prose-strong:text-gray-900 dark:prose-strong:text-white
                                        prose-ul:text-gray-700 dark:prose-ul:text-gray-300
                                        prose-ol:text-gray-700 dark:prose-ol:text-gray-300">
                                {!! $content !!}
                            </div>
                        @endif

                        @if ($withDivider)
                            <hr class="border-gray-200 dark:border-gray-700">
                        @endif

                        <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <span class="opacity-60">Container:</span>
                                <span class="font-medium">{{ $container }}</span>
                            </span>
                            @if ($withDivider)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                                    Divider aktif
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- FEATURES GRID --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'features')
                    @php
                        $title    = trim((string) ($data['title']    ?? ''));
                        $subtitle = trim((string) ($data['subtitle'] ?? ''));
                        $columns  = (int) ($data['columns'] ?? 3);
                        $iconed   = (bool) ($data['iconed'] ?? true);
                        $carded   = (bool) ($data['carded'] ?? true);
                        $items    = $normalizeItems($data['items'] ?? null);

                        $gridClass = match ($columns) {
                            2       => 'grid-cols-1 sm:grid-cols-2',
                            4       => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
                            default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                        };
                    @endphp

                    <div class="space-y-4">
                        @if (filled($title))
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @endif

                        @if (filled($subtitle))
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $subtitle }}</p>
                        @endif

                        @if (count($items) > 0)
                            <div class="grid {{ $gridClass }} gap-3">
                                @foreach ($items as $item)
                                    @if (is_array($item))
                                        @php
                                            $featureTitle = trim((string) ($item['title']       ?? ''));
                                            $featureIcon  = trim((string) ($item['icon']        ?? ''));
                                            $featureDesc  = trim((string) ($item['description'] ?? ''));
                                        @endphp

                                        <div @class([
                                            'rounded-lg p-3.5 border border-emerald-200 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-950/10' => $carded,
                                            'py-2' => ! $carded,
                                        ])>
                                            @if ($iconed)
                                                @if (filled($featureIcon))
                                                    <span class="block mb-2 text-xs font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 rounded px-1.5 py-0.5 w-fit">
                                                        {{ $featureIcon }}
                                                    </span>
                                                @else
                                                    <div class="w-7 h-7 rounded-md bg-emerald-100 dark:bg-emerald-900/30 mb-2"></div>
                                                @endif
                                            @endif

                                            @if (filled($featureTitle))
                                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $featureTitle }}</p>
                                            @endif

                                            @if (filled($featureDesc))
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $featureDesc }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <span class="opacity-60">Kolom:</span>
                                <span class="font-medium">{{ $columns }}</span>
                            </span>
                            @if ($iconed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Pakai Icon
                                </span>
                            @endif
                            @if ($carded)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Pakai Card
                                </span>
                            @endif
                            <span class="text-xs text-gray-400 dark:text-gray-600 self-center ml-1">
                                {{ count($items) }} item
                            </span>
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- CTA --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'cta')
                    @php
                        $title       = trim((string) ($data['title']        ?? ''));
                        $description = trim((string) ($data['description']  ?? ''));
                        $btnLabel    = trim((string) ($data['button_label'] ?? ''));
                        $btnUrl      = trim((string) ($data['button_url']   ?? ''));
                        $style       = trim((string) ($data['style']        ?? 'primary'));
                        $accent      = trim((string) ($data['accent']       ?? ''));
                    @endphp

                    <div class="rounded-lg p-5 border border-orange-200 dark:border-orange-800/40 space-y-3"
                         style="{{ $accent ? "background-color: {$accent}18;" : 'background-color: oklch(0.98 0.02 60 / 0.6);' }}">

                        @if (filled($title))
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @endif

                        @if (filled($description))
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $description }}</p>
                        @endif

                        @if (filled($btnLabel))
                            <div>
                                @if (filled($btnUrl))
                                    <a href="{{ $btnUrl }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-orange-500 text-white hover:bg-orange-600 transition-colors">
                                        {{ $btnLabel }}
                                        <span class="text-xs opacity-75">↗</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-orange-500/70 text-white">
                                        {{ $btnLabel }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2 pt-2 border-t border-orange-200/60 dark:border-orange-800/30">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-white/70 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <span class="opacity-60">Style:</span>
                                <span class="font-medium">{{ $style }}</span>
                            </span>
                            @if (filled($accent))
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs bg-white/70 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                    <span class="inline-block w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                          style="background-color: {{ $accent }}"></span>
                                    <span class="font-mono">{{ $accent }}</span>
                                </span>
                            @endif
                            @if (filled($btnUrl))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-white/70 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-mono truncate max-w-48" title="{{ $btnUrl }}">
                                    URL: {{ $btnUrl }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- FAQ --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'faq')
                    @php
                        $title = trim((string) ($data['title'] ?? ''));
                        $items = $normalizeItems($data['items'] ?? null);
                    @endphp

                    <div class="space-y-4">
                        @if (filled($title))
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @endif

                        @if (count($items) > 0)
                            <div class="space-y-2">
                                @foreach ($items as $i => $item)
                                    @if (is_array($item))
                                        @php
                                            $question = trim((string) ($item['q'] ?? ''));
                                            $answer   = trim((string) ($item['a'] ?? ''));
                                        @endphp

                                        @if (filled($question) || filled($answer))
                                            <div class="rounded-lg border border-amber-200 dark:border-amber-800/40 overflow-hidden">
                                                @if (filled($question))
                                                    <div class="flex items-start gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-950/20">
                                                        <span class="text-xs font-bold text-amber-500 dark:text-amber-400 mt-0.5 shrink-0">Q{{ $i + 1 }}</span>
                                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $question }}</p>
                                                    </div>
                                                @endif

                                                @if (filled($answer))
                                                    <div class="flex items-start gap-3 px-4 py-3 bg-white dark:bg-gray-900">
                                                        <span class="text-xs font-bold text-gray-400 dark:text-gray-600 mt-0.5 shrink-0">A</span>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $answer }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                            <span class="text-xs text-gray-400 dark:text-gray-600">{{ count($items) }} pertanyaan</span>
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- TESTIMONIALS --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'testimonials')
                    @php
                        $title = trim((string) ($data['title'] ?? ''));
                        $items = $normalizeItems($data['items'] ?? null);
                    @endphp

                    <div class="space-y-4">
                        @if (filled($title))
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @endif

                        @if (count($items) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($items as $item)
                                    @if (is_array($item))
                                        @php
                                            $name       = trim((string) ($item['name']   ?? ''));
                                            $role       = trim((string) ($item['role']   ?? ''));
                                            $quote      = trim((string) ($item['quote']  ?? ''));
                                            $avatarPath = $item['avatar'] ?? null;
                                            $avatarUrl  = (is_string($avatarPath) && filled($avatarPath))
                                                ? PageInfolist::resolveImageUrl($avatarPath)
                                                : null;
                                            $initial    = filled($name) ? strtoupper(mb_substr($name, 0, 1)) : '?';
                                        @endphp

                                        @if (filled($name) || filled($quote))
                                            <div class="flex flex-col gap-3 rounded-lg border border-pink-200 dark:border-pink-800/40 p-4 bg-pink-50/30 dark:bg-pink-950/10">
                                                @if (filled($quote))
                                                    <blockquote class="text-sm text-gray-700 dark:text-gray-300 italic leading-relaxed grow">
                                                        "{{ $quote }}"
                                                    </blockquote>
                                                @endif

                                                <div class="flex items-center gap-2.5 pt-2 border-t border-pink-200/60 dark:border-pink-800/30">
                                                    @if ($avatarUrl)
                                                        <img src="{{ $avatarUrl }}" alt="{{ $name }}"
                                                             class="w-8 h-8 rounded-full object-cover border-2 border-pink-200 dark:border-pink-700 shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-full bg-pink-200 dark:bg-pink-800 flex items-center justify-center text-xs font-bold text-pink-700 dark:text-pink-300 shrink-0">
                                                            {{ $initial }}
                                                        </div>
                                                    @endif

                                                    <div class="min-w-0">
                                                        @if (filled($name))
                                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $name }}</p>
                                                        @endif
                                                        @if (filled($role))
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $role }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                            <span class="text-xs text-gray-400 dark:text-gray-600">{{ count($items) }} testimoni</span>
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- DIVIDER --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'divider')
                    <div class="flex items-center gap-3 py-2">
                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        <span class="text-xs font-semibold text-gray-400 dark:text-gray-600 tracking-widest uppercase">
                            Divider
                        </span>
                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- SPACER --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'spacer')
                    @php
                        $size        = trim((string) ($data['size'] ?? 'md'));
                        $heightClass = match ($size) {
                            'sm'    => 'h-10',
                            'lg'    => 'h-20',
                            'xl'    => 'h-28',
                            default => 'h-14',
                        };
                        $sizeLabel   = match ($size) {
                            'sm'    => 'Small',
                            'lg'    => 'Large',
                            'xl'    => 'Extra Large',
                            default => 'Medium',
                        };
                    @endphp

                    <div class="relative {{ $heightClass }} flex items-center justify-center rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
                        <span class="px-3 py-1 text-xs text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-900 font-medium">
                            Spacer {{ $sizeLabel }}
                        </span>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- CUSTOM HTML --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if ($type === 'custom_html')
                    @php
                        $html = is_string($data['html'] ?? null) ? (string) $data['html'] : '';
                        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
                    @endphp

                    <div class="space-y-4">
                        @if (filled($html))
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">
                                    HTML Source
                                </p>
                                <pre class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed max-h-60"><code>{{ $html }}</code></pre>
                            </div>
                        @endif

                        @if (count($meta) > 0)
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">
                                    Meta Data
                                </p>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    @foreach ($meta as $key => $value)
                                        @if (is_scalar($value) || $value === null)
                                            <div class="flex gap-3 px-4 py-2.5 text-xs border-b border-gray-100 dark:border-gray-800 last:border-b-0 odd:bg-gray-50/50 dark:odd:bg-gray-800/20">
                                                <span class="font-mono font-semibold text-gray-600 dark:text-gray-400 shrink-0 w-32 truncate" title="{{ $key }}">{{ $key }}</span>
                                                <span class="text-gray-700 dark:text-gray-300">{{ (string) ($value ?? '') }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (blank($html) && count($meta) === 0)
                            <p class="text-sm text-gray-400 dark:text-gray-600 italic">Tidak ada konten.</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-14 text-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3 text-xl">
                📭
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada blok konten</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tambahkan blok melalui form Page Builder.</p>
        </div>
    @endforelse
</div>
