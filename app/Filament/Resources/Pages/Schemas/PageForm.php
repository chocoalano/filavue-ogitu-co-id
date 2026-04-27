<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema(self::formSections())
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<int, Section>
     */
    protected static function formSections(): array
    {
        return [
            self::mainSection(),
            self::seoSection(),
            self::fallbackContentSection(),
            self::pageBuilderSection(),
        ];
    }

    protected static function mainSection(): Section
    {
        return Section::make('Halaman')
            ->description('Informasi dasar halaman statis.')
            ->columns(12)
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
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
                    ->helperText('URL-friendly. Contoh: tentang-kami / kebijakan-privasi')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug((string) $state)))
                    ->unique(ignoreRecord: true)
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 8,
                    ]),

                Toggle::make('is_published')
                    ->label('Publikasikan')
                    ->default(false)
                    ->helperText('Aktifkan agar halaman tampil di publik.')
                    ->live()
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 4,
                    ]),

                Select::make('template')
                    ->label('Template')
                    ->required()
                    ->default('default')
                    ->helperText('Digunakan oleh frontend untuk memilih layout dasar.')
                    ->options([
                        'default' => 'Default',
                        'landing' => 'Landing Page',
                        'about' => 'About',
                        'faq' => 'FAQ',
                        'legal' => 'Legal (Policy/Terms)',
                        'contact' => 'Contact',
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 6,
                    ]),

                Select::make('show_on')
                    ->label('Tampilkan Di')
                    ->nullable()
                    ->default('bottom_main')
                    ->helperText('Penempatan link halaman di storefront.')
                    ->options([
                        'header_top_bar' => 'Header Top Bar',
                        'header_navbar' => 'Header Navbar',
                        'header_bottombar' => 'Header Bottom Bar',
                        'footer_main' => 'Informasi',
                        'bottom_main' => 'Bottom Main',
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 6,
                    ]),

                TextInput::make('order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Semakin kecil semakin atas.')
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 6,
                    ]),
            ])
            ->columnSpan([
                'default' => 12,
                'lg' => 8,
            ]);
    }

    protected static function seoSection(): Section
    {
        return Section::make('SEO')
            ->description('Optimasi halaman untuk mesin pencari.')
            ->columns(12)
            ->schema([
                TextInput::make('seo_title')
                    ->label('SEO Title')
                    ->maxLength(200)
                    ->helperText('Judul untuk Google (opsional).')
                    ->columnSpanFull(),

                Textarea::make('seo_description')
                    ->label('SEO Description')
                    ->rows(5)
                    ->maxLength(300)
                    ->helperText('Ringkasan 150–300 karakter.')
                    ->columnSpanFull(),

                TagsInput::make('seo_keywords')
                    ->label('SEO Keywords (opsional)')
                    ->helperText('Pisahkan dengan Enter. (Jika kolom tidak ada, hapus field ini.)')
                    ->dehydrated(false)
                    ->visible(false),
            ])
            ->columnSpan([
                'default' => 12,
                'lg' => 4,
            ]);
    }

    protected static function fallbackContentSection(): Section
    {
        return Section::make('Konten (Fallback)')
            ->description('Konten sederhana untuk template yang tidak memakai builder penuh.')
            ->schema([
                RichEditor::make('content')
                    ->label('Konten')
                    ->helperText('Opsional. Jika frontend memakai blocks (builder), konten ini bisa dikosongkan.')
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    protected static function pageBuilderSection(): Section
    {
        return Section::make('Page Builder')
            ->description('Susun halaman menggunakan blok-blok siap pakai.')
            ->schema([
                Hidden::make('blocks')
                    ->default('[]'),

                Builder::make('blocks_builder')
                    ->label('Blocks')
                    ->default([])
                    ->dehydrated(false)
                    ->addActionLabel('Tambah blok')
                    ->blockNumbers(false)
                    ->blockIcons()
                    ->blockPickerColumns(['md' => 2, 'xl' => 3])
                    ->blockPickerWidth('4xl')
                    ->collapsible()
                    ->cloneable()
                    ->live()
                    ->afterStateHydrated(function (Builder $component, $state, Get $get): void {
                        $component->state(self::normalizeBlocksState($get('blocks')));
                    })
                    ->afterStateUpdated(function ($state, Set $set): void {
                        $set('blocks', json_encode(
                            self::normalizeBlocksForStorage($state),
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ));
                    })
                    ->blocks(self::pageBuilderBlocks())
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    /**
     * @return array<int, Block>
     */
    protected static function pageBuilderBlocks(): array
    {
        return [
            Block::make('hero')
                ->label('Hero')
                ->icon('heroicon-o-sparkles')
                ->columns(12)
                ->schema([
                    TextInput::make('headline')
                        ->label('Headline')
                        ->required()
                        ->maxLength(120)
                        ->columnSpan(12),

                    Textarea::make('subheadline')
                        ->label('Subheadline')
                        ->rows(3)
                        ->maxLength(300)
                        ->columnSpan(12),

                    Grid::make(12)->schema([
                        TextInput::make('primary_cta_label')
                            ->label('Primary CTA Label')
                            ->placeholder('Contoh: Mulai Sekarang')
                            ->maxLength(40)
                            ->columnSpan(6),

                        TextInput::make('primary_cta_url')
                            ->label('Primary CTA URL')
                            ->placeholder('/register atau https://...')
                            ->maxLength(255)
                            ->columnSpan(6),

                        TextInput::make('secondary_cta_label')
                            ->label('Secondary CTA Label')
                            ->placeholder('Contoh: Pelajari')
                            ->maxLength(40)
                            ->columnSpan(6),

                        TextInput::make('secondary_cta_url')
                            ->label('Secondary CTA URL')
                            ->placeholder('/features atau https://...')
                            ->maxLength(255)
                            ->columnSpan(6),
                    ])->columnSpanFull(),

                    FileUpload::make('image')
                        ->label('Hero Image')
                        ->optimize('webp')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('pages')
                        ->visibility('public')
                        ->helperText('Opsional. Jika memakai background image, upload di sini.')
                        ->columnSpan(12),

                    Select::make('align')
                        ->label('Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                        ])
                        ->default('left')
                        ->columnSpan(6),

                    Select::make('variant')
                        ->label('Variant')
                        ->options([
                            'image-right' => 'Image Right',
                            'image-left' => 'Image Left',
                            'image-bg' => 'Image Background',
                            'text-only' => 'Text Only',
                        ])
                        ->default('image-right')
                        ->columnSpan(6),
                ]),

            Block::make('section_rich')
                ->label('Section: Rich Text')
                ->icon('heroicon-o-pencil-square')
                ->columns(12)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Section')
                        ->maxLength(120)
                        ->columnSpanFull(),

                    RichEditor::make('content')
                        ->label('Isi')
                        ->required()
                        ->columnSpanFull(),

                    Select::make('container')
                        ->label('Lebar Konten')
                        ->options([
                            'sm' => 'Small',
                            'md' => 'Medium',
                            'lg' => 'Large',
                            'xl' => 'Extra Large',
                        ])
                        ->default('lg')
                        ->columnSpan(6),

                    Toggle::make('with_divider')
                        ->label('Tampilkan Divider')
                        ->default(false)
                        ->columnSpan(6),
                ]),

            Block::make('features')
                ->label('Features Grid')
                ->icon('heroicon-o-squares-2x2')
                ->columns(12)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Textarea::make('subtitle')
                        ->label('Subjudul')
                        ->rows(2)
                        ->maxLength(250)
                        ->columnSpanFull(),

                    Select::make('columns')
                        ->label('Jumlah Kolom')
                        ->options([
                            2 => '2',
                            3 => '3',
                            4 => '4',
                        ])
                        ->default(3)
                        ->columnSpan(4),

                    Toggle::make('iconed')
                        ->label('Pakai Icon')
                        ->default(true)
                        ->columnSpan(4),

                    Toggle::make('carded')
                        ->label('Pakai Card')
                        ->default(true)
                        ->columnSpan(4),

                    Repeater::make('items')
                        ->label('Daftar Feature')
                        ->defaultItems(3)
                        ->reorderable()
                        ->columns(12)
                        ->schema([
                            TextInput::make('title')
                                ->label('Judul')
                                ->required()
                                ->maxLength(80)
                                ->columnSpan(5),

                            TextInput::make('icon')
                                ->label('Icon (opsional)')
                                ->placeholder('i-lucide-star (contoh)')
                                ->maxLength(60)
                                ->columnSpan(3),

                            Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(2)
                                ->required()
                                ->maxLength(220)
                                ->columnSpan(12),
                        ])
                        ->columnSpanFull(),
                ]),

            Block::make('cta')
                ->label('CTA')
                ->icon('heroicon-o-megaphone')
                ->columns(12)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul CTA')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->maxLength(240)
                        ->columnSpanFull(),

                    TextInput::make('button_label')
                        ->label('Label Tombol')
                        ->required()
                        ->maxLength(40)
                        ->columnSpan(6),

                    TextInput::make('button_url')
                        ->label('URL Tombol')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(6),

                    Select::make('style')
                        ->label('Style')
                        ->options([
                            'primary' => 'Primary',
                            'secondary' => 'Secondary',
                            'outline' => 'Outline',
                        ])
                        ->default('primary')
                        ->columnSpan(6),

                    ColorPicker::make('accent')
                        ->label('Accent Color (opsional)')
                        ->columnSpan(6),
                ]),

            Block::make('faq')
                ->label('FAQ')
                ->icon('heroicon-o-question-mark-circle')
                ->columns(12)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Repeater::make('items')
                        ->label('Pertanyaan')
                        ->defaultItems(5)
                        ->reorderable()
                        ->schema([
                            TextInput::make('q')
                                ->label('Pertanyaan')
                                ->required()
                                ->maxLength(150),

                            Textarea::make('a')
                                ->label('Jawaban')
                                ->rows(3)
                                ->required()
                                ->maxLength(600),
                        ])
                        ->columnSpanFull(),
                ]),

            Block::make('testimonials')
                ->label('Testimonials')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->columns(12)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Repeater::make('items')
                        ->label('Testimoni')
                        ->defaultItems(3)
                        ->reorderable()
                        ->columns(12)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->required()
                                ->maxLength(80)
                                ->columnSpan(4),

                            TextInput::make('role')
                                ->label('Jabatan/Info')
                                ->maxLength(80)
                                ->columnSpan(4),

                            FileUpload::make('avatar')
                                ->label('Avatar')
                                ->optimize('webp')
                                ->image()
                                ->disk('public')
                                ->directory('pages/testimonials')
                                ->visibility('public')
                                ->columnSpan(4),

                            Textarea::make('quote')
                                ->label('Kutipan')
                                ->rows(3)
                                ->required()
                                ->maxLength(300)
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ]),

            Block::make('divider')
                ->label('Divider')
                ->icon('heroicon-o-minus')
                ->schema([]),

            Block::make('spacer')
                ->label('Spacer')
                ->icon('heroicon-o-arrows-up-down')
                ->columns(12)
                ->schema([
                    Select::make('size')
                        ->label('Ukuran')
                        ->options([
                            'sm' => 'Small',
                            'md' => 'Medium',
                            'lg' => 'Large',
                            'xl' => 'Extra Large',
                        ])
                        ->default('md')
                        ->required()
                        ->columnSpanFull(),
                ]),

            Block::make('custom_html')
                ->label('Custom HTML')
                ->icon('heroicon-o-code-bracket')
                ->columns(12)
                ->schema([
                    Textarea::make('html')
                        ->label('HTML')
                        ->rows(8)
                        ->helperText('Gunakan dengan hati-hati. Pastikan sanitasi di frontend.')
                        ->columnSpanFull(),

                    KeyValue::make('meta')
                        ->label('Meta (opsional)')
                        ->helperText('Data tambahan untuk frontend renderer.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * Normalize `blocks` (string JSON/text) -> array builder state.
     * Aman untuk kolom blocks bertipe TEXT/LONGTEXT/JSON.
     */
    protected static function normalizeBlocksState(mixed $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        if (is_array($raw)) {
            $normalized = self::normalizeBlocksForStorage($raw);

            return self::looksLikeBuilderState($normalized) ? $normalized : [];
        }

        if (! is_string($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            $normalized = self::normalizeBlocksForStorage($decoded);

            if (self::looksLikeBuilderState($normalized)) {
                return $normalized;
            }
        }

        return [];
    }

    protected static function looksLikeBuilderState(array $value): bool
    {
        return array_is_list($value)
            && isset($value[0])
            && is_array($value[0])
            && array_key_exists('type', $value[0])
            && array_key_exists('data', $value[0]);
    }

    /**
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    protected static function normalizeBlocksForStorage(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $blocks = array_is_list($value) ? $value : array_values($value);

        return collect($blocks)
            ->map(function (mixed $block): ?array {
                if (! is_array($block)) {
                    return null;
                }

                if (! array_key_exists('type', $block)) {
                    return null;
                }

                return [
                    'type' => $blockType = (string) $block['type'],
                    'data' => self::normalizeBlockDataForStorage(
                        $blockType,
                        is_array($block['data'] ?? null) ? $block['data'] : []
                    ),
                ];
            })
            ->filter(fn (?array $block): bool => filled($block))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeBlockDataForStorage(string $blockType, array $data): array
    {
        if ($blockType === 'hero') {
            $data['image'] = self::normalizeSingleFileValue($data['image'] ?? null);
        }

        if ($blockType === 'testimonials' && is_array($data['items'] ?? null)) {
            $data['items'] = collect($data['items'])
                ->map(function (mixed $item): mixed {
                    if (! is_array($item)) {
                        return $item;
                    }

                    $item['avatar'] = self::normalizeSingleFileValue($item['avatar'] ?? null);

                    return $item;
                })
                ->all();
        }

        return $data;
    }

    protected static function normalizeSingleFileValue(mixed $value): ?string
    {
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
                $normalizedItem = self::normalizeSingleFileValue($item);

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
    }
}
