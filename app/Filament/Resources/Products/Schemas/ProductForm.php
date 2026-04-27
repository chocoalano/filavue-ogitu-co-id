<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\CommodityCodes\CommodityCodeResource;
use App\Models\CommodityCode;
use App\Services\Media\WebpImageUploadService;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function richTextEditorToolbar(): array
    {
        return [
            ['bold', 'italic', 'underline', 'strike'],
            ['h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
            ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
            ['undo', 'redo']
        ];
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product Data')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Informasi Utama')
                            ->schema([
                                Section::make('Identitas Produk')
                                    ->columns(12)
                                    ->schema([
                                        Select::make('commodity_code')
                                            ->label('Kode Komoditas')
                                            ->noOptionsMessage('Tidak ada kode komoditas yang ditemukan.')
                                            ->options(fn () => CommodityCode::query()
                                                ->get()
                                                ->mapWithKeys(fn ($item) => [
                                                    $item->code => "{$item->name} ({$item->code})"
                                                ])
                                            )
                                            ->searchable() // Sangat disarankan jika data bertambah banyak
                                            ->preload()    // Memuat data di awal agar transisi smooth
                                            ->required()
                                            ->helperText('Tentukan kode berdasarkan jenis barang untuk validasi pengiriman.')
                                            ->suffixAction(
                                                Action::make('view_codes')
                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                    ->tooltip('Lihat Daftar Kode')
                                                    ->color('gray')
                                                    ->url(fn () => CommodityCodeResource::getUrl('index'))
                                                    ->openUrlInNewTab() // Agar inputan di form saat ini tidak hilang
                                            )
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        TextInput::make('sku')
                                            ->label('SKU')
                                            ->required()
                                            ->placeholder('PRD-001-RED')
                                            ->helperText('Kode unik internal produk. Klik tombol generate untuk membuat SKU otomatis dari nama produk.')
                                            ->suffixAction(
                                                Action::make('generateSku')
                                                    ->label('Generate SKU')
                                                    ->icon(Heroicon::OutlinedArrowPath)
                                                    ->action(function (Get $get, Set $set): void {
                                                        $set('sku', self::generateSku($get('name')));
                                                    })
                                            )
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->placeholder('contoh-produk')
                                            ->helperText('Digunakan pada URL produk. Klik generate untuk membuat slug yang SEO-friendly dari nama produk.')
                                            ->suffixAction(
                                                Action::make('generateSlug')
                                                    ->label('Generate Slug')
                                                    ->icon(Heroicon::OutlinedArrowPath)
                                                    ->action(function (Get $get, Set $set): void {
                                                        $set('slug', self::generateSlug($get('name')));
                                                    })
                                            )
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        TextInput::make('name')
                                            ->label('Nama Produk')
                                            ->required()
                                            ->helperText('Nama produk yang ditampilkan ke customer. Isi dulu nama produk sebelum generate SKU/Slug.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        TextInput::make('brand')
                                            ->label('Merek')
                                            ->helperText('Isi merek/brand produk. Kosongkan jika produk tanpa merek khusus.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        Select::make('categories')
                                            ->label('Kategori / Varian')
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Pilih satu atau lebih kategori yang merepresentasikan varian atau pengelompokan produk.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->required()
                                            ->helperText('Aktifkan agar produk tampil di katalog. Nonaktifkan jika produk disembunyikan sementara.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 4,
                                            ]),
                                    ]),
                                Section::make('Deskripsi')
                                    ->schema([
                                        RichEditor::make('short_desc')
                                            ->label('Deskripsi Singkat')
                                            ->toolbarButtons(self::richTextEditorToolbar())
                                            ->extraInputAttributes([
                                                'style' => 'min-height: 18rem;',
                                            ])
                                            ->helperText('Gunakan untuk ringkasan produk. Area editor dibuat lebih tinggi agar lebih nyaman saat mengetik.')
                                            ->columnSpanFull(),
                                        RichEditor::make('long_desc')
                                            ->label('Deskripsi Lengkap')
                                            ->toolbarButtons(self::richTextEditorToolbar())
                                            ->extraInputAttributes([
                                                'style' => 'min-height: 28rem;',
                                            ])
                                            ->helperText('Gunakan untuk detail lengkap produk (fitur, manfaat, cara pakai, catatan penting).')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Harga & Insentif')
                            ->schema([
                                Section::make('Harga & Stok')
                                    ->columns(12)
                                    ->schema([
                                        TextInput::make('base_price')
                                            ->label('Harga Dasar')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->helperText('Masukkan harga jual dasar produk dalam Rupiah tanpa tanda titik/koma.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('discount')
                                            ->label('Diskon')
                                            ->required()
                                            ->numeric()
                                            ->default(0.0)
                                            ->prefix('Rp')
                                            ->helperText('Nominal diskon produk sesuai data tabel existing.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('currency')
                                            ->label('Mata Uang')
                                            ->required()
                                            ->default('IDR')
                                            ->helperText('Kode mata uang format ISO, contoh: IDR.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('stock')
                                            ->label('Stok')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Jumlah stok tersedia saat ini.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('warranty_months')
                                            ->label('Garansi (Bulan)')
                                            ->numeric()
                                            ->helperText('Durasi garansi produk dalam bulan. Kosongkan jika tidak ada garansi.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                    ]),
                                Section::make('Dimensi & Berat')
                                    ->columns(12)
                                    ->schema([
                                        TextInput::make('weight_gram')
                                            ->label('Berat (gram)')
                                            ->numeric()
                                            ->helperText('Berat produk dalam gram untuk kebutuhan pengiriman.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('length_mm')
                                            ->label('Panjang (mm)')
                                            ->numeric()
                                            ->helperText('Panjang produk atau kemasan dalam milimeter.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('width_mm')
                                            ->label('Lebar (mm)')
                                            ->numeric()
                                            ->helperText('Lebar produk atau kemasan dalam milimeter.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('height_mm')
                                            ->label('Tinggi (mm)')
                                            ->numeric()
                                            ->helperText('Tinggi produk atau kemasan dalam milimeter.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                    ]),
                                Section::make('Bonus & Insentif')
                                    ->columns(12)
                                    ->schema([
                                        TextInput::make('pv')
                                            ->label('PV')
                                            ->required()
                                            ->numeric()
                                            ->default(0.0)
                                            ->helperText('Nilai PV produk sesuai skema database existing.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('b_sponsor')
                                            ->label('Bonus Sponsor')
                                            ->required()
                                            ->numeric()
                                            ->default(0.0)
                                            ->helperText('Nominal bonus sponsor yang dihasilkan produk ini.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('b_royalty')
                                            ->label('Bonus Royalty')
                                            ->required()
                                            ->numeric()
                                            ->default(0.0)
                                            ->helperText('Nominal bonus royalty yang dihasilkan produk ini.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                        TextInput::make('b_cashback')
                                            ->label('Cashback')
                                            ->required()
                                            ->numeric()
                                            ->default(0.0)
                                            ->helperText('Nominal cashback yang diberikan dari pembelian produk ini.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3,
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Media')
                            ->schema([
                                Section::make('Galeri Media')
                                    ->description('Upload dan urutkan media produk. Aktifkan satu media sebagai gambar utama.')
                                    ->schema([
                                        Repeater::make('media')
                                            ->relationship('media')
                                            ->defaultItems(0)
                                            ->addActionLabel('Tambah Media')
                                            ->collapsed()
                                            ->reorderableWithDragAndDrop()
                                            ->orderColumn('sort_order')
                                            ->helperText('Tambahkan gambar produk. Gunakan satu item sebagai gambar utama.')
                                            ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? null)
                                            ->columns(12)
                                            ->schema([
                                                Hidden::make('type')
                                                    ->default('image'),
                                                Hidden::make('sort_order')
                                                    ->default(0),
                                                FileUpload::make('url')
                                                    ->label('File Media')
                                                    ->optimize('webp')
                                                    ->disk('public')
                                                    ->directory('products/media')
                                                    ->visibility('public')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->getUploadedFileNameForStorageUsing(static fn (): string => app(WebpImageUploadService::class)->generateWebpFilename())
                                                    ->saveUploadedFileUsing(static fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => app(WebpImageUploadService::class)->storeForFilament($component, $file))
                                                    ->required()
                                                    ->helperText('Upload file gambar produk. Format gambar direkomendasikan rasio konsisten.')
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'md' => 7,
                                                    ]),
                                                TextInput::make('alt_text')
                                                    ->label('Alt Text')
                                                    ->maxLength(255)
                                                    ->helperText('Teks alternatif untuk SEO dan aksesibilitas gambar.')
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'md' => 3,
                                                    ]),
                                                Toggle::make('is_primary')
                                                    ->label('Gambar Utama')
                                                    ->helperText('Aktifkan pada satu gambar yang akan ditampilkan sebagai thumbnail utama.')
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'md' => 2,
                                                    ]),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected static function generateSku(?string $name): string
    {
        $baseName = Str::of((string) $name)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->substr(0, 6)
            ->value();

        if ($baseName === '') {
            $baseName = 'PRD';
        }

        return "{$baseName}-" . Str::upper(Str::random(6));
    }

    protected static function generateSlug(?string $name): string
    {
        $slug = Str::slug((string) $name);

        if ($slug === '') {
            return 'produk-' . Str::lower(Str::random(6));
        }

        return $slug;
    }
}
