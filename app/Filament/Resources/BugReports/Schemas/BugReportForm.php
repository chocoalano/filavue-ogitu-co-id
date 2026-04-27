<?php

namespace App\Filament\Resources\BugReports\Schemas;

use App\Enums\BugErrorCategory;
use App\Enums\BugPriority;
use App\Enums\BugReporterType;
use App\Enums\BugSeverity;
use App\Enums\BugSource;
use App\Enums\BugStatus;
use App\Enums\MobileType;
use App\Enums\Platform;
use App\Enums\WebScreen;
use App\Filament\Resources\BugReports\BugReportResource;
use App\Models\BugReport;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BugReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            BugReportResource::makeAutoResolveCallout(),

            Tabs::make('Bug Report')
                ->columnSpanFull()
                ->tabs([
                    self::tabPlatform(),
                    self::tabDetail(),
                    self::tabAttachments(),
                    self::tabReporter(),
                    self::tabEnvironment(),
                    self::tabTriage(),
                ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABS
    // ─────────────────────────────────────────────────────────────────────────

    private static function tabPlatform(): Tab
    {
        return Tab::make('Platform & Sumber')
            ->icon('heroicon-o-computer-desktop')
            ->schema([
                Section::make('Konteks Platform')
                    ->description('Dari mana dan perangkat apa yang digunakan saat bug terjadi.')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->columns(12)
                    ->schema([
                        ToggleButtons::make('platform')
                            ->label('Platform')
                            ->options(Platform::class)
                            ->default(fn (): ?string => self::defaultPlatform())
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih platform yang digunakan saat bug terjadi, misalnya Web atau Mobile.',
                            ])
                            ->inline()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if ($state !== Platform::Web->value) {
                                    return;
                                }

                                if (blank($get('source')) && filled($source = self::defaultSource())) {
                                    $set('source', $source);
                                }

                                if (blank($get('page_url')) && filled($pageUrl = self::defaultPageUrl())) {
                                    $set('page_url', $pageUrl);
                                }

                                if (blank($get('browser')) && filled($browser = self::detectedBrowserName())) {
                                    $set('browser', $browser);
                                }

                                if (blank($get('browser_version')) && filled($browserVersion = self::detectedBrowserVersion())) {
                                    $set('browser_version', $browserVersion);
                                }

                                if (blank($get('os')) && filled($osName = self::detectedOsName())) {
                                    $set('os', $osName);
                                }

                                if (blank($get('os_version')) && filled($osVersion = self::detectedOsVersion())) {
                                    $set('os_version', $osVersion);
                                }
                            })
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        ToggleButtons::make('source')
                            ->label('Sumber Aplikasi')
                            ->options(BugSource::class)
                            ->default(fn (): ?string => self::defaultSource())
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih sumber aplikasi tempat bug ditemukan, misalnya Storefront atau Admin Console.',
                            ])
                            ->inline()
                            ->live()
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        Select::make('web_screen')
                            ->label('Ukuran Layar Web')
                            ->options(WebScreen::class)
                            ->placeholder('Pilih ukuran layar...')
                            ->helperText('Pilih ukuran layar browser saat bug terjadi.')
                            ->visible(fn (Get $get): bool => $get('platform') === Platform::Web->value)
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        Select::make('mobile_type')
                            ->label('Sistem Operasi Mobile')
                            ->options(MobileType::class)
                            ->default(fn (): ?string => self::detectedMobileType())
                            ->placeholder('Pilih OS...')
                            ->helperText('Pilih sistem operasi perangkat mobile.')
                            ->visible(fn (Get $get): bool => $get('platform') === Platform::Mobile->value)
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        // ── URL Halaman ───────────────────────────────────────
                        Select::make('page_url')
                            ->label('URL Halaman')
                            ->searchable()
                            ->preload()
                            ->options(fn (Get $get): array => self::searchPageUrls('', $get('source')))
                            ->default(fn (): ?string => self::defaultPageUrl())
                            ->validationMessages([
                                'required' => 'URL halaman wajib diisi untuk laporan bug dari platform web. Pilih halaman yang bermasalah atau masukkan URL lengkapnya.',
                            ])
                            ->getSearchResultsUsing(
                                fn (?string $search, Get $get): array => self::searchPageUrls($search, $get('source'))
                            )
                            ->getOptionLabelUsing(fn (mixed $value): string => self::pageUrlOptionLabel($value))
                            ->wrapOptionLabels()
                            ->placeholder('Cari halaman atau ketik URL lengkap...')
                            ->helperText(
                                fn (Get $get): string => match ($get('source')) {
                                    BugSource::Storefront->value => $get('platform') === Platform::Web->value
                                        ? 'Wajib diisi untuk bug dari platform web. Ketik path, nama halaman, atau URL storefront lengkap.'
                                        : 'Opsional untuk platform mobile. Ketik path, nama halaman, atau URL storefront jika relevan.',
                                    BugSource::AdminConsole->value => $get('platform') === Platform::Web->value
                                        ? 'Wajib diisi untuk bug dari platform web. Ketik path, nama halaman, atau URL Admin Console lengkap.'
                                        : 'Opsional untuk platform mobile. Ketik path, nama halaman, atau URL Admin Console jika relevan.',
                                    default => $get('platform') === Platform::Web->value
                                        ? 'Wajib diisi untuk bug dari platform web. Pilih Sumber Aplikasi terlebih dahulu untuk memfilter daftar halaman.'
                                        : 'Pilih Sumber Aplikasi terlebih dahulu untuk memfilter daftar halaman.',
                                }
                            )
                            ->required(fn (Get $get): bool => $get('platform') === Platform::Web->value)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function tabDetail(): Tab
    {
        return Tab::make('Detail Bug')
            ->icon('heroicon-o-bug-ant')
            ->schema([
                Section::make('Deskripsi Bug')
                    ->description('Jelaskan bug yang ditemukan sejelas mungkin.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Bug')
                            ->required()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? (string) Str::of($state)->squish() : $state)
                            ->validationMessages([
                                'required' => 'Judul bug wajib diisi agar tim bisa cepat memahami inti masalahnya.',
                                'max' => 'Judul bug terlalu panjang. Gunakan ringkasan singkat, maksimal 255 karakter.',
                            ])
                            ->placeholder('Contoh: Tombol checkout tidak bisa diklik di halaman keranjang')
                            ->helperText('Ringkasan singkat bug dalam satu kalimat. Judul yang sama tidak bisa diajukan ulang agar tidak terjadi duplikat laporan.')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->validationMessages([
                                'required' => 'Deskripsi bug wajib diisi. Jelaskan masalah yang terjadi dengan singkat tetapi jelas.',
                            ])
                            ->rows(4)
                            ->placeholder('Jelaskan secara detail apa yang terjadi...')
                            ->helperText('Deskripsi lengkap mengenai bug yang ditemukan.')
                            ->columnSpanFull(),

                        Textarea::make('steps_to_reproduce')
                            ->label('Langkah Reproduksi')
                            ->required()
                            ->validationMessages([
                                'required' => 'Langkah reproduksi wajib diisi agar tim bisa mengikuti alur yang sama saat mencoba memunculkan bug ini.',
                            ])
                            ->rows(4)
                            ->placeholder("1. Buka halaman keranjang\n2. Klik tombol checkout\n3. ...")
                            ->helperText('Wajib diisi. Tuliskan langkah-langkah yang dapat diikuti untuk memunculkan bug yang sama.')
                            ->columnSpanFull(),

                        Textarea::make('expected_behavior')
                            ->label('Perilaku yang Diharapkan')
                            ->required()
                            ->validationMessages([
                                'required' => 'Perilaku yang diharapkan wajib diisi agar tim bisa membandingkan hasil yang seharusnya dengan yang benar-benar terjadi.',
                            ])
                            ->rows(3)
                            ->placeholder('Seharusnya berpindah ke halaman pembayaran...')
                            ->helperText('Apa yang seharusnya terjadi setelah langkah tersebut.')
                            ->columnSpanFull(),

                        Textarea::make('actual_behavior')
                            ->label('Perilaku yang Terjadi')
                            ->required()
                            ->validationMessages([
                                'required' => 'Perilaku yang terjadi wajib diisi agar tim bisa melihat hasil aktual yang bermasalah.',
                            ])
                            ->rows(3)
                            ->placeholder('Tombol tidak merespons / halaman error...')
                            ->helperText('Apa yang sebenarnya terjadi (bukan yang diharapkan).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function tabAttachments(): Tab
    {
        return Tab::make('Lampiran')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Section::make('Lampiran Bug')
                    ->description('Tambahkan screenshot atau rekaman video untuk membantu tim mereproduksi masalah.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Repeater::make('attachments')
                            ->relationship('attachments')
                            ->defaultItems(0)
                            ->minItems(1)
                            ->validationMessages([
                                'min' => 'Minimal tambahkan satu lampiran agar tim dapat melihat bukti visual dari bug yang dilaporkan.',
                            ])
                            ->addActionLabel('Tambah Lampiran')
                            ->helperText('Minimal satu lampiran wajib ditambahkan. Setiap item berisi satu file gambar atau video beserta keterangannya.')
                            ->itemLabel(fn (array $state): ?string => $state['caption'] ?? $state['file_name'] ?? 'Lampiran Baru')
                            ->collapsed()
                            ->collapsible()
                            ->columns(12)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::mutateAttachmentData($data))
                            ->mutateRelationshipDataBeforeSaveUsing(
                                fn (array $data, Model $record): array => self::mutateAttachmentData($data, $record)
                            )
                            ->schema([
                                Hidden::make('file_name'),
                                Hidden::make('mime_type'),
                                Hidden::make('file_size'),
                                FileUpload::make('file_path')
                                    ->label('File Lampiran')
                                    ->disk('public')
                                    ->directory('bug-reports/attachments')
                                    ->visibility('public')
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        'image/gif',
                                        'video/mp4',
                                        'video/webm',
                                        'video/quicktime',
                                        'video/x-msvideo',
                                    ])
                                    ->storeFileNamesIn('file_name')
                                    ->openable()
                                    ->downloadable()
                                    ->maxSize(51200)
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'File lampiran wajib dipilih. Upload gambar atau video yang menunjukkan bug secara jelas.',
                                        'mimetypes' => 'Lampiran harus berupa gambar atau video yang didukung, misalnya JPG, PNG, WebP, GIF, MP4, MOV, WebM, atau AVI.',
                                        'max' => 'Ukuran lampiran terlalu besar. Maksimal 50 MB per file.',
                                    ])
                                    ->helperText('Format yang didukung: JPG, PNG, WebP, GIF, MP4, MOV, WebM, AVI. Maksimal 50 MB.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 7,
                                    ]),
                                Textarea::make('caption')
                                    ->label('Keterangan Lampiran')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Keterangan lampiran wajib diisi agar tim memahami isi gambar atau video yang diunggah.',
                                        'max' => 'Keterangan lampiran terlalu panjang. Maksimal 255 karakter.',
                                    ])
                                    ->placeholder('Contoh: Tampilan error setelah klik tombol checkout')
                                    ->helperText('Wajib diisi. Jelaskan secara singkat apa yang terlihat pada gambar atau video.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 5,
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function tabReporter(): Tab
    {
        return Tab::make('Pelapor')
            ->icon('heroicon-o-user')
            ->schema([
                Section::make('Identitas Pelapor')
                    ->description('Informasi orang yang melaporkan bug ini.')
                    ->icon('heroicon-o-identification')
                    ->columns(12)
                    ->schema([
                        ToggleButtons::make('reporter_type')
                            ->label('Tipe Pelapor')
                            ->options(BugReporterType::class)
                            ->default(BugReporterType::User)
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih siapa yang melaporkan bug ini: customer, user internal, atau anonymous.',
                            ])
                            ->inline()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === BugReporterType::User->value) {
                                    $set('reporter_id', self::currentUserId());
                                }

                                if ($state !== BugReporterType::User->value) {
                                    $set('reporter_id', null);
                                }

                                if ($state !== BugReporterType::Anonymous->value) {
                                    $set('reporter_name', null);
                                    $set('reporter_email', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('reporter_id')
                            ->label('Customer')
                            ->options(Customer::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Customer->value)
                            ->validationMessages([
                                'required' => 'Pilih customer yang melaporkan bug ini.',
                            ])
                            ->placeholder('Cari customer...')
                            ->helperText('Pilih customer yang melaporkan bug.')
                            ->visible(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Customer->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        Select::make('reporter_id')
                            ->label('User Internal')
                            ->options(User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => self::currentUserId())
                            ->required(fn (Get $get): bool => $get('reporter_type') === BugReporterType::User->value)
                            ->validationMessages([
                                'required' => 'Pilih user internal yang melaporkan bug ini.',
                            ])
                            ->placeholder('Cari user...')
                            ->helperText('Pilih user internal yang melaporkan bug.')
                            ->visible(fn (Get $get): bool => $get('reporter_type') === BugReporterType::User->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        TextInput::make('reporter_name')
                            ->label('Nama Pelapor')
                            ->required(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Anonymous->value)
                            ->validationMessages([
                                'required' => 'Nama pelapor wajib diisi jika laporan dibuat tanpa akun.',
                                'max' => 'Nama pelapor terlalu panjang. Maksimal 100 karakter.',
                            ])
                            ->placeholder('Nama lengkap...')
                            ->helperText('Nama pelapor yang tidak memiliki akun.')
                            ->maxLength(100)
                            ->visible(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Anonymous->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        TextInput::make('reporter_email')
                            ->label('Email Pelapor')
                            ->email()
                            ->required(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Anonymous->value)
                            ->validationMessages([
                                'required' => 'Email pelapor wajib diisi jika laporan dibuat tanpa akun.',
                                'email' => 'Format email pelapor belum benar. Contohnya: nama@domain.com.',
                                'max' => 'Email pelapor terlalu panjang. Maksimal 150 karakter.',
                            ])
                            ->placeholder('email@contoh.com')
                            ->helperText('Email untuk menghubungi pelapor kembali.')
                            ->maxLength(150)
                            ->visible(fn (Get $get): bool => $get('reporter_type') === BugReporterType::Anonymous->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),
                    ]),
            ]);
    }

    private static function tabEnvironment(): Tab
    {
        return Tab::make('Lingkungan')
            ->icon('heroicon-o-cpu-chip')
            ->schema([
                Section::make('Browser & Sistem Operasi')
                    ->description('Informasi teknis lingkungan saat bug terjadi.')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(12)
                    ->schema([
                        // ── Browser (required jika platform = web) ────────────
                        Select::make('browser')
                            ->label('Browser')
                            ->options(self::browserOptions())
                            ->searchable()
                            ->default(fn (): ?string => self::detectedBrowserName())
                            ->validationMessages([
                                'required' => 'Browser wajib diisi untuk laporan bug dari platform web.',
                            ])
                            ->placeholder('Chrome, Firefox, Safari...')
                            ->helperText(fn (Get $get): string => $get('platform') === Platform::Web->value
                                ? 'Wajib diisi untuk bug dari platform web.'
                                : 'Opsional untuk bug dari platform mobile.'
                            )
                            ->required(fn (Get $get): bool => $get('platform') === Platform::Web->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        TextInput::make('browser_version')
                            ->label('Versi Browser')
                            ->default(fn (): ?string => self::detectedBrowserVersion())
                            ->validationMessages([
                                'required' => 'Versi browser wajib diisi untuk laporan bug dari platform web.',
                                'max' => 'Versi browser terlalu panjang. Maksimal 30 karakter.',
                            ])
                            ->placeholder('120.0.6099.130')
                            ->maxLength(30)
                            ->helperText(fn (Get $get): string => $get('platform') === Platform::Web->value
                                ? 'Wajib diisi untuk bug dari platform web. Cek di About Browser.'
                                : 'Versi browser jika relevan.'
                            )
                            ->required(fn (Get $get): bool => $get('platform') === Platform::Web->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        // ── Sistem Operasi ────────────────────────────────────
                        Select::make('os')
                            ->label('Sistem Operasi')
                            ->options(self::osOptions())
                            ->searchable()
                            ->default(fn (): ?string => self::detectedOsName())
                            ->placeholder('Windows 11, macOS 14, Android 14...')
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        TextInput::make('os_version')
                            ->label('Versi OS')
                            ->default(fn (): ?string => self::detectedOsVersion())
                            ->validationMessages([
                                'max' => 'Versi sistem operasi terlalu panjang. Maksimal 30 karakter.',
                            ])
                            ->placeholder('11.0, 14.1, 14...')
                            ->maxLength(30)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        // ── Perangkat Mobile (visible & required jika mobile) ─
                        TextInput::make('device_model')
                            ->label('Model Perangkat')
                            ->validationMessages([
                                'required' => 'Model perangkat wajib diisi untuk laporan bug dari platform mobile.',
                                'max' => 'Nama model perangkat terlalu panjang. Maksimal 100 karakter.',
                            ])
                            ->placeholder('Samsung Galaxy S24, iPhone 15 Pro...')
                            ->maxLength(100)
                            ->helperText(fn (Get $get): string => $get('platform') === Platform::Mobile->value
                                ? 'Wajib diisi untuk bug dari platform mobile.'
                                : 'Diisi jika bug terjadi pada perangkat tertentu.'
                            )
                            ->required(fn (Get $get): bool => $get('platform') === Platform::Mobile->value)
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        // ── Layar & Versi Aplikasi ─────────────────────────────
                        TextInput::make('screen_resolution')
                            ->label('Resolusi Layar')
                            ->rule(static fn (): \Closure => static function (string $attribute, mixed $value, \Closure $fail): void {
                                if (blank($value)) {
                                    return;
                                }

                                if (! preg_match('/^\d{2,5}x\d{2,5}$/', (string) $value)) {
                                    $fail('Format resolusi layar belum benar. Gunakan format seperti 1920x1080 atau 390x844.');
                                }
                            })
                            ->validationMessages([
                                'max' => 'Resolusi layar terlalu panjang. Maksimal 20 karakter.',
                            ])
                            ->placeholder('1920x1080')
                            ->helperText('Format: LebarxTinggi. Contoh: 1920x1080, 390x844.')
                            ->maxLength(20)
                            ->columnSpan(['default' => 12, 'md' => 3]),

                        TextInput::make('app_version')
                            ->label('Versi Aplikasi')
                            ->validationMessages([
                                'max' => 'Versi aplikasi terlalu panjang. Maksimal 30 karakter.',
                            ])
                            ->placeholder('1.3.1')
                            ->helperText('Versi aplikasi saat bug terjadi. Cek di footer atau settings.')
                            ->maxLength(30)
                            ->columnSpan(['default' => 12, 'md' => 3]),
                    ]),
            ]);
    }

    private static function tabTriage(): Tab
    {
        return Tab::make('Triase & Penugasan')
            ->icon('heroicon-o-adjustments-horizontal')
            ->schema([
                Section::make('Kategorisasi')
                    ->description('Tentukan severity, prioritas, dan kategori akar masalah.')
                    ->icon('heroicon-o-tag')
                    ->columns(12)
                    ->schema([
                        ToggleButtons::make('severity')
                            ->label('Severity')
                            ->options(BugSeverity::class)
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih tingkat keparahan bug agar tim tahu seberapa besar dampaknya.',
                            ])
                            ->default(BugSeverity::Medium)
                            ->inline()
                            ->columnSpanFull(),

                        ToggleButtons::make('priority')
                            ->label('Prioritas')
                            ->options(BugPriority::class)
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih prioritas penanganan supaya bug bisa dikerjakan sesuai urgensinya.',
                            ])
                            ->default(BugPriority::Medium)
                            ->inline()
                            ->columnSpanFull(),

                        Select::make('error_category')
                            ->label('Kategori Error')
                            ->options(BugErrorCategory::class)
                            ->required()
                            ->validationMessages([
                                'required' => 'Kategori error wajib diisi agar akar masalah bug bisa langsung ditriase dengan jelas.',
                            ])
                            ->placeholder('Belum dikategorikan...')
                            ->helperText('Wajib diisi. Pilih kategori akar masalah yang paling mendekati hasil triase awal.')
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        Select::make('status')
                            ->label('Status')
                            ->options(fn (?BugReport $record): array => self::statusOptions($record))
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih status laporan bug saat ini.',
                            ])
                            ->default(BugStatus::Open)
                            ->live()
                            ->helperText(fn (?BugReport $record): string => self::statusHelperText($record))
                            ->afterStateUpdated(function (?string $state, Set $set, ?BugReport $record): void {
                                if (! filled($record)) {
                                    return;
                                }

                                if (
                                    $state === BugStatus::Closed->value
                                    && self::canConfirmClosure($record)
                                    && blank($record->closed_at)
                                ) {
                                    $set('closed_at', now());
                                }
                            })
                            ->columnSpan(['default' => 12, 'md' => 6]),
                    ]),

                Section::make('Penugasan')
                    ->description('Assign bug ke developer dan tandai jika duplikat.')
                    ->icon('heroicon-o-user-plus')
                    ->columns(12)
                    ->schema([
                        Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->relationship(
                                name: 'assignee',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->where('role', 'developer')
                                    ->orderBy('name'),
                            )
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih user yang ditugaskan untuk menangani laporan bug ini.',
                            ])
                            ->helperText('Wajib diisi. User yang dipilih di sini bertanggung jawab melakukan penanganan dan mengisi data resolusi.')
                            ->placeholder('Belum ditugaskan...')
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        Select::make('duplicate_of_id')
                            ->label('Duplikat Dari')
                            ->options(BugReport::query()->pluck('title', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('status') === BugStatus::Duplicate->value || filled($get('duplicate_of_id')))
                            ->required(fn (Get $get): bool => $get('status') === BugStatus::Duplicate->value)
                            ->validationMessages([
                                'required' => 'Pilih laporan bug induk karena status saat ini ditandai sebagai duplikat.',
                            ])
                            ->placeholder('Bukan duplikat...')
                            ->helperText('Wajib diisi jika status laporan ditandai sebagai duplikat.')
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        DateTimePicker::make('closed_at')
                            ->label('Waktu Closed')
                            ->visible(fn (?BugReport $record): bool => filled($record) && (self::canConfirmClosure($record) || filled($record->closed_at)))
                            ->disabled(fn (?BugReport $record): bool => ! self::canConfirmClosure($record))
                            ->dehydrated(fn (?BugReport $record): bool => self::canConfirmClosure($record))
                            ->required(fn (Get $get, ?BugReport $record): bool => $get('status') === BugStatus::Closed->value && self::canConfirmClosure($record))
                            ->validationMessages([
                                'required' => 'Waktu closed wajib diisi oleh user yang mengajukan laporan saat bug dikonfirmasi selesai.',
                            ])
                            ->seconds(false)
                            ->helperText(fn (?BugReport $record): string => self::canConfirmClosure($record)
                                ? 'Diisi oleh pelapor saat sudah mengonfirmasi bahwa bug benar-benar selesai.'
                                : 'Waktu closed hanya diisi oleh user yang membuat laporan bug ini sebagai tanda konfirmasi selesai.'
                            )
                            ->columnSpan(['default' => 12, 'md' => 6]),
                    ]),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS: Page URL Search
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cari URL halaman berdasarkan keyword dan sumber aplikasi.
     * Mengembalikan [absolute_url => display_label] untuk Select searchable.
     *
     * @return array<string, string>
     */
    private static function searchPageUrls(?string $search, BugSource|string|null $source): array
    {
        $search = trim((string) $search);
        $needle = strtolower($search);

        $routes = collect(self::pageUrlCandidates($source))
            ->filter(function (array $route) use ($needle): bool {
                if ($needle === '') {
                    return true;
                }

                return str_contains(strtolower($route['uri']), $needle)
                    || str_contains(strtolower($route['name']), $needle)
                    || str_contains(strtolower($route['label']), $needle);
            })
            ->take(60)
            ->mapWithKeys(fn (array $route): array => [
                $route['url'] => $route['label'],
            ])
            ->all();

        // Izinkan input URL kustom jika user mengetik URL langsung
        if (
            $search !== ''
            && ! isset($routes[$search])
            && (str_starts_with($search, 'http') || str_starts_with($search, '/'))
        ) {
            $routes[$search] = '🔗 '.Str::limit($search, 90).' (URL kustom)';
        }

        return $routes;
    }

    /**
     * Daftar route untuk opsi URL halaman berdasarkan sumber aplikasi.
     *
     * @return array<int, array{uri: string, name: string, label: string, url: string}>
     */
    private static function pageUrlCandidates(BugSource|string|null $source): array
    {
        static $cache = [];

        $sourceValue = $source instanceof BugSource ? $source->value : (is_string($source) ? $source : null);
        $sourceKey = $sourceValue === BugSource::AdminConsole->value
            ? BugSource::AdminConsole->value
            : BugSource::Storefront->value;

        if (isset($cache[$sourceKey])) {
            return $cache[$sourceKey];
        }

        $isAdmin = $sourceKey === BugSource::AdminConsole->value;

        $cache[$sourceKey] = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route): bool => \in_array('GET', $route->methods(), true))
            ->map(function ($route): array {
                $uri = (string) $route->uri();
                $name = (string) ($route->getName() ?? '');

                return [
                    'uri' => $uri,
                    'name' => $name,
                    'label' => self::routeDisplayLabel($uri, $name),
                    'url' => url($uri),
                ];
            })
            ->filter(fn (array $route): bool => ! str_contains($route['uri'], '{'))
            ->filter(fn (array $route): bool => $isAdmin
                ? str_starts_with($route['uri'], 'control-panel')
                : ! str_starts_with($route['uri'], 'control-panel')
                    && ! str_starts_with($route['uri'], 'api/')
                    && ! str_starts_with($route['uri'], 'livewire/')
                    && ! str_starts_with($route['uri'], 'sanctum/')
                    && ! str_starts_with($route['uri'], '_debugbar')
                    && ! str_starts_with($route['uri'], 'docs')
            )
            ->sortBy('uri')
            ->values()
            ->all();

        return $cache[$sourceKey];
    }

    /**
     * Bangun label singkat untuk URL yang dipilih agar aman di layar mobile.
     */
    private static function pageUrlOptionLabel(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '-';
        }

        $url = trim((string) $value);

        if ($url === '') {
            return '-';
        }

        $host = (string) parse_url($url, PHP_URL_HOST);
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = (string) parse_url($url, PHP_URL_QUERY);

        if ($path === '') {
            return Str::limit($url, 100);
        }

        $label = '/'.ltrim($path, '/');

        if ($query !== '') {
            $label .= '?'.$query;
        }

        $label = Str::limit($label, 95);

        if ($host !== '') {
            return "{$label} ({$host})";
        }

        return $label;
    }

    /**
     * Buat label tampilan yang informatif dari URI dan nama route.
     */
    private static function routeDisplayLabel(string $uri, string $name): string
    {
        // Bersihkan prefiks filament dari nama route
        $shortName = preg_replace('/^filament\.([\w-]+)\./', '', $name);
        $shortName = preg_replace('/^(resources|pages)\./', '', $shortName ?? '');

        // Konversi slug ke label: "bug-reports.index" → "Bug Reports - Index"
        $humanName = $shortName
            ? collect(explode('.', $shortName))
                ->map(fn (string $s): string => Str::headline(str_replace('-', ' ', $s)))
                ->implode(' › ')
            : '';

        $path = '/'.ltrim($uri, '/');

        return $humanName ? "{$humanName} — {$path}" : $path;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS: Predefined Options
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array<string, string>
     */
    private static function browserOptions(): array
    {
        return [
            'Chrome' => 'Google Chrome',
            'Firefox' => 'Mozilla Firefox',
            'Safari' => 'Apple Safari',
            'Edge' => 'Microsoft Edge',
            'Opera' => 'Opera',
            'Brave' => 'Brave',
            'Samsung Internet' => 'Samsung Internet',
            'UC Browser' => 'UC Browser',
            'Other' => 'Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function osOptions(): array
    {
        return [
            'Windows' => [
                'Windows 11' => 'Windows 11',
                'Windows 10' => 'Windows 10',
            ],
            'macOS' => [
                'macOS Sequoia (15)' => 'macOS Sequoia (15)',
                'macOS Sonoma (14)' => 'macOS Sonoma (14)',
                'macOS Ventura (13)' => 'macOS Ventura (13)',
            ],
            'Android' => [
                'Android 15' => 'Android 15',
                'Android 14' => 'Android 14',
                'Android 13' => 'Android 13',
                'Android 12' => 'Android 12',
            ],
            'iOS' => [
                'iOS 18' => 'iOS 18',
                'iOS 17' => 'iOS 17',
                'iOS 16' => 'iOS 16',
            ],
            'Linux' => [
                'Ubuntu 24.04' => 'Ubuntu 24.04',
                'Ubuntu 22.04' => 'Ubuntu 22.04',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function mutateAttachmentData(array $data, ?Model $record = null): array
    {
        $path = $data['file_path'] ?? $record?->getAttribute('file_path');

        if (blank($path)) {
            return $data;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $data['file_name'] = $data['file_name'] ?? $record?->getAttribute('file_name') ?? basename((string) $path);

        if (! $disk->exists($path)) {
            return $data;
        }

        $data['mime_type'] = $disk->mimeType($path) ?: ($data['mime_type'] ?? $record?->getAttribute('mime_type'));
        $data['file_size'] = $disk->size($path) ?: ($data['file_size'] ?? $record?->getAttribute('file_size'));

        return $data;
    }

    public static function currentUserId(): ?int
    {
        $userId = Filament::auth()->id();

        return $userId !== null ? (int) $userId : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function testingAutofillData(): array
    {
        $faker = fake('id_ID');
        $assignedUserId = self::currentUserId();
        $referenceCode = now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        $attachment = self::testingAttachment();
        $pageUrl = self::defaultPageUrl() ?? url('/');
        $source = self::defaultSource() ?? BugSource::Storefront->value;
        $browser = self::detectedBrowserName() ?? 'Chrome';
        $browserVersion = self::detectedBrowserVersion() ?? '122.0.6261.95';
        $osName = self::detectedOsName() ?? 'Windows 11';
        $osVersion = self::detectedOsVersion() ?? '10.0';

        return [
            'platform' => Platform::Web->value,
            'source' => $source,
            'web_screen' => WebScreen::Desktop->value,
            'mobile_type' => null,
            'page_url' => $pageUrl,
            'title' => "Autofill Pengujian {$referenceCode} - Tombol checkout gagal diproses",
            'description' => $faker->paragraphs(2, true),
            'steps_to_reproduce' => implode(PHP_EOL, [
                '1. Buka halaman checkout.',
                '2. Isi data pengiriman dan metode pembayaran.',
                '3. Klik tombol Bayar sekarang.',
                '4. Amati respons halaman setelah tombol diklik.',
            ]),
            'expected_behavior' => 'Sistem seharusnya memproses checkout dan mengarahkan pengguna ke halaman pembayaran atau ringkasan pesanan.',
            'actual_behavior' => 'Tombol checkout terlihat aktif, tetapi setelah diklik proses tidak berjalan dan pengguna tetap berada di halaman yang sama.',
            'attachments' => [$attachment],
            'reporter_type' => BugReporterType::User->value,
            'reporter_id' => $assignedUserId,
            'reporter_name' => null,
            'reporter_email' => null,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $osName,
            'os_version' => $osVersion,
            'device_model' => null,
            'screen_resolution' => '1920x1080',
            'app_version' => 'test-build-1.0.0',
            'severity' => BugSeverity::High->value,
            'priority' => BugPriority::High->value,
            'error_category' => BugErrorCategory::SystemError->value,
            'status' => BugStatus::Open->value,
            'assigned_to' => $assignedUserId,
            'duplicate_of_id' => null,
            'resolution_note' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public static function defaultPlatform(): ?string
    {
        $requestedPlatform = request()->query('platform');

        if (is_string($requestedPlatform) && in_array($requestedPlatform, array_column(Platform::cases(), 'value'), true)) {
            return $requestedPlatform;
        }

        return filled(self::defaultPageUrl()) ? Platform::Web->value : null;
    }

    public static function defaultSource(): ?string
    {
        $requestedSource = request()->query('source');

        if (is_string($requestedSource) && in_array($requestedSource, array_column(BugSource::cases(), 'value'), true)) {
            return $requestedSource;
        }

        $pageUrl = self::defaultPageUrl();

        if (blank($pageUrl)) {
            return null;
        }

        $path = ltrim((string) parse_url($pageUrl, PHP_URL_PATH), '/');

        if ($path === '') {
            return null;
        }

        return str_starts_with($path, 'control-panel')
            ? BugSource::AdminConsole->value
            : BugSource::Storefront->value;
    }

    public static function defaultPageUrl(): ?string
    {
        $queryUrl = request()->query('page_url') ?? request()->query('url');

        if (is_string($queryUrl) && trim($queryUrl) !== '') {
            return trim($queryUrl);
        }

        $referer = trim((string) request()->headers->get('referer'));
        $currentUrl = trim((string) request()->fullUrl());

        if ($referer === '' || $referer === $currentUrl) {
            return null;
        }

        $refererPath = (string) parse_url($referer, PHP_URL_PATH);

        if ($refererPath !== '' && str_contains($refererPath, '/bug-reports')) {
            return null;
        }

        return $referer;
    }

    public static function detectedBrowserName(): ?string
    {
        return self::detectedBrowser()['name'];
    }

    public static function detectedBrowserVersion(): ?string
    {
        return self::detectedBrowser()['version'];
    }

    public static function detectedOsName(): ?string
    {
        return self::detectedOs()['name'];
    }

    public static function detectedOsVersion(): ?string
    {
        return self::detectedOs()['version'];
    }

    public static function detectedMobileType(): ?string
    {
        $userAgent = self::currentUserAgent();

        if ($userAgent === '') {
            return null;
        }

        if (str_contains($userAgent, 'Android')) {
            return MobileType::Android->value;
        }

        if (preg_match('/iPhone|iPad|iPod/i', $userAgent) === 1) {
            return MobileType::Ios->value;
        }

        return null;
    }

    public static function isAssignedUser(?BugReport $record): bool
    {
        if (! filled($record) || ! filled($record->assigned_to)) {
            return false;
        }

        return (int) $record->assigned_to === self::currentUserId();
    }

    public static function isReporterUser(?BugReport $record): bool
    {
        if (! filled($record) || ! filled($record->reporter_id)) {
            return false;
        }

        return $record->reporter_type === BugReporterType::User
            && (int) $record->reporter_id === self::currentUserId();
    }

    public static function canEditResolution(?BugReport $record): bool
    {
        return self::isAssignedUser($record);
    }

    public static function canConfirmClosure(?BugReport $record): bool
    {
        return self::isReporterUser($record)
            && filled($record?->resolved_at)
            && in_array($record?->status, [BugStatus::Resolved, BugStatus::Closed], true);
    }

    public static function findDuplicateTitleReport(string $title, ?int $ignoreId = null): ?BugReport
    {
        $normalizedTitle = self::normalizeTitle($title);

        if ($normalizedTitle === '') {
            return null;
        }

        return BugReport::query()
            ->select(['id', 'title', 'status'])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->orderByDesc('id')
            ->get()
            ->first(fn (BugReport $report): bool => self::normalizeTitle($report->title) === $normalizedTitle);
    }

    public static function duplicateTitleMessage(BugReport $report): string
    {
        return match ($report->status) {
            BugStatus::Resolved, BugStatus::Closed => "Judul laporan ini sudah pernah diajukan pada laporan #{$report->id} dan sudah diselesaikan. Ajukan laporan baru hanya jika masalahnya benar-benar berbeda.",
            BugStatus::Rejected => "Judul laporan ini sudah pernah diajukan pada laporan #{$report->id} dan ditolak. Pastikan masalah yang Anda ajukan sekarang benar-benar berbeda.",
            BugStatus::Duplicate => "Judul laporan ini sudah pernah tercatat pada laporan #{$report->id} sebagai duplikat. Gunakan judul lain hanya jika masalahnya berbeda.",
            default => "Judul laporan ini sudah digunakan pada laporan #{$report->id} dengan status {$report->status->getLabel()}. Masalah yang sama tidak perlu diajukan ulang.",
        };
    }

    private static function statusOptions(?BugReport $record): array
    {
        if (! filled($record)) {
            return self::enumOptions([BugStatus::Open]);
        }

        if ($record->status === BugStatus::Closed) {
            return self::enumOptions([BugStatus::Closed]);
        }

        $mainFormStatuses = [
            BugStatus::Open,
            BugStatus::UnderReview,
            BugStatus::Confirmed,
            BugStatus::InProgress,
            BugStatus::Duplicate,
        ];

        if (self::isAssignedUser($record)) {
            if ($record->status === BugStatus::Resolved) {
                $allowedStatuses = [BugStatus::Resolved];
            } elseif ($record->status === BugStatus::Rejected) {
                $allowedStatuses = [BugStatus::Rejected];
            } else {
                $allowedStatuses = $mainFormStatuses;
            }

            if (self::canConfirmClosure($record)) {
                $allowedStatuses[] = BugStatus::Closed;
            }

            return self::enumOptions($allowedStatuses);
        }

        if (self::isReporterUser($record)) {
            $allowedStatuses = [$record->status];

            if (self::canConfirmClosure($record)) {
                $allowedStatuses[] = BugStatus::Closed;
            }

            return self::enumOptions($allowedStatuses);
        }

        if ($record->status === BugStatus::Resolved) {
            return self::enumOptions([BugStatus::Resolved]);
        }

        if ($record->status === BugStatus::Rejected) {
            return self::enumOptions([BugStatus::Rejected]);
        }

        if ($record->status === BugStatus::Duplicate) {
            return self::enumOptions([BugStatus::Duplicate]);
        }

        return self::enumOptions($mainFormStatuses);
    }

    private static function statusHelperText(?BugReport $record): string
    {
        if (! filled($record)) {
            return 'Status awal laporan bug adalah Open. Proses penanganan dilakukan setelah laporan dibuat.';
        }

        if ($record->status === BugStatus::Closed) {
            return 'Laporan ini sudah ditutup oleh pelapor setelah perbaikan dikonfirmasi.';
        }

        if (self::isAssignedUser($record) && self::isReporterUser($record)) {
            return self::canConfirmClosure($record)
                ? 'Anda adalah pelapor sekaligus user yang ditugaskan. Gunakan dialog Perbaikan untuk mengisi resolusi, lalu ubah ke Closed setelah hasilnya dikonfirmasi.'
                : 'Anda adalah pelapor sekaligus user yang ditugaskan. Gunakan dialog Perbaikan untuk menyimpan hasil penanganan, lalu lakukan konfirmasi penutupan setelah bug benar-benar selesai.';
        }

        if (self::isAssignedUser($record)) {
            return 'Anda adalah user yang ditugaskan. Gunakan tombol Perbaikan untuk mengisi catatan resolusi dan waktu resolved. Penutupan akhir tetap dikonfirmasi oleh pelapor.';
        }

        if (self::isReporterUser($record)) {
            return self::canConfirmClosure($record)
                ? 'Anda adalah pelapor. Setelah memastikan perbaikannya sesuai, ubah status menjadi Closed untuk mengonfirmasi penyelesaian.'
                : 'Anda adalah pelapor. Status Closed baru tersedia setelah user yang ditugaskan menyelesaikan bug ini terlebih dahulu.';
        }

        return 'Form edit ini dipakai untuk triase dan penugasan. Hanya user yang ditugaskan yang dapat mengisi hasil perbaikan melalui dialog Perbaikan, dan hanya pelapor yang dapat menutup laporan.';
    }

    /**
     * @param  list<BugStatus>  $statuses
     * @return array<string, string>
     */
    private static function enumOptions(array $statuses): array
    {
        return collect($statuses)
            ->unique(fn (BugStatus $status): string => $status->value)
            ->mapWithKeys(fn (BugStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    private static function normalizeTitle(?string $title): string
    {
        return (string) Str::of((string) $title)
            ->squish()
            ->lower();
    }

    /**
     * @return array<string, mixed>
     */
    private static function testingAttachment(): array
    {
        $path = 'bug-reports/attachments/autofill/bug-report-testing-'.Str::uuid().'.png';
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7+SxkAAAAASUVORK5CYII=',
            true
        );

        Storage::disk('public')->put($path, $contents ?: '');

        return [
            'file_path' => $path,
            'file_name' => basename($path),
            'mime_type' => 'image/png',
            'file_size' => Storage::disk('public')->size($path),
            'caption' => 'Lampiran contoh hasil autofill untuk pengujian laporan bug.',
        ];
    }

    /**
     * @return array{name: string|null, version: string|null}
     */
    private static function detectedBrowser(): array
    {
        $userAgent = self::currentUserAgent();

        if ($userAgent === '') {
            return ['name' => null, 'version' => null];
        }

        $patterns = [
            'Samsung Internet' => '/SamsungBrowser\/([\d.]+)/i',
            'UC Browser' => '/UCBrowser\/([\d.]+)/i',
            'Edge' => '/Edg\/([\d.]+)/i',
            'Opera' => '/OPR\/([\d.]+)/i',
            'Firefox' => '/Firefox\/([\d.]+)/i',
            'Chrome' => '/Chrome\/([\d.]+)/i',
            'Safari' => '/Version\/([\d.]+).*Safari/i',
        ];

        foreach ($patterns as $browser => $pattern) {
            if (preg_match($pattern, $userAgent, $matches) !== 1) {
                continue;
            }

            return [
                'name' => $browser,
                'version' => $matches[1] ?? null,
            ];
        }

        return ['name' => 'Other', 'version' => null];
    }

    /**
     * @return array{name: string|null, version: string|null}
     */
    private static function detectedOs(): array
    {
        $userAgent = self::currentUserAgent();

        if ($userAgent === '') {
            return ['name' => null, 'version' => null];
        }

        if (preg_match('/Android\s+([\d.]+)/i', $userAgent, $matches) === 1) {
            $version = $matches[1] ?? null;
            $majorVersion = (int) Str::before((string) $version, '.');

            return [
                'name' => in_array($majorVersion, [12, 13, 14, 15], true) ? "Android {$majorVersion}" : null,
                'version' => $version,
            ];
        }

        if (preg_match('/(?:CPU(?: iPhone)? OS|iPhone OS)\s+([\d_]+)/i', $userAgent, $matches) === 1) {
            $version = str_replace('_', '.', (string) ($matches[1] ?? ''));
            $majorVersion = (int) Str::before($version, '.');

            return [
                'name' => in_array($majorVersion, [16, 17, 18], true) ? "iOS {$majorVersion}" : null,
                'version' => $version,
            ];
        }

        if (preg_match('/Windows NT\s+([\d.]+)/i', $userAgent, $matches) === 1) {
            return [
                'name' => null,
                'version' => $matches[1] ?? null,
            ];
        }

        if (preg_match('/Mac OS X\s+([\d_]+)/i', $userAgent, $matches) === 1) {
            return [
                'name' => null,
                'version' => str_replace('_', '.', (string) ($matches[1] ?? '')),
            ];
        }

        return ['name' => null, 'version' => null];
    }

    private static function currentUserAgent(): string
    {
        return trim((string) request()->userAgent());
    }
}
