<?php

namespace App\Filament\Resources\Contents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([

                // ── Kiri: Editor Konten ───────────────────────────────────
                Section::make('Konten Modul')
                    ->description('Informasi dasar dan isi materi.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Tabs::make('content-tabs')
                            ->contained(false)
                            ->persistTab()
                            ->id('zenner-content-tabs')
                            ->tabs([

                                Tab::make('Informasi Dasar')
                                    ->icon('heroicon-o-information-circle')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Modul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                                if (($get('slug') ?? '') !== Str::slug($old ?? '')) {
                                                    return;
                                                }

                                                $set('slug', Str::slug($state ?? ''));
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari Judul. Bisa diubah untuk URL yang lebih baik.'),

                                        Select::make('category_id')
                                            ->label('Kursus (Kategori)')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->helperText('Kursus tempat modul ini berada.'),

                                        TextInput::make('sort_order')
                                            ->label('Urutan Modul')
                                            ->numeric()
                                            ->integer()
                                            ->default(0)
                                            ->minValue(0)
                                            ->helperText('Urutan tampil dalam kursus. Angka lebih kecil = lebih awal.'),

                                        Select::make('content_type')
                                            ->label('Tipe Konten')
                                            ->options([
                                                'video' => '🎬 Video',
                                                'article' => '📄 Artikel',
                                                'pdf' => '📎 PDF',
                                                'xlsx' => '📄 XLSX',
                                            ])
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->helperText('Jenis materi yang disajikan.'),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Published',
                                                'archived' => 'Archived',
                                            ])
                                            ->native(false)
                                            ->required()
                                            ->default('draft')
                                            ->helperText('Draft = belum tampil • Published = live • Archived = disembunyikan.'),
                                    ]),

                                Tab::make('Isi Konten')
                                    ->icon('heroicon-o-pencil-square')
                                    ->schema([
                                        RichEditor::make('content')
                                            ->label('Isi Materi')
                                            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                            ->fileAttachmentsDirectory('zenner-academy/content-attachments')
                                            ->fileAttachmentsVisibility('private')
                                            ->resizableImages()
                                            ->extraInputAttributes(['style' => 'min-height: 400px;'])
                                            ->columnSpanFull(),
                                    ]),

                                Tab::make('Media & Lampiran')
                                    ->icon('heroicon-o-paper-clip')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('vlink')
                                            ->label('URL Video')
                                            ->url()
                                            ->placeholder('https://youtube.com/watch?v=... atau https://vimeo.com/...')
                                            ->helperText('Link video eksternal (YouTube, Vimeo, Google Drive, dll).')
                                            ->visible(fn (Get $get) => $get('content_type') === 'video')
                                            ->columnSpanFull(),

                                        TextInput::make('duration_sec')
                                            ->label('Durasi Video (detik)')
                                            ->numeric()
                                            ->integer()
                                            ->minValue(0)
                                            ->placeholder('0')
                                            ->helperText('Durasi total video dalam detik. Digunakan untuk progress bar.')
                                            ->visible(fn (Get $get) => $get('content_type') === 'video'),

                                        FileUpload::make('thumbnail_url')
                                            ->label('Thumbnail Modul')
                                            ->optimize('webp')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('zenner-academy/module-thumbnails')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->helperText('JPG/PNG/WebP • Maks 2 MB • Rasio 16:9.')
                                            ->visible(fn (Get $get) => in_array($get('content_type'), ['video', 'article'], true)),

                                        FileUpload::make('file')
                                            ->label('File Lampiran')
                                            ->directory('zenner-academy/attachments')
                                            ->optimize('webp')
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                                'image/*',
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                            ])
                                            ->maxSize(51200)
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('PDF/Gambar/Video/XLSX • Maks 50 MB.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 12]),

            ])->columnSpanFull(),
        ]);
    }
}
