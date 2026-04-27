<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Arr;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // =========================
            // CALLOUT: Status Kritis
            // =========================
            Callout::make(fn (Order $record): string => match (strtolower((string) $record->status)) {
                'cancelled', 'canceled' => 'Pesanan Dibatalkan',
                'pending' => 'Menunggu Pembayaran',
                default => '',
            })
                ->description(fn (Order $record): string => match (strtolower((string) $record->status)) {
                    'cancelled', 'canceled' => 'Pesanan ini telah dibatalkan dan tidak dapat diproses lebih lanjut.',
                    'pending' => 'Pesanan belum dibayar. Menunggu konfirmasi pembayaran dari pelanggan.',
                    default => '',
                })
                ->color(fn (Order $record): string => match (strtolower((string) $record->status)) {
                    'cancelled', 'canceled' => 'danger',
                    'pending' => 'warning',
                    default => 'gray',
                })
                ->icon(fn (Order $record): string => match (strtolower((string) $record->status)) {
                    'cancelled', 'canceled' => 'heroicon-o-x-circle',
                    'pending' => 'heroicon-o-clock',
                    default => 'heroicon-o-information-circle',
                })
                ->visible(fn (Order $record): bool => in_array(
                    strtolower((string) $record->status),
                    ['cancelled', 'canceled', 'pending'],
                )),

            // =========================
            // HEADER: Ringkasan Pesanan
            // =========================
            Section::make('Ringkasan Pesanan')
                ->description('Informasi utama pesanan dan pelanggan.')
                ->icon('heroicon-o-shopping-bag')
                ->columns(['default' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('order_no')
                        ->label('Nomor Pesanan')
                        ->fontFamily(FontFamily::Mono)
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->copyable()
                        ->icon('heroicon-o-hashtag'),

                    TextEntry::make('grand_total')
                        ->label('Total Akhir')
                        ->money(fn (Order $record): string => self::currency($record))
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->icon('heroicon-o-banknotes'),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (?string $state, Order $record): string => $record->workflowStatusLabel())
                        ->color(fn (?string $state): string => self::statusColor($state))
                        ->placeholder('-'),

                    TextEntry::make('resolved_payment_type')
                        ->label('Tipe Pembayaran')
                        ->badge()
                        ->state(fn (Order $record): ?string => $record->resolvedPaymentType())
                        ->formatStateUsing(fn (?string $state, Order $record): string => $record->paymentTypeLabel())
                        ->placeholder('-'),

                    TextEntry::make('type')
                        ->label('Tipe Plan')
                        ->badge()
                        ->color(fn (?string $state): string => self::typeColor($state))
                        ->placeholder('-'),

                    TextEntry::make('currency')
                        ->label('Mata Uang')
                        ->badge()
                        ->placeholder('-'),

                    IconEntry::make('bonus_generated')
                        ->label('Bonus Terbentuk')
                        ->boolean()
                        ->size(IconSize::Medium),

                    TextEntry::make('customer.name')
                        ->label('Pelanggan')
                        ->icon('heroicon-o-user')
                        ->placeholder('-'),

                    TextEntry::make('customer.username')
                        ->label('Username')
                        ->badge()
                        ->placeholder('-'),
                ])->columnSpanFull(),

            // =========================
            // TABS: Detail Pesanan
            // =========================
            Tabs::make('Detail')
                ->id('order-infolist-tabs')
                ->persistTab()
                ->scrollable(false)
                ->contained(false)
                ->tabs([

                    // -------------------------
                    // TAB: Transaksi
                    // -------------------------
                    Tab::make('Transaksi')
                        ->icon('heroicon-o-receipt-percent')
                        ->columns(['default' => 1, 'lg' => 3])
                        ->schema([
                            Section::make('Timeline')
                                ->description('Jejak waktu proses order.')
                                ->icon('heroicon-o-clock')
                                ->compact()
                                ->inlineLabel()
                                ->schema([
                                    TextEntry::make('placed_at')
                                        ->label('Dipesan')
                                        ->icon('heroicon-o-shopping-cart')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('paid_at')
                                        ->label('Dibayar')
                                        ->icon('heroicon-o-credit-card')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('processed_at')
                                        ->label('Diproses')
                                        ->icon('heroicon-o-cog-6-tooth')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('created_at')
                                        ->label('Dibuat')
                                        ->icon('heroicon-o-plus-circle')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Diperbarui')
                                        ->icon('heroicon-o-pencil-square')
                                        ->dateTime()
                                        ->placeholder('-'),
                                ])
                                ->columnSpan(1),

                            Section::make('Nilai Transaksi')
                                ->description('Subtotal, diskon, ongkir, pajak, dan promo.')
                                ->icon('heroicon-o-calculator')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('subtotal_amount')
                                        ->label('Subtotal')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('discount_amount')
                                        ->label('Diskon')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('shipping_amount')
                                        ->label('Ongkir')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('tax_amount')
                                        ->label('Pajak')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    KeyValueEntry::make('applied_promos')
                                        ->label('Kode Promo')
                                        ->keyLabel('Kode')
                                        ->valueLabel('Detail')
                                        ->state(fn (Order $record): array => self::promosToKeyValue($record->applied_promos))
                                        ->visible(fn (Order $record): bool => filled($record->applied_promos))
                                        ->columnSpanFull(),
                                ])
                                ->columnSpan(['default' => 1, 'lg' => 2]),
                        ]),

                    // -------------------------
                    // TAB: Bonus & Komisi
                    // -------------------------
                    Tab::make('Bonus & Komisi')
                        ->icon('heroicon-o-gift')
                        ->schema([
                            Section::make('Bonus & Komisi')
                                ->description('Komponen bonus yang dihasilkan dari transaksi ini.')
                                ->icon('heroicon-o-sparkles')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('bv_amount')
                                        ->label('BV')
                                        ->numeric(decimalPlaces: 2)
                                        ->placeholder('-'),

                                    TextEntry::make('sponsor_amount')
                                        ->label('Bonus Sponsor')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('match_amount')
                                        ->label('Bonus Matching')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('pairing_amount')
                                        ->label('Bonus Pairing')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('retail_amount')
                                        ->label('Bonus Retail')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('cashback_amount')
                                        ->label('Bonus Cashback')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-'),

                                    TextEntry::make('stockist_amount')
                                        ->label('Bonus Stockist')
                                        ->money(fn (Order $record): string => self::currency($record))
                                        ->placeholder('-')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // -------------------------
                    // TAB: Alamat
                    // -------------------------
                    Tab::make('Alamat')
                        ->icon('heroicon-o-map-pin')
                        ->columns(['default' => 1, 'lg' => 2])
                        ->schema([
                            Section::make('Pengiriman')
                                ->description('Data alamat pengiriman.')
                                ->icon('heroicon-o-truck')
                                ->compact()
                                ->schema([
                                    Callout::make('Belum ada alamat pengiriman.')
                                        ->warning()
                                        ->icon('heroicon-o-exclamation-triangle')
                                        ->visible(fn (Order $record): bool => blank($record->shippingAddress)),

                                    KeyValueEntry::make('shipping_address')
                                        ->label('')
                                        ->keyLabel('Field')
                                        ->valueLabel('Nilai')
                                        ->state(fn (Order $record): array => self::addressToKeyValue($record->shippingAddress))
                                        ->visible(fn (Order $record): bool => filled($record->shippingAddress))
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Penagihan')
                                ->description('Data alamat penagihan.')
                                ->icon('heroicon-o-building-office')
                                ->compact()
                                ->schema([
                                    Callout::make('Belum ada alamat penagihan.')
                                        ->warning()
                                        ->icon('heroicon-o-exclamation-triangle')
                                        ->visible(fn (Order $record): bool => blank($record->billingAddress)),

                                    KeyValueEntry::make('billing_address')
                                        ->label('')
                                        ->keyLabel('Field')
                                        ->valueLabel('Nilai')
                                        ->state(fn (Order $record): array => self::addressToKeyValue($record->billingAddress))
                                        ->visible(fn (Order $record): bool => filled($record->billingAddress))
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // -------------------------
                    // TAB: Operasional
                    // -------------------------
                    Tab::make('Operasional')
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make('Operasional Terkait')
                                ->description('Ringkasan data turunan order untuk monitoring.')
                                ->icon('heroicon-o-cube')
                                ->columns(['default' => 2, 'lg' => 3])
                                ->schema([
                                    TextEntry::make('items_count')
                                        ->label('Total Item')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'items_count', fn () => $record->items()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-shopping-bag'),

                                    TextEntry::make('items_sum_qty')
                                        ->label('Total Kuantitas')
                                        ->state(fn (Order $record): float => (float) self::safeAttr($record, 'items_sum_qty', fn () => $record->items()->sum('qty')))
                                        ->numeric(decimalPlaces: 2)
                                        ->icon('heroicon-o-hashtag'),

                                    TextEntry::make('payments_count')
                                        ->label('Total Pembayaran')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'payments_count', fn () => $record->payments()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-credit-card'),

                                    TextEntry::make('shipments_count')
                                        ->label('Total Pengiriman')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'shipments_count', fn () => $record->shipments()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-truck'),

                                    TextEntry::make('refunds_count')
                                        ->label('Total Pengembalian Dana')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'refunds_count', fn () => $record->refunds()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-arrow-uturn-left'),

                                    TextEntry::make('returns_count')
                                        ->label('Total Retur')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'returns_count', fn () => $record->returns()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-arrow-path'),

                                    TextEntry::make('bonus_cashbacks_count')
                                        ->label('Total Bonus Cashback')
                                        ->state(fn (Order $record): int => (int) self::safeAttr($record, 'bonus_cashbacks_count', fn () => $record->bonusCashbacks()->count()))
                                        ->badge()
                                        ->icon('heroicon-o-gift')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // -------------------------
                    // TAB: Catatan & Sistem
                    // -------------------------
                    Tab::make('Catatan & Sistem')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Catatan Pesanan')
                                ->description('Catatan tambahan untuk order ini.')
                                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                ->collapsible()
                                ->schema([
                                    TextEntry::make('notes')
                                        ->label('')
                                        ->placeholder('-')
                                        ->markdown()
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Informasi Sistem')
                                ->description('Metadata pembuatan dan pembaruan.')
                                ->icon('heroicon-o-server')
                                ->compact()
                                ->inlineLabel()
                                ->secondary()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Dibuat')
                                        ->icon('heroicon-o-plus-circle')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Diperbarui')
                                        ->icon('heroicon-o-pencil-square')
                                        ->dateTime()
                                        ->placeholder('-'),
                                ]),
                        ]),
                ])->columnSpanFull(),
        ]);
    }

    private static function currency(Order $record): string
    {
        return $record->currency ?: 'IDR';
    }

    private static function safeAttr(Order $record, string $attribute, callable $fallback): mixed
    {
        if (array_key_exists($attribute, $record->getAttributes()) && $record->{$attribute} !== null) {
            return $record->{$attribute};
        }

        return $fallback();
    }

    private static function statusColor(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'processing', 'delivered' => 'success',
            'shipped' => 'info',
            'pending' => 'warning',
            'cancelled', 'canceled' => 'danger',
            default => 'gray',
        };
    }

    private static function typeColor(?string $type): string
    {
        return match (strtolower((string) $type)) {
            'plana' => 'primary',
            'planb' => 'info',
            default => 'gray',
        };
    }

    /**
     * Bentuk 1D associative array untuk KeyValueEntry applied_promos.
     *
     * Mendukung format:
     * - JSON string → di-decode terlebih dahulu
     * - String tunggal → ['1' => 'KODE']
     * - Indexed array (list kode) → ['1' => 'KODE1', '2' => 'KODE2']
     * - Associative array → dipakai langsung, value di-cast ke string
     * - Array of objects → key diambil dari 'code'/'kode', value dari 'discount'/'amount'/'value'
     */
    private static function promosToKeyValue(mixed $state): array
    {
        if (blank($state)) {
            return [];
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = is_array($decoded) ? $decoded : [trim($state)];
        }

        if (! is_array($state)) {
            return [];
        }

        $result = [];

        foreach ($state as $key => $value) {
            if (is_array($value)) {
                $label = (string) ($value['code'] ?? $value['kode'] ?? $value['name'] ?? (is_int($key) ? $key + 1 : $key));
                $detail = (string) ($value['discount'] ?? $value['amount'] ?? $value['value'] ?? $value['detail'] ?? '-');
                $result[$label] = $detail;
            } elseif (is_int($key)) {
                $result[(string) ($key + 1)] = trim((string) $value);
            } else {
                $result[(string) $key] = trim((string) $value);
            }
        }

        return $result;
    }

    /**
     * Bentuk 1D array untuk KeyValueEntry.
     */
    private static function addressToKeyValue(mixed $address): array
    {
        if (blank($address)) {
            return [];
        }

        $data = [
            'Penerima' => data_get($address, 'recipient_name'),
            'Telepon' => data_get($address, 'recipient_phone'),
            'Kota' => data_get($address, 'city_label'),
            'Alamat Utama' => data_get($address, 'address_line1'),
            'Alamat Tambahan' => data_get($address, 'address_line2'),
        ];

        return Arr::where($data, fn ($value) => filled($value));
    }
}
