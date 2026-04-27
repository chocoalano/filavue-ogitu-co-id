<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PromotionForm
{
    /** Nilai valid untuk field show_on sesuai EloquentDashboardRepository & EloquentHomeRepository. */
    private const SHOW_ON_OPTIONS = [
        'homepage' => 'Homepage — Hanya di halaman beranda toko',
        'dashboard' => 'Dashboard — Di halaman dashboard member',
        'member' => 'Member — Khusus halaman area member',
        'cart' => 'Cart — Di halaman keranjang belanja',
        'checkout' => 'Checkout — Di halaman proses pembayaran',
        'all' => 'All — Tampil di semua halaman',
    ];

    /** @return array<string, string> */
    private static function typeInstructions(): array
    {
        return [
            'discount' => 'Promo **Discount** memberikan potongan harga reguler. Atur diskon global di bagian "Kondisi Diskon", lalu tambahkan produk di tab **"Produk dalam Promosi"** setelah disimpan.',
            'voucher' => 'Promo **Voucher** menggunakan kode kupon yang di-assign per customer oleh admin. Setelah menyimpan, buka tab **"Voucher Customer"** untuk membuat dan assign kode ke member. Tambahkan produk jika voucher berlaku untuk produk tertentu.',
            'flash_sale' => 'Promo **Flash Sale** adalah diskon waktu terbatas. Atur periode dengan cermat dan isi **Sisa Kuota**. Tambahkan produk di tab **"Produk dalam Promosi"** dan tentukan diskon per-produk.',
            'bundle' => 'Promo **Bundle** menjual produk dalam paket harga khusus. Tambahkan produk di tab **"Produk dalam Promosi"** dan isi **Harga Bundle** per-produk. Diskon persen/nominal tidak digunakan — harga bundle menggantikan harga satuan.',
            'free_shipping' => 'Promo **Free Shipping** menggratiskan ongkos kirim. Isi **Minimum Pembelian** agar promo hanya berlaku jika total belanja mencapai nilai tertentu. Produk dan diskon harga tidak diperlukan.',
            'member' => 'Promo **Member** adalah promo eksklusif untuk member tertentu. Gunakan tab **"Voucher Customer"** untuk assign promo ke member yang berhak. Tambahkan produk di tab **"Produk dalam Promosi"** jika promo berlaku untuk produk spesifik.',
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // =====================================================
                // CALLOUT: berubah dinamis sesuai tipe promo
                // =====================================================
                Callout::make(fn (Get $get): string => match ($get('type')) {
                    'discount' => 'Panduan: Promo Discount',
                    'voucher' => 'Panduan: Promo Voucher',
                    'flash_sale' => 'Panduan: Promo Flash Sale',
                    'bundle' => 'Panduan: Promo Bundle',
                    'free_shipping' => 'Panduan: Promo Free Shipping',
                    'member' => 'Panduan: Promo Member',
                    default => 'Pilih Tipe Promo untuk Melihat Panduan',
                })
                    ->description(fn (Get $get): string => self::typeInstructions()[$get('type')] ?? 'Pilih tipe promo di atas untuk memunculkan panduan pengisian form yang sesuai.')
                    ->color(fn (Get $get): string => match ($get('type')) {
                        'discount' => 'success',
                        'voucher' => 'primary',
                        'flash_sale' => 'danger',
                        'bundle' => 'warning',
                        'free_shipping' => 'info',
                        'member' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (Get $get): string => match ($get('type')) {
                        'discount' => 'heroicon-o-tag',
                        'voucher' => 'heroicon-o-ticket',
                        'flash_sale' => 'heroicon-o-bolt',
                        'bundle' => 'heroicon-o-cube',
                        'free_shipping' => 'heroicon-o-truck',
                        'member' => 'heroicon-o-users',
                        default => 'heroicon-o-information-circle',
                    }),

                Grid::make(12)->schema([
                    // =====================================================
                    // SECTION: Informasi Dasar (selalu tampil)
                    // =====================================================
                    Section::make('Informasi Promosi')
                        ->description('Data identitas dan periode aktif promo.')
                        ->columns(12)
                        ->schema([
                            Select::make('type')
                                ->label('Tipe Promo')
                                ->options([
                                    'discount' => 'Discount — Potongan harga reguler',
                                    'voucher' => 'Voucher — Kode kupon untuk member',
                                    'flash_sale' => 'Flash Sale — Diskon waktu terbatas',
                                    'bundle' => 'Bundle — Paket produk dengan harga khusus',
                                    'free_shipping' => 'Free Shipping — Gratis ongkos kirim',
                                    'member' => 'Member — Promo khusus member tertentu',
                                ])
                                ->native(false)
                                ->required()
                                ->live()
                                ->helperText('Pilih tipe untuk memunculkan panduan dan field yang relevan.')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            TextInput::make('code')
                                ->label('Kode Unik Promo')
                                ->required()
                                ->maxLength(100)
                                ->unique(ignoreRecord: true)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::upper(str_replace(' ', '-', (string) $state))))
                                ->helperText('Kode internal pengenal promo. Huruf kapital tanpa spasi. Contoh: RAMADAN-2026')
                                ->suffixAction(
                                    Action::make('generateCode')
                                        ->label('Generate')
                                        ->icon(Heroicon::OutlinedArrowPath)
                                        ->tooltip('Generate kode unik secara otomatis')
                                        ->action(fn (Set $set) => $set('code', strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4))))
                                )
                                ->columnSpan(['default' => 12, 'lg' => 5]),

                            Toggle::make('is_active')
                                ->label('Aktif / Tayang')
                                ->default(true)
                                ->required()
                                ->helperText('Nonaktifkan untuk menyembunyikan promo tanpa menghapus.')
                                ->columnSpan(['default' => 12, 'lg' => 3]),

                            TextInput::make('name')
                                ->label('Nama Promosi')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                    if (blank($get('landing_slug')) && filled($state)) {
                                        $set('landing_slug', Str::slug((string) $state));
                                    }
                                })
                                ->helperText('Nama tampilan promo. Contoh: Flash Sale Ramadan 2026')
                                ->columnSpan(['default' => 12, 'lg' => 6]),

                            TextInput::make('priority')
                                ->label('Prioritas Urutan')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->extraInputAttributes([
                                    'inputmode' => 'numeric',
                                    'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                                ])
                                ->helperText('Angka lebih kecil = tampil lebih awal.')
                                ->columnSpan(['default' => 12, 'lg' => 2]),

                            TextInput::make('landing_slug')
                                ->label('Landing Slug')
                                ->maxLength(255)
                                ->helperText('Slug halaman landing. Contoh: flash-sale-2026 → /promo/flash-sale-2026')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            Select::make('show_on')
                                ->label('Ditampilkan Di')
                                ->options(self::SHOW_ON_OPTIONS)
                                ->native(false)
                                ->searchable()
                                ->placeholder('Pilih lokasi banner...')
                                ->helperText('Halaman mana banner/promo ini ditampilkan.')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            TextInput::make('page')
                                ->label('Halaman Target (Link Banner)')
                                ->maxLength(255)
                                ->prefix('/')
                                ->placeholder('promo/ramadan-2026')
                                ->helperText('Path tujuan saat banner diklik. Contoh: shop, promo/flash-sale')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            DateTimePicker::make('start_at')
                                ->label('Mulai Berlaku')
                                ->seconds(false)
                                ->required()
                                ->columnSpan(['default' => 12, 'lg' => 6]),

                            DateTimePicker::make('end_at')
                                ->label('Berakhir')
                                ->seconds(false)
                                ->required()
                                ->afterOrEqual('start_at')
                                ->columnSpan(['default' => 12, 'lg' => 6]),
                        ])
                        ->columnSpan(['default' => 12, 'lg' => 8]),

                    // =====================================================
                    // SECTION: Konten & Aturan (selalu tampil)
                    // =====================================================
                    Section::make('Konten & Aturan')
                        ->description('Deskripsi, gambar, dan batas penggunaan.')
                        ->columns(12)
                        ->schema([
                            FileUpload::make('image')
                                ->label('Gambar Banner / Slider')
                                ->optimize('webp')
                                ->image()
                                ->disk('public')
                                ->directory('promotions')
                                ->visibility('public')
                                ->helperText('Rasio 16:5 disarankan (misal 1600×500 px).')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(3)
                                ->placeholder('Tulis deskripsi singkat promo ini...')
                                ->columnSpanFull(),

                            TextInput::make('max_redemption')
                                ->label('Maksimal Redemption Total')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Kosongkan = tidak terbatas')
                                ->helperText('Batas total pemakaian promo oleh semua user.')
                                ->columnSpan(['default' => 12, 'lg' => 6]),

                            TextInput::make('per_user_limit')
                                ->label('Batas Per User')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Kosongkan = tidak terbatas')
                                ->helperText('Batas pemakaian promo per satu akun member.')
                                ->columnSpan(['default' => 12, 'lg' => 6]),
                        ])
                        ->columnSpan(['default' => 12, 'lg' => 4]),

                    // =====================================================
                    // SECTION: Kondisi Diskon Global
                    // Tersembunyi jika tipe belum dipilih
                    // Field tertentu show/hide sesuai tipe
                    // =====================================================
                    Section::make('Kondisi Diskon Global')
                        ->description(fn (Get $get): string => match ($get('type')) {
                            'bundle' => 'Untuk Bundle: isi Minimum Pembelian dan Harga Bundle. Diskon persen/nominal tidak digunakan.',
                            'free_shipping' => 'Untuk Free Shipping: isi Minimum Pembelian. Diskon harga tidak diperlukan.',
                            'voucher' => 'Kondisi di sini berlaku sebagai fallback global. Detail diskon per-voucher diatur di tab "Voucher Customer".',
                            default => 'Kondisi berlaku untuk SELURUH promosi ini. Diskon per-produk diatur di tab "Produk dalam Promosi".',
                        })
                        ->columns(12)
                        ->hidden(fn (Get $get): bool => blank($get('type')))
                        ->schema([
                            TextInput::make('conditions_json.min_spend')
                                ->label('Minimum Pembelian')
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0)
                                ->placeholder('0')
                                ->helperText('Minimal total belanja agar promo berlaku.')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            // Diskon persen: tersembunyi untuk bundle & free_shipping
                            TextInput::make('conditions_json.discount_percent')
                                ->label('Diskon Persen')
                                ->numeric()
                                ->suffix('%')
                                ->minValue(0)
                                ->maxValue(100)
                                ->placeholder('0')
                                ->helperText('Contoh: 20 → diskon 20%. Prioritas lebih tinggi dari nominal.')
                                ->hidden(fn (Get $get): bool => in_array($get('type'), ['bundle', 'free_shipping'], true))
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            // Diskon nominal: tersembunyi untuk bundle & free_shipping
                            TextInput::make('conditions_json.discount_value')
                                ->label('Diskon Nominal')
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0)
                                ->placeholder('0')
                                ->helperText('Contoh: 50000 → Potongan Rp50.000')
                                ->hidden(fn (Get $get): bool => in_array($get('type'), ['bundle', 'free_shipping'], true))
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            // Harga bundle global: hanya muncul untuk bundle
                            TextInput::make('conditions_json.bundle_price')
                                ->label('Harga Bundle (Global)')
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0)
                                ->placeholder('0')
                                ->helperText('Harga paket bundle secara global. Bisa dioverride per-produk di tab Produk.')
                                ->visible(fn (Get $get): bool => $get('type') === 'bundle')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            // Sisa kuota: semua tipe kecuali free_shipping
                            TextInput::make('conditions_json.quota_left')
                                ->label('Sisa Kuota')
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('0')
                                ->extraInputAttributes([
                                    'inputmode' => 'numeric',
                                    'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                                ])
                                ->helperText('Sisa kuota ditampilkan ke member. Wajib diisi untuk Flash Sale.')
                                ->hidden(fn (Get $get): bool => $get('type') === 'free_shipping')
                                ->columnSpan(['default' => 12, 'lg' => 4]),

                            Textarea::make('conditions_json.terms')
                                ->label('Syarat & Ketentuan')
                                ->rows(4)
                                ->placeholder("- Berlaku untuk semua produk\n- Tidak dapat digabung dengan promo lain\n- Berlaku selama stok masih ada")
                                ->helperText('Satu syarat per baris. Ditampilkan sebagai poin di halaman promo.')
                                ->columnSpan(['default' => 12, 'lg' => 4]),
                        ])
                        ->columnSpanFull(),

                    // =====================================================
                    // SECTION: Custom HTML — collapsed, selalu ada
                    // =====================================================
                    Section::make('Konten HTML Kustom')
                        ->description('Opsional. HTML tambahan untuk halaman landing promo.')
                        ->schema([
                            Textarea::make('custom_html')
                                ->label('Custom HTML')
                                ->rows(6)
                                ->placeholder('<p>Konten HTML kustom jika dibutuhkan...</p>')
                                ->helperText('Opsional. Hanya isi jika ada kebutuhan tampilan khusus di halaman landing.'),
                        ])
                        ->columnSpanFull()
                        ->collapsed(),
                ])->columnSpanFull(),
            ]);
    }

    /**
     * Generate data autofill acak namun valid untuk pengujian form Promosi.
     * Data relasi (customer, produk) diambil dari database yang sudah ada.
     *
     * @return array<string, mixed>
     */
    public static function testingAutofillData(): array
    {
        $faker = fake('id_ID');

        $types = array_keys([
            'discount' => '',
            'voucher' => '',
            'flash_sale' => '',
            'bundle' => '',
            'free_shipping' => '',
            'member' => '',
        ]);
        $type = $faker->randomElement($types);

        $showOnOptions = array_keys(self::SHOW_ON_OPTIONS);
        $showOn = $faker->randomElement($showOnOptions);

        $productNames = [
            'Flash Sale', 'Promo Spesial', 'Diskon Akhir Tahun', 'Harbolnas',
            'Paket Hemat', 'Bundling Spesial', 'Member Eksklusif', 'Weekend Sale',
        ];
        $suffix = Str::upper(Str::random(4));
        $nameBase = $faker->randomElement($productNames).' '.$faker->monthName().' '.now()->year;
        $name = $nameBase.' '.$suffix;
        $code = Str::upper(Str::slug($faker->randomElement(['PROMO', 'SALE', 'DISC', 'VCR', 'DEAL'])).'-'.now()->format('Ym').'-'.$suffix);
        $slug = Str::slug($name);

        $startAt = now()->addDays($faker->numberBetween(1, 7))->startOfDay();
        $endAt = (clone $startAt)->addDays($faker->numberBetween(7, 30))->endOfDay();

        $discountPercent = $faker->numberBetween(5, 50);
        $discountValue = $faker->numberBetween(5, 50) * 5000;
        $minSpend = $faker->numberBetween(1, 20) * 50000;
        $quotaLeft = $faker->numberBetween(10, 500);

        $terms = implode("\n", [
            '- Promo berlaku pada periode yang ditentukan.',
            '- Tidak dapat digabung dengan promo lain.',
            '- Berlaku selama stok dan kuota masih tersedia.',
            '- Berlaku untuk member yang sudah melakukan verifikasi akun.',
            '- Keputusan tim '.\config('app.name').' bersifat final.',
        ]);

        // Ambil sample produk yang aktif dari database untuk referensi deskripsi
        $sampleProduct = Product::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->value('name');

        // Ambil sample customer aktif dari database untuk referensi deskripsi
        $sampleCustomer = Customer::query()
            ->where('status', 'active')
            ->inRandomOrder()
            ->value('name');

        $productHint = $sampleProduct ? " Termasuk produk seperti \"{$sampleProduct}\"." : '';
        $customerHint = $sampleCustomer ? " Cocok untuk member seperti {$sampleCustomer}." : '';

        $description = match ($type) {
            'discount' => "Dapatkan potongan harga hingga {$discountPercent}% untuk produk pilihan.{$productHint}",
            'voucher' => "Gunakan kode {$code} di halaman checkout untuk mendapatkan diskon eksklusif.{$customerHint}",
            'flash_sale' => "Flash sale terbatas! Diskon {$discountPercent}% hanya berlaku selama periode promo.{$productHint}",
            'bundle' => "Beli paket bundling spesial dan hemat lebih banyak.{$productHint}",
            'free_shipping' => 'Gratis ongkos kirim ke seluruh Indonesia untuk pembelian minimal Rp'.number_format($minSpend, 0, ',', '.').'.',
            'member' => "Promo eksklusif untuk member terpilih.{$customerHint}",
            default => $faker->paragraph(),
        };

        $pageOptions = ['shop', 'promo/'.Str::slug($nameBase), 'produk', 'member/dashboard'];

        return [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'is_active' => true,
            'priority' => $faker->numberBetween(0, 10),
            'landing_slug' => $slug,
            'show_on' => $showOn,
            'page' => $faker->randomElement($pageOptions),
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'description' => $description,
            'max_redemption' => $faker->randomElement([$quotaLeft, null]),
            'per_user_limit' => $faker->randomElement([1, 2, 3, null]),
            'conditions_json' => [
                'min_spend' => $minSpend,
                'discount_percent' => in_array($type, ['discount', 'flash_sale', 'voucher', 'member']) ? $discountPercent : null,
                'discount_value' => $type === 'free_shipping' ? null : $discountValue,
                'bundle_price' => $type === 'bundle' ? ($faker->numberBetween(5, 50) * 10000) : null,
                'quota_left' => $quotaLeft,
                'terms' => $terms,
            ],
            'custom_html' => null,
            'image' => null,
        ];
    }
}
