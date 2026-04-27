<?php

namespace App\Filament\Resources\ContentCategories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([

                // ── Kiri: Informasi Kursus ────────────────────────────────
                Section::make('Informasi Kursus')
                    ->description('Nama, slug, dan posisi kursus dalam hierarki.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kursus')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug / URL')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                            ->helperText('Otomatis dari nama kursus. Bisa diubah manual.')
                            ->columnSpanFull(),

                        Select::make('parent_id')
                            ->label('Induk (Parent Kategori)')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Kosongkan jika ini kursus utama')
                            ->helperText('Isi jika kursus ini berada di bawah kategori lain.'),

                        TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu.'),
                    ])
                    ->columns(2)
                    ->columnSpan(['default' => 12, 'lg' => 8]),

                // ── Kanan: Tampilan UI ────────────────────────────────────
                Section::make('Tampilan UI')
                    ->description('Ikon, warna aksen, dan thumbnail untuk ditampilkan di aplikasi.')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        TextInput::make('icon_key')
                            ->label('Kunci Ikon')
                            ->maxLength(100)
                            ->placeholder('campaign_rounded')
                            ->helperText('Nama ikon Material Symbols. Contoh: campaign_rounded, trending_up_rounded.'),

                        ColorPicker::make('accent_hex')
                            ->label('Warna Aksen')
                            ->hexColor()
                            ->helperText('Warna latar/aksen kartu kategori di aplikasi.'),

                        FileUpload::make('thumbnail_url')
                            ->label('Thumbnail Kursus')
                            ->optimize('webp')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('zenner-academy/thumbnails')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Format JPG/PNG/WebP • Maks 2 MB • Rasio 16:9 disarankan.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 4]),

            ])->columnSpanFull(),
        ]);
    }
}
