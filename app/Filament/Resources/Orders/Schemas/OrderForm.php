<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\CustomerAddress;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ============================================================
            // ROW 1: Identitas Pesanan (2/3) | Timeline (1/3)
            // ============================================================
            Grid::make(['default' => 1, 'lg' => 2])->schema([

                Section::make('Identitas Pesanan')
                    ->description('Nomor order, pelanggan, status, tipe plan, dan mata uang.')
                    ->icon('heroicon-o-shopping-bag')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_no')
                            ->label('Nomor Pesanan')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Kode unik pesanan.'),

                        Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Pilih pelanggan yang melakukan pesanan ini.'),

                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options(Order::statusOptions())
                            ->native(false)
                            ->default('pending')
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Status order mengikuti workflow admin: PAID -> SHIPPED -> DELIVERED.'),

                        Select::make('type')
                            ->label('Tipe Plan')
                            ->options([
                                'planA' => 'Plan A',
                                'planB' => 'Plan B',
                            ])
                            ->native(false)
                            ->default('planA')
                            ->required()
                            ->helperText('Tentukan jenis paket yang diambil.'),

                        TextInput::make('currency')
                            ->label('Mata Uang')
                            ->required()
                            ->maxLength(10)
                            ->default('IDR')
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->helperText('Kode ISO 4217, misal: IDR, USD, SGD.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),

                Section::make('Timeline')
                    ->description('Waktu pencatatan setiap tahap proses order.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        DateTimePicker::make('placed_at')
                            ->label('Waktu Pemesanan')
                            ->prefixIcon('heroicon-o-shopping-cart')
                            ->seconds(false)
                            ->helperText('Kapan order ditempatkan oleh pelanggan.'),

                        DateTimePicker::make('paid_at')
                            ->label('Waktu Pembayaran')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->seconds(false)
                            ->helperText('Kapan pembayaran dikonfirmasi.'),

                        DateTimePicker::make('processed_at')
                            ->label('Waktu Diproses')
                            ->prefixIcon('heroicon-o-cog-6-tooth')
                            ->seconds(false)
                            ->helperText('Kapan order mulai diproses/dikirim.'),
                    ])
                    ->columnSpan(1),

            ])->columnSpanFull(),

            // ============================================================
            // ROW 2: Nilai Transaksi (2/3) | Alamat & Catatan (1/3)
            // ============================================================
            Grid::make(['default' => 1, 'lg' => 2])->schema([

                Section::make('Nilai Transaksi')
                    ->description('Komponen nilai finansial order. Semua nominal dalam rupiah (Rp).')
                    ->icon('heroicon-o-calculator')
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal_amount')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Total harga produk sebelum diskon, ongkir, dan pajak.'),

                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Total nilai diskon yang diterapkan pada order.'),

                        TextInput::make('shipping_amount')
                            ->label('Ongkos Kirim')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Biaya pengiriman yang ditagihkan ke pelanggan.'),

                        TextInput::make('tax_amount')
                            ->label('Pajak')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Pajak yang dikenakan, misal PPN 11%.'),

                        TextInput::make('grand_total')
                            ->label('Total Akhir')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('Rp')
                            ->helperText('Rumus: Subtotal − Diskon + Ongkir + Pajak.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Alamat & Catatan')
                    ->description('Alamat pengiriman/penagihan, kode promo, dan catatan internal.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Select::make('shipping_address_id')
                            ->label('Alamat Pengiriman')
                            ->relationship('shippingAddress', 'recipient_name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (CustomerAddress $record): string => self::addressOptionLabel($record))
                            ->placeholder('Pilih alamat pengiriman')
                            ->prefixIcon('heroicon-o-truck')
                            ->helperText('Alamat tujuan pengiriman produk.'),

                        Select::make('billing_address_id')
                            ->label('Alamat Penagihan')
                            ->relationship('billingAddress', 'recipient_name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (CustomerAddress $record): string => self::addressOptionLabel($record))
                            ->placeholder('Pilih alamat penagihan')
                            ->prefixIcon('heroicon-o-building-office')
                            ->helperText('Kosongkan jika sama dengan alamat pengiriman.'),

                        TagsInput::make('applied_promos')
                            ->label('Kode Promo')
                            ->placeholder('Contoh: FLASHSALE')
                            ->helperText('Ketik kode promo lalu tekan Enter untuk menambahkan.')
                            ->afterStateHydrated(function (TagsInput $component, mixed $state): void {
                                $component->state(self::normalizePromoTags($state));
                            })
                            ->dehydrateStateUsing(function (mixed $state): ?array {
                                $values = self::normalizePromoTags($state);

                                return empty($values) ? null : $values;
                            }),

                        Textarea::make('notes')
                            ->label('Catatan Pesanan')
                            ->rows(4)
                            ->placeholder('Catatan internal atau instruksi khusus untuk tim...')
                            ->helperText('Catatan ini hanya terlihat oleh admin, tidak tampil ke pelanggan.'),
                    ]),

            ])->columnSpanFull(),

            // ============================================================
            // ROW 3: Bonus & Komisi (full width, collapsible)
            // ============================================================
            Section::make('Bonus & Komisi')
                ->description('Komponen bonus dari transaksi ini. Biasanya dihitung otomatis — isi manual hanya jika diperlukan.')
                ->icon('heroicon-o-gift')
                ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('bv_amount')
                        ->label('BV (Business Volume)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->suffix('BV')
                        ->default(0)
                        ->helperText('Poin volume bisnis yang dihasilkan dari order ini.'),

                    TextInput::make('sponsor_amount')
                        ->label('Bonus Sponsor')
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Komisi untuk sponsor/upline langsung.'),

                    TextInput::make('match_amount')
                        ->label('Bonus Matching')
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Bonus dari kesamaan volume jaringan (matching).'),

                    TextInput::make('pairing_amount')
                        ->label('Bonus Pairing')
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Bonus dari pasangan jaringan binary kiri/kanan.'),

                    TextInput::make('retail_amount')
                        ->label('Bonus Retail')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Selisih harga retail dikurangi harga member.'),

                    TextInput::make('cashback_amount')
                        ->label('Bonus Cashback')
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Cashback yang dikembalikan ke pelanggan.'),

                    TextInput::make('stockist_amount')
                        ->label('Bonus Stockist')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->step(1)
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Bonus untuk stockist yang terkait dengan order ini.'),

                    Toggle::make('bonus_generated')
                        ->label('Bonus Sudah Dibentuk')
                        ->required()
                        ->helperText('Aktifkan jika semua bonus sudah dikalkulasi dan siap dibagikan.'),
                ])->columnSpanFull(),

        ]);
    }

    private static function addressOptionLabel(CustomerAddress $record): string
    {
        return collect([
            $record->label,
            $record->recipient_name,
            $record->city_label,
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->implode(' • ');
    }

    /**
     * @return array<int, string>
     */
    private static function normalizePromoTags(mixed $state): array
    {
        if (blank($state)) {
            return [];
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = is_array($decoded) ? $decoded : [$state];
        }

        if (! is_array($state)) {
            return [];
        }

        return collect($state)
            ->map(function (mixed $value): ?string {
                if (is_scalar($value)) {
                    return trim((string) $value);
                }

                if (is_array($value)) {
                    if (isset($value['code']) && is_scalar($value['code'])) {
                        return trim((string) $value['code']);
                    }

                    return json_encode($value, JSON_UNESCAPED_UNICODE) ?: null;
                }

                return null;
            })
            ->filter(fn (?string $value): bool => filled($value))
            ->values()
            ->all();
    }
}
