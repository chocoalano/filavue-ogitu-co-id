<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Customer Information')
                ->tabs([
                    self::profilTab(),
                    self::jaringanTab(),
                    self::pesananTab(),
                    self::walletBonusTab(),
                    self::rewardTab(),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function profilTab(): Tab
    {
        return Tab::make('Profil')
            ->schema([
                Grid::make(12)
                    ->schema([
                        Section::make('Ringkasan Akun')
                            ->description('Informasi utama customer.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 4,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 8,
                            ])
                            ->schema([
                                self::text('username', 'Username'),
                                self::text('name', 'Nama Lengkap'),
                                self::statusEntry('status', 'Status Customer'),
                                self::text('package.name', 'Paket'),
                                self::text('ref_code', 'Kode Referal'),
                                self::text('level', 'Level')->badge(),
                                IconEntry::make('network_generated')
                                    ->label('Generate Jaringan')
                                    ->boolean(),
                            ]),

                        Section::make('Relasi Jaringan')
                            ->description('Sponsor customer.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 4,
                            ])
                            ->schema([
                                self::text('sponsor.name', 'Sponsor'),
                            ]),

                        Section::make('Identitas & Kontak')
                            ->description('Data identitas dasar dan kontak customer.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 3,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 8,
                            ])
                            ->schema([
                                self::text('nik', 'NIK'),
                                self::text('gender', 'Jenis Kelamin')->badge(),
                                self::text('email', 'Email'),
                                self::text('phone', 'Nomor Telepon'),
                                self::dateTimeEntry('created_at', 'Dibuat Pada'),
                                self::dateTimeEntry('updated_at', 'Diperbarui Pada'),
                            ]),

                        Section::make('Keanggotaan & Wallet')
                            ->description('Status keanggotaan dan saldo bonus customer.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 4,
                            ])
                            ->schema([
                                self::text('ewallet_id', 'ID E-Wallet'),
                                self::moneyEntry('ewallet_saldo', 'Saldo E-Wallet'),
                                self::moneyEntry('bonus_pending', 'Bonus Tertunda'),
                                self::moneyEntry('bonus_processed', 'Bonus Diproses'),
                            ]),

                        Section::make('Alamat')
                            ->description('Alamat utama dan alamat tambahan customer.')
                            ->columns(1)
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 8,
                            ])
                            ->schema([
                                self::text('alamat', 'Alamat'),
                                self::text('address', 'Alamat Tambahan'),
                            ]),

                        Section::make('Bank & Stockist')
                            ->description('Informasi rekening dan data stockist.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 4,
                            ])
                            ->schema([
                                self::text('bank_name', 'Nama Bank'),
                                self::text('bank_account', 'Nomor Rekening'),
                                IconEntry::make('is_stockist')
                                    ->label('Stockist')
                                    ->boolean(),
                                self::text('stockist_kabupaten_name', 'Kabupaten Stockist'),
                                self::text('stockist_province_name', 'Provinsi Stockist'),
                                self::numberEntry('stockist_kabupaten_id', 'ID Kabupaten'),
                                self::numberEntry('stockist_province_id', 'ID Provinsi'),
                            ]),

                        Section::make('Metrik Jaringan')
                            ->description('Statistik sponsor, PV, dan omzet sesuai tabel customer existing.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 4,
                            ])
                            ->collapsed()
                            ->columnSpan(12)
                            ->schema([
                                self::numberEntry('sponsor_left', 'Sponsor Kiri'),
                                self::numberEntry('pv', 'PV'),

                                self::moneyEntry('omzet', 'Omzet Pribadi'),
                                self::moneyEntry('omzet_group', 'Omzet Grup'),
                            ]),

                        Section::make('Informasi Sistem')
                            ->description('Metadata audit data.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 6,
                            ])
                            ->schema([
                                self::dateTimeEntry('created_at', 'Dibuat Pada'),
                                self::dateTimeEntry('updated_at', 'Diperbarui Pada'),
                            ]),
                    ]),
            ]);
    }

    private static function jaringanTab(): Tab
    {
        return Tab::make('Jaringan')
            ->schema([
                Section::make('Downline Sponsor')
                    ->description('Member yang disponsori langsung oleh customer ini.')
                    ->schema([
                        RepeatableEntry::make('downlines')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Username'),
                                TableColumn::make('Nama'),
                                TableColumn::make('Level'),
                                TableColumn::make('Status'),
                                TableColumn::make('Bergabung'),
                            ])
                            ->schema([
                                self::text('username', 'Username'),
                                self::text('name', 'Nama'),
                                self::text('level', 'Level')->badge(),
                                self::statusEntry('status', 'Status'),
                                self::dateEntry('created_at', 'Bergabung'),
                            ]),
                    ]),

                Section::make('Network Matrix')
                    ->description('Matrix jaringan sponsor customer.')
                    ->schema([
                        RepeatableEntry::make('networkMatrixes')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Sponsor ID'),
                                TableColumn::make('Level'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('sponsor_id', 'Sponsor ID'),
                                self::text('level', 'Level'),
                                self::text('description', 'Keterangan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),
            ]);
    }

    private static function pesananTab(): Tab
    {
        return Tab::make('Pesanan')
            ->schema([
                Section::make('Pesanan')
                    ->description('Riwayat transaksi belanja customer.')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('No. Order'),
                                TableColumn::make('Status'),
                                TableColumn::make('Total'),
                                TableColumn::make('BV'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Tanggal'),
                            ])
                            ->schema([
                                self::text('order_no', 'No. Order'),
                                self::statusEntry('status', 'Status'),
                                self::moneyEntry('grand_total', 'Total'),
                                self::numberEntry('bv_amount', 'BV'),
                                self::text('type', 'Tipe')->badge(),
                                self::dateEntry('placed_at', 'Tanggal'),
                            ]),
                    ]),

                Section::make('Alamat Pengiriman')
                    ->description('Daftar alamat pengiriman yang tersimpan.')
                    ->schema([
                        RepeatableEntry::make('addresses')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Label'),
                                TableColumn::make('Penerima'),
                                TableColumn::make('Telepon'),
                                TableColumn::make('Alamat'),
                                TableColumn::make('Kota'),
                                TableColumn::make('Provinsi'),
                                TableColumn::make('Default'),
                            ])
                            ->schema([
                                self::text('label', 'Label'),
                                self::text('recipient_name', 'Penerima'),
                                self::text('recipient_phone', 'Telepon'),
                                self::text('address_line1', 'Alamat'),
                                self::text('city_label', 'Kota'),
                                self::text('province_label', 'Provinsi'),
                                IconEntry::make('is_default')->label('Default')->boolean(),
                            ]),
                    ]),

                Section::make('Wishlist')
                    ->description('Produk yang disimpan dalam wishlist.')
                    ->schema([
                        RepeatableEntry::make('wishlists')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Nama'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::text('name', 'Nama'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Review Produk')
                    ->description('Ulasan produk yang ditulis customer.')
                    ->schema([
                        RepeatableEntry::make('productReviews')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Produk'),
                                TableColumn::make('Rating'),
                                TableColumn::make('Ulasan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::text('product.name', 'Produk'),
                                self::numberEntry('rating', 'Rating'),
                                self::text('body', 'Ulasan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),
            ]);
    }

    private static function walletBonusTab(): Tab
    {
        return Tab::make('Wallet & Bonus')
            ->schema([
                Section::make('Transaksi Wallet')
                    ->description('Riwayat transaksi e-wallet customer.')
                    ->schema([
                        RepeatableEntry::make('walletTransactions')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Tipe'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Saldo Sebelum'),
                                TableColumn::make('Saldo Sesudah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Tanggal'),
                            ])
                            ->schema([
                                self::text('type', 'Tipe')->badge(),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::moneyEntry('balance_before', 'Saldo Sebelum'),
                                self::moneyEntry('balance_after', 'Saldo Sesudah'),
                                self::statusEntry('status', 'Status'),
                                self::text('notes', 'Keterangan'),
                                self::dateTimeEntry('created_at', 'Tanggal'),
                            ]),
                    ]),

                Section::make('Bonus (Ringkasan)')
                    ->description('Ringkasan bonus harian customer.')
                    ->schema([
                        RepeatableEntry::make('bonuses')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Tanggal'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Pajak (%)'),
                                TableColumn::make('Nilai Pajak'),
                                TableColumn::make('Netto'),
                                TableColumn::make('Status'),
                            ])
                            ->schema([
                                self::dateEntry('date', 'Tanggal'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::numberEntry('tax_percent', 'Pajak (%)'),
                                self::moneyEntry('tax_value', 'Nilai Pajak'),
                                self::moneyEntry('tax_netto', 'Netto'),
                                self::statusEntry('status', 'Status'),
                            ]),
                    ]),

                Section::make('Bonus Sponsor')
                    ->description('Bonus dari downline yang disponsori.')
                    ->schema([
                        RepeatableEntry::make('bonusSponsors')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Dari Member'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('from_member_id', 'Dari Member'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::text('description', 'Keterangan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Matching')
                    ->description('Bonus dari kedalaman jaringan.')
                    ->schema([
                        RepeatableEntry::make('bonusMatchings')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Dari Member'),
                                TableColumn::make('Level'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('from_member_id', 'Dari Member'),
                                self::numberEntry('level', 'Level'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Pairing')
                    ->description('Bonus dari pasangan binary tree.')
                    ->schema([
                        RepeatableEntry::make('bonusPairings')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Tanggal Pairing'),
                                TableColumn::make('Jumlah Pairing'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::dateEntry('pairing_date', 'Tanggal Pairing'),
                                self::numberEntry('pairing_count', 'Jumlah Pairing'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Retail')
                    ->description('Bonus dari selisih harga jual.')
                    ->schema([
                        RepeatableEntry::make('bonusRetails')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Dari Member'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('from_member_id', 'Dari Member'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::text('description', 'Keterangan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Cashback')
                    ->description('Bonus cashback dari pembelian.')
                    ->schema([
                        RepeatableEntry::make('bonusCashbacks')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Order ID'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('order_id', 'Order ID'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::text('description', 'Keterangan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Reward')
                    ->description('Bonus reward promosi.')
                    ->schema([
                        RepeatableEntry::make('bonusRewards')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Tipe Reward'),
                                TableColumn::make('Reward'),
                                TableColumn::make('BV'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::text('reward_type', 'Tipe Reward'),
                                self::text('reward', 'Reward'),
                                self::numberEntry('bv', 'BV'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('Bonus Lifetime Cash Reward')
                    ->description('Bonus lifetime cash reward.')
                    ->schema([
                        RepeatableEntry::make('bonusLifetimeCashRewards')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Nama Reward'),
                                TableColumn::make('BV'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Status'),
                                TableColumn::make('Keterangan'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::text('reward_name', 'Nama Reward'),
                                self::numberEntry('bv', 'BV'),
                                self::moneyEntry('amount', 'Jumlah'),
                                self::statusEntry('status', 'Status'),
                                self::text('description', 'Keterangan'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),
            ]);
    }

    private static function rewardTab(): Tab
    {
        return Tab::make('Reward')
            ->schema([
                Section::make('Reward')
                    ->description('Reward yang telah diraih customer.')
                    ->schema([
                        RepeatableEntry::make('rewards')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Reward ID'),
                                TableColumn::make('Reward'),
                                TableColumn::make('Total BV'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Status'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('reward_id', 'Reward ID'),
                                self::text('reward', 'Reward'),
                                self::numberEntry('total_bv_achieved', 'Total BV'),
                                self::text('type', 'Tipe')->badge(),
                                self::statusEntry('status', 'Status'),
                                self::dateEntry('created_at', 'Dibuat'),
                            ]),
                    ]),

                Section::make('BV Reward')
                    ->description('Tracking BV reward customer.')
                    ->schema([
                        RepeatableEntry::make('bvRewards')
                            ->label('')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Reward ID'),
                                TableColumn::make('Omzet Kiri'),
                                TableColumn::make('Omzet Kanan'),
                                TableColumn::make('Status'),
                                TableColumn::make('Dibuat'),
                            ])
                            ->schema([
                                self::numberEntry('reward_id', 'Reward ID'),
                                self::moneyEntry('omzet_left', 'Omzet Kiri'),
                                self::moneyEntry('omzet_right', 'Omzet Kanan'),
                                self::statusEntry('status', 'Status'),
                                self::dateTimeEntry('created_on', 'Dibuat'),
                            ]),
                    ]),
            ]);
    }

    private static function text(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->placeholder('-');
    }

    private static function numberEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 0, ',', '.') : '-');
    }

    private static function moneyEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->formatStateUsing(fn ($state) => filled($state) ? 'Rp '.number_format((float) $state, 0, ',', '.') : '-');
    }

    private static function dateEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->date('d M Y')
            ->placeholder('-');
    }

    private static function dateTimeEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->dateTime('d M Y H:i')
            ->placeholder('-');
    }

    private static function statusEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->badge()
            ->formatStateUsing(fn ($state) => self::formatStatus($state))
            ->color(fn ($state) => self::statusColor($state))
            ->placeholder('-');
    }

    private static function formatStatus(mixed $state): string
    {
        if (blank($state)) {
            return '-';
        }

        if (is_numeric($state)) {
            return match ((int) $state) {
                1 => 'Prospek',
                2 => 'Pasif',
                3 => 'Aktif',
                default => (string) $state,
            };
        }

        return Str::headline(str_replace(['-', '_'], ' ', (string) $state));
    }

    private static function statusColor(mixed $state): string
    {
        $value = Str::lower(self::formatStatus($state));

        return match ($value) {
            'aktif', 'paid', 'success', 'approved', 'completed', 'settled' => 'success',
            'prospek', 'pending', 'processing', 'process' => 'warning',
            'pasif', 'failed', 'rejected', 'cancelled', 'expired', 'inactive' => 'danger',
            default => 'gray',
        };
    }
}
