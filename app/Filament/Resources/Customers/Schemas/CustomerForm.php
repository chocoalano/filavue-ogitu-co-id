<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use App\Services\RajaOngkirService;
use App\Support\CustomerUiSettingsConfig;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerForm
{
    private const MAX_CONTACT_USAGE = 7;

    /**
     * Mengonfigurasi skema form untuk Customer.
     */
    public static function configure(Schema $form): Schema
    {
        return $form->schema(self::formSchema());
    }

    /**
     * @return array<int, Tabs|Section>
     */
    private static function formSchema(): array
    {
        return [
            self::customerManagementTabs(),
            self::adminInternalLogSection(),
        ];
    }

    private static function customerManagementTabs(): Tabs
    {
        return Tabs::make('Customer Management')
            ->columnSpanFull()
            ->tabs([
                self::identityAddressTab(),
                self::networkTab(),
                self::financialTab(),
            ]);
    }

    private static function identityAddressTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Identitas & Alamat')
            ->icon('heroicon-o-user-circle')
            ->visible(fn (): bool => self::isFormSectionEnabled('profile_basic') || self::isFormSectionEnabled('addresses'))
            ->schema([
                self::profileBasicSection(),
                self::addressesSection(),
            ]);
    }

    private static function profileBasicSection(): Section
    {
        return Section::make('Informasi Profil Dasar')
            ->description('Data utama akun customer yang digunakan untuk identifikasi dan login.')
            ->columns(3)
            ->visible(fn (): bool => self::isFormSectionEnabled('profile_basic'))
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Masukkan nama lengkap sesuai identitas resmi untuk keperluan verifikasi.'),

                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaNum()
                    ->helperText('Gunakan kombinasi huruf dan angka tanpa spasi (Unique ID).'),

                TextInput::make('nik')
                    ->label('Nomor NIK')
                    ->required()
                    ->length(16)
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    ])
                    ->rule(self::maxUsageRule('nik'))
                    ->helperText('16 digit Nomor Induk Kependudukan sesuai KTP.'),

                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->rule(self::maxUsageRule('email'))
                    ->prefixIcon('heroicon-m-envelope')
                    ->helperText('Email aktif untuk korespondensi dan pemulihan akun.'),

                TextInput::make('phone')
                    ->label('Nomor Telepon/WA')
                    ->tel()
                    ->rule(self::maxUsageRule('phone'))
                    ->prefix('+62')
                    ->required()
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    ])
                    ->helperText('Masukkan nomor yang aktif di WhatsApp untuk notifikasi sistem.'),

                TextInput::make('password')
                    ->label('Password Akun')
                    ->password()
                    ->revealable()
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password saat ini.'),

                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->native(false)
                    ->helperText('Digunakan untuk personalisasi promo dan data demografi.'),

                DateTimePicker::make('email_verified_at')
                    ->label('Status Verifikasi Email')
                    ->placeholder('Belum terverifikasi')
                    ->helperText('Menunjukkan kapan user melakukan konfirmasi email.'),
            ]);
    }

    private static function addressesSection(): Section
    {
        return Section::make('Buku Alamat')
            ->description('Daftar lokasi pengiriman barang untuk customer ini.')
            ->visible(fn (): bool => self::isFormSectionEnabled('addresses'))
            ->schema([
                self::addressesRepeater(),
            ]);
    }

    private static function addressesRepeater(): Repeater
    {
        return Repeater::make('addresses')
            ->relationship('addresses')
            ->label('Lokasi Pengiriman')
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Alamat Baru')
            ->schema([
                TextInput::make('label')
                    ->label('Label Alamat')
                    ->placeholder('Contoh: Rumah, Kantor, Toko')
                    ->required()
                    ->helperText('Nama alias untuk membedakan antar alamat.'),

                Toggle::make('is_default')
                    ->label('Alamat Utama')
                    ->inline(false)
                    ->helperText('Aktifkan jika ini adalah alamat tujuan utama pengiriman.'),

                TextInput::make('recipient_name')
                    ->label('Nama Penerima')
                    ->required()
                    ->helperText('Nama orang yang berada di lokasi tujuan.'),

                TextInput::make('recipient_phone')
                    ->label('Kontak Penerima')
                    ->tel()
                    ->required()
                    ->helperText('Nomor telepon yang dapat dihubungi oleh pihak ekspedisi.'),

                Textarea::make('address_line1')
                    ->label('Detail Alamat')
                    ->placeholder('Nama jalan, No. Rumah, RT/RW, Komplek, dll.')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Tuliskan alamat selengkap mungkin agar mudah ditemukan kurir.'),

                Select::make('province_id')
                    ->label('Provinsi')
                    ->options(fn (): array => self::provinceOptions())
                    ->searchable()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $selectedId = self::normalizeRegionId($state);
                        $provinceLabel = $selectedId ? (self::provinceOptions()[$selectedId] ?? null) : null;
                        $set('province_label', self::toUppercaseLabel($provinceLabel));

                        $set('city_id', null);
                        $set('city_label', null);
                        $set('district', null);
                    })
                    ->helperText('Pilih provinsi tujuan terlebih dahulu.'),

                Select::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->options(fn (Get $get): array => self::cityOptions($get('province_id')))
                    ->searchable()
                    ->live()
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('province_id')))
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $selectedId = self::normalizeRegionId($state);
                        $cityOptions = self::cityOptions($get('province_id'));
                        $cityLabel = $selectedId ? ($cityOptions[$selectedId] ?? null) : null;
                        $set('city_label', self::toUppercaseLabel($cityLabel));

                        $set('district', null);
                    })
                    ->helperText('Daftar kota akan muncul setelah provinsi dipilih.'),

                Select::make('district')
                    ->label('Kecamatan')
                    ->options(fn (Get $get): array => self::districtOptions($get('city_id')))
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('city_id')))
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('district_lion', self::toUppercaseLabel($state));
                    })
                    ->helperText('Wajib diisi untuk perhitungan ongkir yang akurat.'),

                TextInput::make('postal_code')
                    ->label('Kode Pos')
                    ->numeric()
                    ->length(5)
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    ])
                    ->helperText('5 digit kode wilayah pos.'),

                Textarea::make('description')
                    ->label('Catatan Kurir')
                    ->placeholder('Contoh: Pagar hitam, dekat warung Padang...')
                    ->rows(2)
                    ->columnSpanFull(),

                Hidden::make('province_label'),
                Hidden::make('city_label'),
                Hidden::make('district_lion'),
            ])
            ->columns(2)
            ->defaultItems(1)
            ->addActionLabel('Tambahkan Alamat Lain')
            ->columnSpanFull();
    }

    private static function networkTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Jaringan')
            ->icon('heroicon-o-share')
            ->visible(fn (): bool => self::isFormSectionEnabled('network_affiliation'))
            ->schema([
                self::networkAffiliationSection(),
            ]);
    }

    private static function networkAffiliationSection(): Section
    {
        return Section::make('Sponsor & Afiliasi')
            ->columns(3)
            ->visible(fn (): bool => self::isFormSectionEnabled('network_affiliation'))
            ->schema([
                Select::make('sponsor_id')
                    ->label('Sponsor')
                    ->relationship('sponsor', 'username')
                    ->searchable()
                    ->preload()
                    ->helperText('Member yang mereferensikan pendaftaran akun ini.'),

                Select::make('package_id')
                    ->label('Paket Bergabung')
                    ->relationship('package', 'name')
                    ->required()
                    ->helperText('Lisensi paket bisnis yang dibeli saat pendaftaran.'),

                Select::make('level')
                    ->label('Peringkat Karier')
                    ->options([
                        'Associate' => 'Associate',
                        'Senior Associate' => 'Senior Associate',
                        'Executive' => 'Executive',
                        'Director' => 'Director',
                    ])
                    ->helperText('Status jenjang karier member berdasarkan omzet/prestasi.'),

                ToggleButtons::make('status')
                    ->label('Status member berdasarkan jaringan.')
                    ->options(self::statusFieldOptions())
                    ->required()
                    ->default(1)
                    ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                    ->rules(self::statusFieldRules())
                    ->grouped(),
            ]);
    }

    private static function financialTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Finansial')
            ->icon('heroicon-o-banknotes')
            ->visible(fn (): bool => self::isFormSectionEnabled('financial_bank') || self::isFormSectionEnabled('stockist_authority'))
            ->schema([
                self::financialBankSection(),
                self::stockistAuthoritySection(),
            ]);
    }

    private static function financialBankSection(): Section
    {
        return Section::make('Data Rekening Transfer')
            ->description('Informasi bank digunakan untuk pencairan (Withdrawal) bonus.')
            ->columns(2)
            ->visible(fn (): bool => self::isFormSectionEnabled('financial_bank'))
            ->schema([
                TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->placeholder('Contoh: BCA / Mandiri / BNI')
                    ->helperText('Gunakan nama resmi bank.'),

                TextInput::make('bank_account')
                    ->label('Nomor Rekening')
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    ])
                    ->helperText('Pastikan nomor rekening sesuai dengan nama pemilik akun.'),
            ]);
    }

    private static function stockistAuthoritySection(): Section
    {
        return Section::make('Otoritas Stockist')
            ->description('Akses khusus jika member ditunjuk sebagai distributor wilayah.')
            ->columns(3)
            ->visible(fn (): bool => self::isFormSectionEnabled('stockist_authority'))
            ->schema([
                Toggle::make('is_stockist')
                    ->label('Status Stockist Aktif')
                    ->live()
                    ->helperText('Aktifkan jika member ini berperan sebagai gudang distribusi.'),

                Select::make('stockist_province_id')
                    ->label('Provinsi Stockist')
                    ->options(fn (): array => self::provinceOptions())
                    ->searchable()
                    ->live()
                    ->required(fn (Get $get): bool => (bool) $get('is_stockist'))
                    ->disabled(fn (Get $get): bool => ! (bool) $get('is_stockist'))
                    ->afterStateUpdated(function (Set $set, $state) {
                        $selectedId = self::normalizeRegionId($state);
                        $provinceLabel = $selectedId ? (self::provinceOptions()[$selectedId] ?? null) : null;
                        $set('stockist_province_name', self::toUppercaseLabel($provinceLabel));
                        $set('stockist_kabupaten_id', null);
                    })
                    ->helperText('Wilayah provinsi penempatan stok.'),

                Select::make('stockist_kabupaten_id')
                    ->label('Kota/Kabupaten Stockist')
                    ->options(fn (Get $get): array => self::cityOptions($get('stockist_province_id')))
                    ->searchable()
                    ->live()
                    ->required(fn (Get $get): bool => (bool) $get('is_stockist'))
                    ->disabled(fn (Get $get): bool => ! (bool) $get('is_stockist') || blank($get('stockist_province_id')))
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $selectedId = self::normalizeRegionId($state);
                        $cityOptions = self::cityOptions($get('stockist_province_id'));
                        $cityLabel = $selectedId ? ($cityOptions[$selectedId] ?? null) : null;
                        $set('stockist_kabupaten_name', self::toUppercaseLabel($cityLabel));
                    })
                    ->helperText('Cakupan kota operasional distributor.'),

                Hidden::make('stockist_province_name'),
                Hidden::make('stockist_kabupaten_name'),
            ]);
    }

    private static function adminInternalLogSection(): Section
    {
        return Section::make('Admin Internal Log')
            ->collapsed()
            ->visible(fn (): bool => self::isFormSectionEnabled('admin_internal_log'))
            ->schema([
                Textarea::make('description')
                    ->label('Catatan Khusus Admin')
                    ->rows(3)
                    ->placeholder('Tuliskan catatan internal di sini...')
                    ->helperText('Catatan ini hanya dapat dilihat oleh admin dan tidak akan muncul di panel member.'),
            ]);
    }

    private static function isFormSectionEnabled(string $key): bool
    {
        $settings = CustomerUiSettingsConfig::formSectionSettings();

        return $settings[$key] ?? true;
    }

    /**
     * @return array<int, string>
     */
    private static function statusFieldOptions(): array
    {
        $labels = CustomerUiSettingsConfig::statusLabels();

        if ($labels === []) {
            return [
                1 => 'Prospek',
                2 => 'Pasif',
                3 => 'Aktif',
            ];
        }

        return $labels;
    }

    /**
     * @return array<int, string|ValidationRule>
     */
    private static function statusFieldRules(): array
    {
        return [
            'required',
            'integer',
            Rule::in(array_keys(self::statusFieldOptions())),
        ];
    }

    // -------------------------------------------------------------------------
    // PRIVATE UTILITIES (RajaOngkir Logic)
    // -------------------------------------------------------------------------

    private static function provinceOptions(): array
    {
        return Cache::remember('customer_form:provinces', now()->addHours(12), function () {
            $provinces = app(RajaOngkirService::class)->getProvinces();
            $options = [];
            foreach ($provinces as $province) {
                $id = self::extractRajaOngkirValue($province, ['id', 'province_id']);
                $name = self::extractRajaOngkirValue($province, ['province_name', 'province', 'name']);
                if ($id && $name) {
                    $options[(string) $id] = Str::upper($name);
                }
            }

            return $options;
        });
    }

    private static function cityOptions(mixed $provinceId): array
    {
        $provinceId = self::normalizeRegionId($provinceId);
        if (! $provinceId) {
            return [];
        }

        return Cache::remember("customer_form:cities:{$provinceId}", now()->addHours(12), function () use ($provinceId) {
            $cities = app(RajaOngkirService::class)->getCities((int) $provinceId);
            $options = [];
            foreach ($cities as $city) {
                $id = self::extractRajaOngkirValue($city, ['id', 'city_id']);
                $name = self::extractRajaOngkirValue($city, ['city_name', 'city', 'name']);
                $type = self::extractRajaOngkirValue($city, ['type']);
                if ($id && $name) {
                    $label = $type ? "{$type} {$name}" : $name;
                    $options[(string) $id] = Str::upper($label);
                }
            }

            return $options;
        });
    }

    private static function districtOptions(mixed $cityId): array
    {
        $cityId = self::normalizeRegionId($cityId);
        if (! $cityId) {
            return [];
        }

        return Cache::remember("customer_form:districts:{$cityId}", now()->addHours(12), function () use ($cityId) {
            $districts = app(RajaOngkirService::class)->getDistricts((int) $cityId);
            $options = [];
            foreach ($districts as $district) {
                $name = self::extractRajaOngkirValue($district, ['district_name', 'subdistrict_name', 'name']);
                if ($name) {
                    $options[Str::upper($name)] = Str::upper($name);
                }
            }

            return $options;
        });
    }

    private static function normalizeRegionId(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }

    private static function extractRajaOngkirValue(mixed $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (is_array($row) && isset($row[$key])) {
                return $row[$key];
            }
            if (is_object($row) && isset($row->{$key})) {
                return $row->{$key};
            }
        }

        return null;
    }

    private static function toUppercaseLabel(mixed $label): ?string
    {
        return filled($label) ? Str::upper(trim((string) $label)) : null;
    }

    private static function maxUsageRule(string $column): Closure
    {
        return static function (Field $component) use ($column): Closure {
            return static function (string $attribute, mixed $value, Closure $fail) use ($column, $component): void {
                $normalizedValue = self::normalizeUsageValue($column, $value);

                if (blank($normalizedValue)) {
                    return;
                }

                $record = $component->getRecord();
                $currentValue = self::normalizeUsageValue(
                    $column,
                    $record?->{$column},
                );

                if (
                    $record !== null &&
                    $record->exists &&
                    $currentValue === $normalizedValue
                ) {
                    return;
                }

                $usageCount = Customer::query()
                    ->when(
                        $column === 'email',
                        fn ($query) => $query->whereRaw('LOWER(email) = ?', [Str::lower($normalizedValue)]),
                        fn ($query) => $query->where($column, $normalizedValue)
                    )
                    ->when(
                        $record !== null && $record->exists,
                        fn ($query) => $query->whereKeyNot($record)
                    )
                    ->count();

                if ($usageCount >= self::MAX_CONTACT_USAGE) {
                    $label = match ($column) {
                        'email' => 'Email',
                        'nik' => 'NIK',
                        default => 'Nomor telepon/WhatsApp',
                    };
                    $fail("{$label} ini sudah digunakan oleh ".self::MAX_CONTACT_USAGE.' akun.');
                }
            };
        };
    }

    private static function normalizeUsageValue(string $column, mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            return null;
        }

        if ($column === 'email') {
            return Str::lower($normalized);
        }

        if ($column === 'nik') {
            return preg_replace('/\D+/', '', $normalized) ?: null;
        }

        return preg_replace('/\s+/', '', $normalized) ?: null;
    }
}
