<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Services\Media\WebpImageUploadService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::formComponents());
    }

    /**
     * @return array<int, Grid>
     */
    private static function formComponents(): array
    {
        return [
            Grid::make(12)
                ->schema([
                    self::articleSection(),
                    self::seoSection(),
                    self::contentSection(),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function articleSection(): Section
    {
        return Section::make('Artikel')
            ->description('Informasi dasar artikel dan publikasi.')
            ->columns(12)
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (blank($get('slug')) && filled($state)) {
                            $set('slug', Str::slug((string) $state));
                        }

                        if (blank($get('seo_title')) && filled($state)) {
                            $set('seo_title', (string) $state);
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(200)
                    ->helperText('URL-friendly. Contoh: judul-artikel-anda')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                    ->unique(ignoreRecord: true)
                    ->columnSpan(['default' => 12, 'lg' => 8]),

                Toggle::make('is_published')
                    ->label('Publikasikan')
                    ->default(false)
                    ->helperText('Aktifkan untuk mempublikasikan artikel.')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && blank($get('published_at'))) {
                            $set('published_at', now());
                        }

                        if (! $state) {
                            $set('published_at', null);
                        }
                    })
                    ->columnSpan(['default' => 12, 'lg' => 4]),

                DateTimePicker::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->seconds(false)
                    ->helperText('Diisi otomatis saat dipublikasikan, bisa diubah untuk penjadwalan.')
                    ->visible(fn (callable $get) => (bool) $get('is_published'))
                    ->required(fn (callable $get) => (bool) $get('is_published'))
                    ->columnSpanFull(),

                FileUpload::make('image_banner')
                    ->label('Image Banner')
                    ->image()
                    ->optimize('webp')
                    ->imageEditor()
                    ->directory('articles/banners')
                    ->disk('public')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->getUploadedFileNameForStorageUsing(
                        static function (TemporaryUploadedFile $file, callable $get): string {
                            $slug = (string) ($get('slug') ?? 'artikel');

                            return app(WebpImageUploadService::class)->generateSlugWebpFilename("{$slug}-banner");
                        }
                    )
                    ->saveUploadedFileUsing(
                        static fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => app(WebpImageUploadService::class)->storeForFilament($component, $file)
                    )
                    ->helperText('Banner utama untuk halaman index dan detail artikel. Otomatis dioptimasi ke .webp.')
                    ->columnSpanFull(),
            ])
            ->columnSpan(['default' => 12, 'lg' => 8]);
    }

    private static function seoSection(): Section
    {
        return Section::make('SEO')
            ->description('Optimasi tampilan di mesin pencari.')
            ->schema([
                TextInput::make('seo_title')
                    ->label('SEO Title')
                    ->maxLength(200)
                    ->helperText('Judul untuk Google (opsional).'),

                Textarea::make('seo_description')
                    ->label('SEO Description')
                    ->rows(5)
                    ->maxLength(300)
                    ->helperText('Ringkasan 150–300 karakter untuk snippet.')
                    ->columnSpanFull(),
            ])
            ->columnSpan(['default' => 12, 'lg' => 4]);
    }

    private static function contentSection(): Section
    {
        return Section::make('Konten')
            ->description('Gunakan Builder untuk menyusun blok-blok konten artikel.')
            ->schema([
                self::contentsRepeater(),
            ])
            ->columnSpanFull();
    }

    private static function contentsRepeater(): Repeater
    {
        return Repeater::make('contents')
            ->label('Bagian Konten')
            ->relationship('contents')
            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                $data['content'] = self::normalizeBuilderState($data['content'] ?? null);

                return $data;
            })
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                $data['content'] = self::encodeBuilderStateForStorage($data['content'] ?? []);

                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                $data['content'] = self::encodeBuilderStateForStorage($data['content'] ?? []);

                return $data;
            })
            ->defaultItems(1)
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => filled($state['tags'] ?? null)
                ? 'Konten • '.implode(', ', array_slice((array) $state['tags'], 0, 3))
                : 'Konten'
            )
            ->schema([
                self::contentBuilder(),
                TagsInput::make('tags')
                    ->label('Tags')
                    ->helperText('Pisahkan dengan Enter. Contoh: seo, bisnis, teknologi')
                    ->columnSpanFull(),
            ])
            ->columns(12)
            ->columnSpanFull();
    }

    private static function contentBuilder(): Builder
    {
        return Builder::make('content')
            ->label('Body (Builder)')
            ->required()
            ->default([])
            ->addActionLabel('Tambah blok')
            ->blockNumbers(false)
            ->blockIcons()
            ->collapsible()
            ->cloneable()
            ->blocks(self::contentBuilderBlocks())
            ->columnSpanFull();
    }

    /**
     * @return array<int, Block>
     */
    private static function contentBuilderBlocks(): array
    {
        return [
            Block::make('heading')
                ->label('Heading')
                ->columns(2)
                ->schema([
                    TextInput::make('content')
                        ->label('Teks')
                        ->required()
                        ->maxLength(200),

                    Select::make('level')
                        ->label('Level')
                        ->options([
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ])
                        ->default('h2')
                        ->required(),
                ]),

            Block::make('rich_text')
                ->label('Rich Text')
                ->schema([
                    RichEditor::make('content')
                        ->label('Isi')
                        ->required(),
                ]),

            Block::make('image')
                ->label('Gambar')
                ->columns(2)
                ->schema([
                    FileUpload::make('url')
                        ->label('File Gambar')
                        ->image()
                        ->optimize('webp')
                        ->imageEditor()
                        ->directory('articles')
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->getUploadedFileNameForStorageUsing(
                            static function (TemporaryUploadedFile $file, callable $get): string {
                                $slug = (string) ($get('/slug') ?? '');

                                return app(WebpImageUploadService::class)->generateSlugWebpFilename($slug);
                            }
                        )
                        ->saveUploadedFileUsing(
                            static fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => app(WebpImageUploadService::class)->storeForFilament($component, $file)
                        )
                        ->helperText('Upload gambar artikel (JPG, PNG, WebP). Otomatis dioptimasi ke .webp.')
                        ->required(),

                    TextInput::make('alt')
                        ->label('Alt Text')
                        ->maxLength(150)
                        ->required(),
                ]),

            Block::make('quote')
                ->label('Quote')
                ->schema([
                    Textarea::make('quote')
                        ->label('Kutipan')
                        ->rows(4)
                        ->required(),

                    TextInput::make('cite')
                        ->label('Sumber (opsional)')
                        ->maxLength(100),
                ]),

            Block::make('divider')
                ->label('Divider')
                ->schema([]),
        ];
    }

    /**
     * Pastikan Builder selalu menerima array:
     * - Jika DB berisi JSON builder string -> decode
     * - Jika DB berisi HTML/string lama -> bungkus jadi 1 block rich_text
     * - Jika kosong -> []
     */
    protected static function normalizeBuilderState(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        // Sudah array (mungkin karena cast/atau sudah normal)
        if (is_array($value)) {
            if (self::looksLikeBuilderState($value)) {
                return self::normalizeBuilderBlocks($value);
            }

            return self::normalizeBuilderBlocks([
                self::makeRichTextBlock(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''),
            ]);
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded) && self::looksLikeBuilderState($decoded)) {
            return self::normalizeBuilderBlocks($decoded);
        }

        // Fallback: anggap HTML/plain
        return self::normalizeBuilderBlocks([
            self::makeRichTextBlock($value),
        ]);
    }

    protected static function looksLikeBuilderState(array $value): bool
    {
        return array_is_list($value)
            && isset($value[0])
            && is_array($value[0])
            && array_key_exists('type', $value[0])
            && array_key_exists('data', $value[0]);
    }

    protected static function makeRichTextBlock(string $html): array
    {
        return [
            'type' => 'rich_text',
            'data' => [
                'content' => $html,
            ],
        ];
    }

    protected static function encodeBuilderStateForStorage(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return '[]';
        }

        $normalizedValue = self::looksLikeBuilderState($value)
            ? self::normalizeBuilderBlocks($value)
            : array_values($value);

        return json_encode(
            $normalizedValue,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '[]';
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeBuilderBlocks(array $blocks): array
    {
        $normalizedBlocks = [];

        foreach (array_values($blocks) as $block) {
            $normalizedBlock = self::normalizeSingleBuilderBlock($block);

            if ($normalizedBlock !== null) {
                $normalizedBlocks[] = $normalizedBlock;
            }
        }

        return $normalizedBlocks;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function normalizeSingleBuilderBlock(mixed $block): ?array
    {
        if (! is_array($block)) {
            return null;
        }

        $type = (string) ($block['type'] ?? '');

        if ($type === '') {
            return null;
        }

        $data = is_array($block['data'] ?? null) ? $block['data'] : [];

        if ($type === 'rich_text') {
            $data['content'] = self::normalizeRichTextState($data['content'] ?? '');
        }

        $block['type'] = $type;
        $block['data'] = $data;

        return $block;
    }

    /**
     * @return string|array<string, mixed>
     */
    protected static function normalizeRichTextState(mixed $value): string|array
    {
        if (is_array($value)) {
            return self::normalizeTipTapDocument($value);
        }

        if (! is_string($value)) {
            return '';
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $value;
        }

        return self::normalizeTipTapDocument($decoded);
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $document
     * @return array<string, mixed>
     */
    protected static function normalizeTipTapDocument(array $document): array
    {
        if (array_is_list($document)) {
            return [
                'type' => 'doc',
                'content' => self::normalizeTipTapNodeList($document),
            ];
        }

        if (($document['type'] ?? null) === 'doc') {
            return [
                'type' => 'doc',
                'content' => is_array($document['content'] ?? null)
                    ? self::normalizeTipTapNodeList($document['content'])
                    : [],
            ];
        }

        if (is_string($document['type'] ?? null)) {
            $normalizedNode = self::normalizeTipTapNode($document);

            return [
                'type' => 'doc',
                'content' => $normalizedNode ? [$normalizedNode] : [],
            ];
        }

        if (is_array($document['content'] ?? null)) {
            return [
                'type' => 'doc',
                'content' => self::normalizeTipTapNodeList($document['content']),
            ];
        }

        return [
            'type' => 'doc',
            'content' => [],
        ];
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeTipTapNodeList(array $nodes): array
    {
        $normalizedNodes = [];

        foreach ($nodes as $node) {
            $normalizedNode = self::normalizeTipTapNode($node);

            if ($normalizedNode !== null) {
                $normalizedNodes[] = $normalizedNode;
            }
        }

        return $normalizedNodes;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function normalizeTipTapNode(mixed $node): ?array
    {
        if (! is_array($node)) {
            return null;
        }

        $type = trim((string) ($node['type'] ?? ''));

        if ($type === '') {
            return null;
        }

        if (isset($node['content'])) {
            if (is_array($node['content'])) {
                $node['content'] = self::normalizeTipTapNodeList($node['content']);
            } else {
                unset($node['content']);
            }
        }

        if ($type === 'heading') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $level = (int) ($attrs['level'] ?? 2);
            $attrs['level'] = max(1, min(6, $level));
            $node['attrs'] = $attrs;
        }

        $node['type'] = $type;

        return $node;
    }
}
