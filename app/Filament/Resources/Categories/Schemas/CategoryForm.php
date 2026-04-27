<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Services\Media\WebpImageUploadService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Kolom Kiri: Informasi Utama
                Grid::make(2)
                    ->schema([
                        Section::make('Informasi Kategori')
                            ->description('Atur detail nama dan deskripsi kategori Anda.')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                                    ->helperText('Nama kategori yang akan tampil di publik (Contoh: Elektronik, Pakaian Pria).'),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('URL ramah SEO. Biasanya dihasilkan otomatis dari nama.'),

                                Textarea::make('description')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->helperText('Penjelasan singkat mengenai kategori ini untuk membantu SEO.'),
                            ])->columns(2),

                        Section::make('Media')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('categories')
                                    ->optimize('webp')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->getUploadedFileNameForStorageUsing(static fn (): string => app(WebpImageUploadService::class)->generateWebpFilename())
                                    ->saveUploadedFileUsing(static fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => app(WebpImageUploadService::class)->storeForFilament($component, $file))
                                    ->helperText('Unggah ikon/foto kategori (JPG, PNG, atau WebP). File akan dioptimasi otomatis dan disimpan dalam format .webp.'),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                // Kolom Kanan: Pengaturan & Struktur
                Grid::make(1)
                    ->schema([
                        Section::make('Struktur & Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Jika nonaktif, kategori tidak akan muncul di website.'),

                                Select::make('parent_id')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->placeholder('Pilih Induk (Opsional)')
                                    ->helperText('Kosongkan jika ini adalah kategori utama (Root).'),

                                TextInput::make('sort_order')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Urutan tampilan (0 untuk pertama). Angka lebih kecil muncul lebih awal.'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ]);
    }
}
